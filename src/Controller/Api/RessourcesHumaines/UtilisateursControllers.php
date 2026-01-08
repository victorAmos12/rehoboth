<?php

namespace App\Controller\Api\RessourcesHumaines;

use App\Entity\Personnel\Utilisateurs;
use App\Entity\Personnel\Roles;
use App\Entity\Personnel\ProfilsUtilisateurs;
use App\Entity\Personnel\Specialites;
use App\Entity\Personnel\AffectationsUtilisateurs;
use App\Entity\Administration\Hopitaux;
use App\Entity\Administration\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\PermissionService;
use DateTimeImmutable;
use Exception;

/**
 * Contrôleur API pour la gestion complète des utilisateurs
 * 
 * Gère:
 * - CRUD complet des utilisateurs (Create, Read, Update, Delete)
 * - Recherche et filtrage avancés
 * - Gestion des rôles et profils
 * - Gestion des affectations aux services
 * - Gestion des spécialités
 * - Authentification et sécurité (2FA, verrouillage compte)
 * - Gestion des mots de passe
 * - Export de données (CSV, PDF)
 * - Statistiques utilisateurs
 * - Historique et audit
 * 
 * Tous les endpoints retournent du JSON sauf les téléchargements
 */
#[Route('/api/utilisateurs', name: 'api_utilisateurs_')]
class UtilisateursControllers extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * Récupère la liste de tous les utilisateurs avec pagination et filtrage
     * GET /api/utilisateurs
     * 
     * Paramètres de requête:
     * - page: numéro de page (défaut: 1)
     * - limit: nombre d'éléments par page (défaut: 20, max: 100)
     * - search: recherche par nom, prénom, email, login
     * - hopital_id: filtrer par hôpital
     * - role_id: filtrer par rôle
     * - profil_id: filtrer par profil
     * - specialite_id: filtrer par spécialité
     * - actif: filtrer par statut actif/inactif
     * - sort: champ de tri (défaut: dateCreation)
     * - order: ordre de tri ASC/DESC (défaut: DESC)
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $search = trim($request->query->get('search', ''));
            $hopitalId = $request->query->get('hopital_id');
            $roleId = $request->query->get('role_id');
            $profilId = $request->query->get('profil_id');
            $specialiteId = $request->query->get('specialite_id');
            $actif = $request->query->get('actif');
            $sort = $request->query->get('sort', 'dateCreation');
            $order = strtoupper($request->query->get('order', 'DESC'));

            if (!in_array($order, ['ASC', 'DESC'])) {
                $order = 'DESC';
            }

            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->leftJoin('u.specialiteId', 's')
                ->addSelect('h', 'r', 'p', 's');

            // Appliquer les filtres
            if (!empty($search)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('u.nom', ':search'),
                        $queryBuilder->expr()->like('u.prenom', ':search'),
                        $queryBuilder->expr()->like('u.email', ':search'),
                        $queryBuilder->expr()->like('u.login', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            if ($hopitalId) {
                $queryBuilder->andWhere('u.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($roleId) {
                $queryBuilder->andWhere('u.roleId = :roleId')
                    ->setParameter('roleId', $roleId);
            }

            if ($profilId) {
                $queryBuilder->andWhere('u.profilId = :profilId')
                    ->setParameter('profilId', $profilId);
            }

            if ($specialiteId) {
                $queryBuilder->andWhere('u.specialiteId = :specialiteId')
                    ->setParameter('specialiteId', $specialiteId);
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('u.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer le tri et la pagination
            $queryBuilder->orderBy('u.' . $sort, $order)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $utilisateurs = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Utilisateurs $user) {
                return $this->formatUtilisateurData($user);
            }, $utilisateurs);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails complets d'un utilisateur
     * GET /api/utilisateurs/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->leftJoin('u.specialiteId', 's')
                ->addSelect('h', 'r', 'p', 's')
                ->where('u.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            // Récupérer les affectations
            $affectations = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->findBy(['utilisateurId' => $utilisateur], ['dateDebut' => 'DESC']);

            $data = [
                'identite' => $this->formatUtilisateurData($utilisateur, true),
                'informations_personnelles' => [
                    'adresse' => $utilisateur->getAdresse(),
                    'ville' => $utilisateur->getVille(),
                    'code_postal' => $utilisateur->getCodePostal(),
                    'telephone' => $utilisateur->getTelephone(),
                    'email' => $utilisateur->getEmail(),
                    'date_naissance' => $utilisateur->getDateNaissance()?->format('Y-m-d'),
                    'sexe' => $utilisateur->getSexe(),
                    'nationalite' => $utilisateur->getNationalite(),
                    'numero_identite' => $utilisateur->getNumeroIdentite(),
                    'type_identite' => $utilisateur->getTypeIdentite(),
                    'contact_urgence' => [
                        'nom' => $utilisateur->getContactUrgenceNom(),
                        'telephone' => $utilisateur->getTelephoneUrgence(),
                    ],
                ],
                'informations_professionnelles' => [
                    'numero_licence' => $utilisateur->getNumeroLicence(),
                    'numero_ordre' => $utilisateur->getNumeroOrdre(),
                    'date_embauche' => $utilisateur->getDateEmbauche()?->format('Y-m-d'),
                    'specialite' => $utilisateur->getSpecialiteId() ? [
                        'id' => $utilisateur->getSpecialiteId()->getId(),
                        'nom' => $utilisateur->getSpecialiteId()->getNom(),
                        'code' => $utilisateur->getSpecialiteId()->getCode(),
                    ] : null,
                    'bio' => $utilisateur->getBio(),
                    'photo_profil' => $utilisateur->getPhotoProfil(),
                    'signature_numerique' => $utilisateur->getSignatureNumerique(),
                ],
                'informations_administratives' => [
                    'hopital' => [
                        'id' => $utilisateur->getHopitalId()->getId(),
                        'nom' => $utilisateur->getHopitalId()->getNom(),
                    ],
                    'role' => [
                        'id' => $utilisateur->getRoleId()->getId(),
                        'code' => $utilisateur->getRoleId()->getCode(),
                        'nom' => $utilisateur->getRoleId()->getNom(),
                        'niveau_acces' => $utilisateur->getRoleId()->getNiveauAcces(),
                    ],
                    'profil' => [
                        'id' => $utilisateur->getProfilId()->getId(),
                        'code' => $utilisateur->getProfilId()->getCode(),
                        'nom' => $utilisateur->getProfilId()->getNom(),
                        'type' => $utilisateur->getProfilId()->getTypeProfil(),
                    ],
                    'affectations' => array_map(function (AffectationsUtilisateurs $affectation) {
                        return [
                            'id' => $affectation->getId(),
                            'service' => $affectation->getServiceId()->getNom(),
                            'date_debut' => $affectation->getDateDebut()->format('Y-m-d'),
                            'date_fin' => $affectation->getDateFin()?->format('Y-m-d'),
                            'pourcentage_temps' => $affectation->getPourcentageTemps(),
                            'actif' => $affectation->getActif(),
                        ];
                    }, $affectations),
                ],
                'securite' => [
                    'actif' => $utilisateur->getActif(),
                    'compte_verrouille' => $utilisateur->getCompteVerrouille(),
                    'nombre_tentatives_connexion' => $utilisateur->getNombreTentativesConnexion(),
                    'mdp_temporaire' => $utilisateur->getMdpTemporaire(),
                    'authentification_2fa' => $utilisateur->getAuthentification2fa(),
                    'date_dernier_changement_mdp' => $utilisateur->getDateDernierChangementMdp()?->format('c'),
                    'derniere_connexion' => $utilisateur->getDerniereConnexion()?->format('c'),
                ],
                'historique' => [
                    'date_creation' => $utilisateur->getDateCreation()?->format('c'),
                    'date_modification' => $utilisateur->getDateModification()?->format('c'),
                ],
            ];

            return $this->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée un nouvel utilisateur
     * POST /api/utilisateurs
     * 
     * Champs requis:
     * - nom, prenom, email, login, motDePasse
     * - hopital_id, role_id, profil_id
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Valider les champs requis
            $requiredFields = ['nom', 'prenom', 'email', 'login', 'motDePasse', 'hopital_id', 'role_id', 'profil_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '$field' est requis",
                    ], 400);
                }
            }

            // Vérifier l'unicité du login et email
            $existingLogin = $this->entityManager->getRepository(Utilisateurs::class)
                ->findOneBy(['login' => $data['login']]);
            if ($existingLogin) {
                return $this->json([
                    'success' => false,
                    'error' => 'Ce login est déjà utilisé',
                ], 409);
            }

            $existingEmail = $this->entityManager->getRepository(Utilisateurs::class)
                ->findOneBy(['email' => $data['email']]);
            if ($existingEmail) {
                return $this->json([
                    'success' => false,
                    'error' => 'Cet email est déjà utilisé',
                ], 409);
            }

            // Récupérer les entités liées
            $hopital = $this->entityManager->getRepository(Hopitaux::class)
                ->find($data['hopital_id']);
            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            $role = $this->entityManager->getRepository(Roles::class)
                ->find($data['role_id']);
            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            $profil = $this->entityManager->getRepository(ProfilsUtilisateurs::class)
                ->find($data['profil_id']);
            if (!$profil) {
                return $this->json([
                    'success' => false,
                    'error' => 'Profil non trouvé',
                ], 404);
            }

            // Créer l'utilisateur
            $utilisateur = new Utilisateurs();
            $utilisateur->setNom($data['nom']);
            $utilisateur->setPrenom($data['prenom']);
            $utilisateur->setEmail($data['email']);
            $utilisateur->setLogin($data['login']);
            
            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $data['motDePasse']);
            $utilisateur->setMotDePasse($hashedPassword);
            
            $utilisateur->setHopitalId($hopital);
            $utilisateur->setRoleId($role);
            $utilisateur->setProfilId($profil);

            // Champs optionnels
            if (isset($data['telephone'])) $utilisateur->setTelephone($data['telephone']);
            if (isset($data['numeroLicence'])) $utilisateur->setNumeroLicence($data['numeroLicence']);
            if (isset($data['numeroOrdre'])) $utilisateur->setNumeroOrdre($data['numeroOrdre']);
            if (isset($data['dateEmbauche'])) $utilisateur->setDateEmbauche(new \DateTime($data['dateEmbauche']));
            if (isset($data['photoProfil'])) $utilisateur->setPhotoProfil($data['photoProfil']);
            if (isset($data['signatureNumerique'])) $utilisateur->setSignatureNumerique($data['signatureNumerique']);
            if (isset($data['bio'])) $utilisateur->setBio($data['bio']);
            if (isset($data['adresse'])) $utilisateur->setAdresse($data['adresse']);
            if (isset($data['ville'])) $utilisateur->setVille($data['ville']);
            if (isset($data['codePostal'])) $utilisateur->setCodePostal($data['codePostal']);
            if (isset($data['dateNaissance'])) $utilisateur->setDateNaissance(new \DateTime($data['dateNaissance']));
            if (isset($data['sexe'])) $utilisateur->setSexe($data['sexe']);
            if (isset($data['nationalite'])) $utilisateur->setNationalite($data['nationalite']);
            if (isset($data['numeroIdentite'])) $utilisateur->setNumeroIdentite($data['numeroIdentite']);
            if (isset($data['typeIdentite'])) $utilisateur->setTypeIdentite($data['typeIdentite']);
            if (isset($data['telephoneUrgence'])) $utilisateur->setTelephoneUrgence($data['telephoneUrgence']);
            if (isset($data['contactUrgenceNom'])) $utilisateur->setContactUrgenceNom($data['contactUrgenceNom']);
            if (isset($data['specialite_id'])) {
                $specialite = $this->entityManager->getRepository(Specialites::class)
                    ->find($data['specialite_id']);
                if ($specialite) {
                    $utilisateur->setSpecialiteId($specialite);
                }
            }
            if (isset($data['authentification_2fa'])) $utilisateur->setAuthentification2fa($data['authentification_2fa']);

            // Valider l'entité
            $errors = $this->validator->validate($utilisateur);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'details' => $errorMessages,
                ], 400);
            }

            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'data' => $this->formatUtilisateurData($utilisateur),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour un utilisateur existant
     * PUT /api/utilisateurs/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['nom'])) $utilisateur->setNom($data['nom']);
            if (isset($data['prenom'])) $utilisateur->setPrenom($data['prenom']);
            if (isset($data['email'])) {
                // Vérifier l'unicité
                $existing = $this->entityManager->getRepository(Utilisateurs::class)
                    ->findOneBy(['email' => $data['email']]);
                if ($existing && $existing->getId() !== $id) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Cet email est déjà utilisé',
                    ], 409);
                }
                $utilisateur->setEmail($data['email']);
            }
            if (isset($data['telephone'])) $utilisateur->setTelephone($data['telephone']);
            if (isset($data['numeroLicence'])) $utilisateur->setNumeroLicence($data['numeroLicence']);
            if (isset($data['numeroOrdre'])) $utilisateur->setNumeroOrdre($data['numeroOrdre']);
            if (isset($data['dateEmbauche'])) $utilisateur->setDateEmbauche(new \DateTime($data['dateEmbauche']));
            if (isset($data['photoProfil'])) $utilisateur->setPhotoProfil($data['photoProfil']);
            if (isset($data['signatureNumerique'])) $utilisateur->setSignatureNumerique($data['signatureNumerique']);
            if (isset($data['bio'])) $utilisateur->setBio($data['bio']);
            if (isset($data['adresse'])) $utilisateur->setAdresse($data['adresse']);
            if (isset($data['ville'])) $utilisateur->setVille($data['ville']);
            if (isset($data['codePostal'])) $utilisateur->setCodePostal($data['codePostal']);
            if (isset($data['dateNaissance'])) $utilisateur->setDateNaissance(new \DateTime($data['dateNaissance']));
            if (isset($data['sexe'])) $utilisateur->setSexe($data['sexe']);
            if (isset($data['nationalite'])) $utilisateur->setNationalite($data['nationalite']);
            if (isset($data['numeroIdentite'])) $utilisateur->setNumeroIdentite($data['numeroIdentite']);
            if (isset($data['typeIdentite'])) $utilisateur->setTypeIdentite($data['typeIdentite']);
            if (isset($data['telephoneUrgence'])) $utilisateur->setTelephoneUrgence($data['telephoneUrgence']);
            if (isset($data['contactUrgenceNom'])) $utilisateur->setContactUrgenceNom($data['contactUrgenceNom']);
            if (isset($data['actif'])) $utilisateur->setActif($data['actif']);
            if (isset($data['authentification_2fa'])) $utilisateur->setAuthentification2fa($data['authentification_2fa']);
            
            if (isset($data['role_id'])) {
                $role = $this->entityManager->getRepository(Roles::class)
                    ->find($data['role_id']);
                if ($role) {
                    $utilisateur->setRoleId($role);
                }
            }
            
            if (isset($data['profil_id'])) {
                $profil = $this->entityManager->getRepository(ProfilsUtilisateurs::class)
                    ->find($data['profil_id']);
                if ($profil) {
                    $utilisateur->setProfilId($profil);
                }
            }
            
            if (isset($data['specialite_id'])) {
                if ($data['specialite_id'] === null) {
                    $utilisateur->setSpecialiteId(null);
                } else {
                    $specialite = $this->entityManager->getRepository(Specialites::class)
                        ->find($data['specialite_id']);
                    if ($specialite) {
                        $utilisateur->setSpecialiteId($specialite);
                    }
                }
            }

            $utilisateur->setDateModification(new DateTimeImmutable());

            // Valider l'entité
            $errors = $this->validator->validate($utilisateur);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'details' => $errorMessages,
                ], 400);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Utilisateur mis à jour avec succès',
                'data' => $this->formatUtilisateurData($utilisateur),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un utilisateur (soft delete - marque comme inactif)
     * DELETE /api/utilisateurs/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            // Soft delete
            $utilisateur->setActif(false);
            $utilisateur->setDateModification(new DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change le mot de passe d'un utilisateur
     * POST /api/utilisateurs/{id}/change-password
     * 
     * Body:
     * - ancien_mot_de_passe: ancien mot de passe
     * - nouveau_mot_de_passe: nouveau mot de passe
     */
    #[Route('/{id}/change-password', name: 'change_password', methods: ['POST'])]
    public function changePassword(int $id, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['ancien_mot_de_passe']) || !isset($data['nouveau_mot_de_passe'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs ancien_mot_de_passe et nouveau_mot_de_passe sont requis',
                ], 400);
            }

            // Vérifier l'ancien mot de passe
            if (!$this->passwordHasher->isPasswordValid($utilisateur, $data['ancien_mot_de_passe'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'L\'ancien mot de passe est incorrect',
                ], 401);
            }

            // Hasher et définir le nouveau mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $data['nouveau_mot_de_passe']);
            $utilisateur->setMotDePasse($hashedPassword);
            $utilisateur->setDateDernierChangementMdp(new DateTimeImmutable());
            $utilisateur->setMdpTemporaire(false);
            $utilisateur->setDateModification(new DateTimeImmutable());

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Mot de passe changé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors du changement de mot de passe: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur (admin)
     * POST /api/utilisateurs/{id}/reset-password
     * 
     * Body:
     * - nouveau_mot_de_passe: nouveau mot de passe
     */
    #[Route('/{id}/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(int $id, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['nouveau_mot_de_passe'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ nouveau_mot_de_passe est requis',
                ], 400);
            }

            // Hasher et définir le nouveau mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $data['nouveau_mot_de_passe']);
            $utilisateur->setMotDePasse($hashedPassword);
            $utilisateur->setDateDernierChangementMdp(new DateTimeImmutable());
            $utilisateur->setMdpTemporaire(true);
            $utilisateur->setNombreTentativesConnexion(0);
            $utilisateur->setCompteVerrouille(false);
            $utilisateur->setDateModification(new DateTimeImmutable());

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
                'mdp_temporaire' => true,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la réinitialisation du mot de passe: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verrouille/déverrouille un compte utilisateur
     * POST /api/utilisateurs/{id}/lock
     * 
     * Body:
     * - verrouille: true pour verrouiller, false pour déverrouiller
     */
    #[Route('/{id}/lock', name: 'lock_account', methods: ['POST'])]
    public function lockAccount(int $id, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $verrouille = $data['verrouille'] ?? true;

            $utilisateur->setCompteVerrouille($verrouille);
            if (!$verrouille) {
                $utilisateur->setNombreTentativesConnexion(0);
            }
            $utilisateur->setDateModification(new DateTimeImmutable());

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $verrouille ? 'Compte verrouillé' : 'Compte déverrouillé',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors du verrouillage du compte: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Active/désactive la 2FA pour un utilisateur
     * POST /api/utilisateurs/{id}/2fa
     * 
     * Body:
     * - actif: true pour activer, false pour désactiver
     */
    #[Route('/{id}/2fa', name: 'toggle_2fa', methods: ['POST'])]
    public function toggle2FA(int $id, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouv��',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);
            $actif = $data['actif'] ?? true;

            $utilisateur->setAuthentification2fa($actif);
            $utilisateur->setDateModification(new DateTimeImmutable());

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $actif ? 'Authentification 2FA activée' : 'Authentification 2FA désactivée',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification de la 2FA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enregistre la dernière connexion d'un utilisateur
     * POST /api/utilisateurs/{id}/last-login
     */
    #[Route('/{id}/last-login', name: 'update_last_login', methods: ['POST'])]
    public function updateLastLogin(int $id): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $utilisateur->setDerniereConnexion(new DateTimeImmutable());
            $utilisateur->setNombreTentativesConnexion(0);
            $utilisateur->setDateModification(new DateTimeImmutable());

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Dernière connexion mise à jour',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recherche avancée d'utilisateurs
     * POST /api/utilisateurs/search
     */
    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $page = max(1, (int)($data['page'] ?? 1));
            $limit = min(100, max(1, (int)($data['limit'] ?? 20)));

            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->addSelect('h', 'r', 'p');

            // Appliquer les filtres
            if (isset($data['nom']) && !empty($data['nom'])) {
                $queryBuilder->andWhere('u.nom LIKE :nom')
                    ->setParameter('nom', '%' . $data['nom'] . '%');
            }

            if (isset($data['prenom']) && !empty($data['prenom'])) {
                $queryBuilder->andWhere('u.prenom LIKE :prenom')
                    ->setParameter('prenom', '%' . $data['prenom'] . '%');
            }

            if (isset($data['email']) && !empty($data['email'])) {
                $queryBuilder->andWhere('u.email LIKE :email')
                    ->setParameter('email', '%' . $data['email'] . '%');
            }

            if (isset($data['login']) && !empty($data['login'])) {
                $queryBuilder->andWhere('u.login LIKE :login')
                    ->setParameter('login', '%' . $data['login'] . '%');
            }

            if (isset($data['hopital_id'])) {
                $queryBuilder->andWhere('u.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $data['hopital_id']);
            }

            if (isset($data['role_id'])) {
                $queryBuilder->andWhere('u.roleId = :roleId')
                    ->setParameter('roleId', $data['role_id']);
            }

            if (isset($data['profil_id'])) {
                $queryBuilder->andWhere('u.profilId = :profilId')
                    ->setParameter('profilId', $data['profil_id']);
            }

            if (isset($data['actif'])) {
                $queryBuilder->andWhere('u.actif = :actif')
                    ->setParameter('actif', filter_var($data['actif'], FILTER_VALIDATE_BOOLEAN));
            }

            if (isset($data['compte_verrouille'])) {
                $queryBuilder->andWhere('u.compteVerrouille = :compteVerrouille')
                    ->setParameter('compteVerrouille', filter_var($data['compte_verrouille'], FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('u.dateCreation', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $utilisateurs = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Utilisateurs $user) {
                return $this->formatUtilisateurData($user);
            }, $utilisateurs);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les utilisateurs d'un hôpital spécifique
     * GET /api/utilisateurs/hopital/{hopitalId}
     */
    #[Route('/hopital/{hopitalId}', name: 'by_hopital', methods: ['GET'])]
    public function byHopital(int $hopitalId, Request $request): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($hopitalId);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->addSelect('h', 'r', 'p')
                ->where('u.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId)
                ->orderBy('u.dateCreation', 'DESC');

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $utilisateurs = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Utilisateurs $user) {
                return $this->formatUtilisateurData($user);
            }, $utilisateurs);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'hopital' => $hopital->getNom(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les utilisateurs par rôle
     * GET /api/utilisateurs/role/{roleId}
     */
    #[Route('/role/{roleId}', name: 'by_role', methods: ['GET'])]
    public function byRole(int $roleId, Request $request): JsonResponse
    {
        try {
            $role = $this->entityManager->getRepository(Roles::class)->find($roleId);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->addSelect('h', 'r', 'p')
                ->where('u.roleId = :roleId')
                ->setParameter('roleId', $roleId)
                ->orderBy('u.dateCreation', 'DESC');

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $utilisateurs = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Utilisateurs $user) {
                return $this->formatUtilisateurData($user);
            }, $utilisateurs);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'role' => $role->getNom(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des utilisateurs
     * GET /api/utilisateurs/stats
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        try {
            $repository = $this->entityManager->getRepository(Utilisateurs::class);

            // Total
            $total = count($repository->findAll());

            // Actifs/Inactifs
            $actifs = count($repository->findBy(['actif' => true]));
            $inactifs = $total - $actifs;

            // Comptes verrouillés
            $verrouilles = count($repository->findBy(['compteVerrouille' => true]));

            // 2FA activé
            $avec2fa = count($repository->findBy(['authentification2fa' => true]));

            // Par rôle
            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->select('r.nom, COUNT(u.id) as count')
                ->leftJoin('u.roleId', 'r')
                ->groupBy('r.id')
                ->getQuery()
                ->getResult();

            $parRole = [];
            foreach ($queryBuilder as $row) {
                $parRole[$row['nom']] = (int)$row['count'];
            }

            // Par profil
            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->select('p.nom, COUNT(u.id) as count')
                ->leftJoin('u.profilId', 'p')
                ->groupBy('p.id')
                ->getQuery()
                ->getResult();

            $parProfil = [];
            foreach ($queryBuilder as $row) {
                $parProfil[$row['nom']] = (int)$row['count'];
            }

            // Par hôpital
            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->select('h.nom, COUNT(u.id) as count')
                ->leftJoin('u.hopitalId', 'h')
                ->groupBy('h.id')
                ->getQuery()
                ->getResult();

            $parHopital = [];
            foreach ($queryBuilder as $row) {
                $parHopital[$row['nom']] = (int)$row['count'];
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'actifs' => $actifs,
                    'inactifs' => $inactifs,
                    'comptes_verrouilles' => $verrouilles,
                    'avec_2fa' => $avec2fa,
                    'par_role' => $parRole,
                    'par_profil' => $parProfil,
                    'par_hopital' => $parHopital,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporte les utilisateurs en CSV
     * GET /api/utilisateurs/export/csv
     */
    #[Route('/export/csv', name: 'export_csv', methods: ['GET'])]
    public function exportCsv(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $hopitalId = $request->query->get('hopital_id');
            $roleId = $request->query->get('role_id');
            $actif = $request->query->get('actif');

            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->addSelect('h', 'r', 'p');

            if ($hopitalId) {
                $queryBuilder->andWhere('u.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($roleId) {
                $queryBuilder->andWhere('u.roleId = :roleId')
                    ->setParameter('roleId', $roleId);
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('u.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            $utilisateurs = $queryBuilder->getQuery()->getResult();

            // Créer le fichier CSV
            $filename = 'utilisateurs_export_' . time() . '.csv';
            $filepath = sys_get_temp_dir() . '/' . $filename;
            $file = fopen($filepath, 'w');

            // En-têtes
            fputcsv($file, [
                'ID',
                'Nom',
                'Prénom',
                'Email',
                'Login',
                'Téléphone',
                'Rôle',
                'Profil',
                'Hôpital',
                'Spécialité',
                'Date d\'embauche',
                'Actif',
                'Compte verrouillé',
                'Authentification 2FA',
                'Date de création',
            ], ';');

            // Données
            foreach ($utilisateurs as $user) {
                fputcsv($file, [
                    $user->getId(),
                    $user->getNom(),
                    $user->getPrenom(),
                    $user->getEmail(),
                    $user->getLogin(),
                    $user->getTelephone() ?? '',
                    $user->getRoleId()->getNom(),
                    $user->getProfilId()->getNom(),
                    $user->getHopitalId()->getNom(),
                    $user->getSpecialiteId()?->getNom() ?? '',
                    $user->getDateEmbauche()?->format('d/m/Y') ?? '',
                    $user->getActif() ? 'Oui' : 'Non',
                    $user->getCompteVerrouille() ? 'Oui' : 'Non',
                    $user->getAuthentification2fa() ? 'Oui' : 'Non',
                    $user->getDateCreation()?->format('d/m/Y H:i') ?? '',
                ], ';');
            }

            fclose($file);

            $response = new BinaryFileResponse($filepath);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            );

            return $response;

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export CSV: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formate les données d'un utilisateur pour la réponse JSON
     */
    private function formatUtilisateurData(Utilisateurs $user, bool $detailed = false): array
    {
        $data = [
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'login' => $user->getLogin(),
            'telephone' => $user->getTelephone(),
            'actif' => $user->getActif(),
            'hopital' => [
                'id' => $user->getHopitalId()->getId(),
                'nom' => $user->getHopitalId()->getNom(),
            ],
            'role' => [
                'id' => $user->getRoleId()->getId(),
                'code' => $user->getRoleId()->getCode(),
                'nom' => $user->getRoleId()->getNom(),
            ],
            'profil' => [
                'id' => $user->getProfilId()->getId(),
                'code' => $user->getProfilId()->getCode(),
                'nom' => $user->getProfilId()->getNom(),
            ],
            'specialite' => $user->getSpecialiteId() ? [
                'id' => $user->getSpecialiteId()->getId(),
                'nom' => $user->getSpecialiteId()->getNom(),
            ] : null,
            'dateCreation' => $user->getDateCreation()?->format('c'),
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'numeroLicence' => $user->getNumeroLicence(),
                'numeroOrdre' => $user->getNumeroOrdre(),
                'dateEmbauche' => $user->getDateEmbauche()?->format('Y-m-d'),
                'photoProfil' => $user->getPhotoProfil(),
                'signatureNumerique' => $user->getSignatureNumerique(),
                'bio' => $user->getBio(),
                'adresse' => $user->getAdresse(),
                'ville' => $user->getVille(),
                'codePostal' => $user->getCodePostal(),
                'dateNaissance' => $user->getDateNaissance()?->format('Y-m-d'),
                'sexe' => $user->getSexe(),
                'nationalite' => $user->getNationalite(),
                'numeroIdentite' => $user->getNumeroIdentite(),
                'typeIdentite' => $user->getTypeIdentite(),
                'telephoneUrgence' => $user->getTelephoneUrgence(),
                'contactUrgenceNom' => $user->getContactUrgenceNom(),
                'compteVerrouille' => $user->getCompteVerrouille(),
                'nombreTentativesConnexion' => $user->getNombreTentativesConnexion(),
                'mdpTemporaire' => $user->getMdpTemporaire(),
                'authentification2fa' => $user->getAuthentification2fa(),
                'dateDernierChangementMdp' => $user->getDateDernierChangementMdp()?->format('c'),
                'derniereConnexion' => $user->getDerniereConnexion()?->format('c'),
                'dateModification' => $user->getDateModification()?->format('c'),
            ]);
        }

        return $data;
    }
}
