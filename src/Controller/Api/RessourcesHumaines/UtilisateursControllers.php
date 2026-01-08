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
use OTPHP\TOTP;
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
     * Active la 2FA pour un utilisateur avec vérification du code PIN
     * POST /api/utilisateurs/{id}/2fa/enable
     * 
     * Body:
     * - pin: Code PIN à 6 chiffres pour activer la 2FA
     * 
     * Response (Succès):
     * {
     *   "success": true,
     *   "message": "Authentification 2FA activée avec succès",
     *   "secret": "JBSWY3DPEBLW64TMMQ====",
     *   "qr_code": "data:image/png;base64,..."
     * }
     */
    #[Route('/{id}/2fa/enable', name: 'enable_2fa', methods: ['POST'])]
    public function enable2FA(string $id, Request $request): JsonResponse
    {
        try {
            $id = (int)$id;
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['pin'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "pin" est requis',
                ], 400);
            }

            $pin = trim($data['pin']);

            // Valider le PIN (6 chiffres)
            if (!preg_match('/^\d{6}$/', $pin)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le PIN doit être composé de 6 chiffres',
                ], 400);
            }

            // Générer un secret 2FA (TOTP)
            $secret = $this->generate2FASecret();

            // Sauvegarder le secret et le PIN hashé
            $utilisateur->setAuthentification2fa(true);
            $utilisateur->setSecret2fa($secret);
            $utilisateur->setPin2fa(password_hash($pin, PASSWORD_BCRYPT));
            $utilisateur->setDateModification(new DateTimeImmutable());

            $this->entityManager->flush();

            // Générer le QR code
            $qrCode = $this->generate2FAQRCode($utilisateur, $secret);

            return $this->json([
                'success' => true,
                'message' => 'Authentification 2FA activée avec succès',
                'secret' => $secret,
                'qr_code' => $qrCode,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'activation de la 2FA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Désactive la 2FA pour un utilisateur avec vérification du code PIN
     * POST /api/utilisateurs/{id}/2fa/disable
     * 
     * Body:
     * - pin: Code PIN pour désactiver la 2FA
     */
    #[Route('/{id}/2fa/disable', name: 'disable_2fa', methods: ['POST'])]
    public function disable2FA(int $id, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            if (!$utilisateur->getAuthentification2fa()) {
                return $this->json([
                    'success' => false,
                    'error' => 'La 2FA n\'est pas activée pour cet utilisateur',
                ], 400);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['pin'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "pin" est requis',
                ], 400);
            }

            $pin = trim($data['pin']);

            // Vérifier le PIN
            if (!password_verify($pin, $utilisateur->getPin2fa())) {
                return $this->json([
                    'success' => false,
                    'error' => 'PIN incorrect',
                ], 401);
            }

            // Désactiver la 2FA
            $utilisateur->setAuthentification2fa(false);
            $utilisateur->setSecret2fa(null);
            $utilisateur->setPin2fa(null);
            $utilisateur->setDateModification(new DateTimeImmutable());

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Authentification 2FA désactivée avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la désactivation de la 2FA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifie le code 2FA (TOTP) d'un utilisateur
     * POST /api/utilisateurs/{id}/2fa/verify
     * 
     * Body:
     * - code: Code TOTP à 6 chiffres
     * 
     * Response (Succès):
     * {
     *   "success": true,
     *   "message": "Code 2FA valide"
     * }
     */
    #[Route('/{id}/2fa/verify', name: 'verify_2fa', methods: ['POST'])]
    public function verify2FA(int $id, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($id);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            if (!$utilisateur->getAuthentification2fa()) {
                return $this->json([
                    'success' => false,
                    'error' => 'La 2FA n\'est pas activée pour cet utilisateur',
                ], 400);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['code'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "code" est requis',
                ], 400);
            }

            $code = trim($data['code']);

            // Valider le code TOTP
            if (!preg_match('/^\d{6}$/', $code)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le code doit être composé de 6 chiffres',
                ], 400);
            }

            // Vérifier le code TOTP
            if (!$this->verify2FACode($utilisateur->getSecret2fa(), $code)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Code 2FA invalide',
                ], 401);
            }

            return $this->json([
                'success' => true,
                'message' => 'Code 2FA valide',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la vérification du code 2FA: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Active/désactive la 2FA pour un utilisateur (ancienne méthode - dépréciée)
     * POST /api/utilisateurs/{id}/2fa
     * 
     * Body:
     * - actif: true pour activer, false pour désactiver
     * 
     * @deprecated Utiliser /2fa/enable et /2fa/disable à la place
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
     * Exporte un seul utilisateur en PDF
     * GET /api/utilisateurs/{id}/export/pdf
     */
    #[Route('/{id}/export/pdf', name: 'export_single_pdf', methods: ['GET'])]
    public function exportSinglePdf(int $id): Response|JsonResponse
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

            // Générer le PDF pour un seul utilisateur
            $pdf = $this->generateSingleUserPdf($utilisateur);
            $pdfContent = $pdf->Output('', 'S');

            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set(
                'Content-Disposition',
                'attachment; filename="utilisateur_' . $utilisateur->getNom() . '_' . date('Y-m-d_H-i-s') . '.pdf"'
            );

            return $response;

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exporte les utilisateurs en PDF avec TCPDF
     * GET /api/utilisateurs/export/pdf
     * 
     * Paramètres de requête:
     * - hopital_id: filtrer par hôpital
     * - role_id: filtrer par rôle
     * - actif: filtrer par statut actif/inactif
     * - format: 'list' (défaut) ou 'detailed' pour un rapport détaillé
     */
    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request): Response|JsonResponse
    {
        try {
            $hopitalId = $request->query->get('hopital_id');
            $roleId = $request->query->get('role_id');
            $actif = $request->query->get('actif');
            $format = $request->query->get('format', 'list');

            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->leftJoin('u.specialiteId', 's')
                ->addSelect('h', 'r', 'p', 's');

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

            $utilisateurs = $queryBuilder->orderBy('u.nom', 'ASC')->getQuery()->getResult();

            // Générer le PDF moderne
            $pdf = $this->generateModernUsersPdf($utilisateurs, $hopitalId, $roleId, $actif, $format);
            $pdfContent = $pdf->Output('', 'S');

            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set(
                'Content-Disposition',
                'attachment; filename="utilisateurs_export_' . date('Y-m-d_H-i-s') . '.pdf"'
            );

            return $response;

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Génère un PDF moderne et professionnel pour la liste des utilisateurs
     * Design hospitalier avec logo, en-têtes, pieds de page, couleurs personnalisées
     */
    private function generateModernUsersPdf(array $utilisateurs, ?int $hopitalId, ?int $roleId, ?string $actif, string $format): \TCPDF
    {
        // Récupérer l'hôpital pour le logo et les couleurs
        $hopital = null;
        if ($hopitalId) {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($hopitalId);
        } elseif (!empty($utilisateurs)) {
            $hopital = $utilisateurs[0]->getHopitalId();
        }

        // Couleurs professionnelles (bleu médical)
        $couleurPrimaire = [41, 128, 185];      // Bleu
        $couleurSecondaire = [52, 152, 219];    // Bleu clair
        $couleurAccent = [230, 126, 34];        // Orange
        $couleurFond = [236, 240, 241];         // Gris clair

        // Chemin du logo
        $logoPath = null;
        try {
            $logoPath = $this->getParameter('kernel.project_dir') . '/public/assets/image_rehoboth.png';
            if (!file_exists($logoPath)) {
                $logoPath = null;
            }
        } catch (Exception $e) {
            $logoPath = null;
        }

        // Créer une classe TCPDF personnalisée pour les en-têtes/pieds de page
        $pdf = new class extends \TCPDF {
            public $hospitalName = '';
            public $logoPath = '';
            public $couleurPrimaire = [];
            public $pageNumber = 0;

            public function Header(): void
            {
                $this->SetMargins(12, 12, 12);
                
                // Fond de l'en-tête
                $this->SetFillColor($this->couleurPrimaire[0], $this->couleurPrimaire[1], $this->couleurPrimaire[2]);
                $this->Rect(0, 0, $this->GetPageWidth(), 22, 'F');

                // Logo
                if ($this->logoPath && file_exists($this->logoPath)) {
                    try {
                        $this->Image($this->logoPath, 12, 3, 18, 18, 'PNG');
                    } catch (Exception $e) {
                        // Continuer sans logo
                    }
                }

                // Texte d'en-tête
                $this->SetFont('helvetica', 'B', 14);
                $this->SetTextColor(255, 255, 255);
                $this->SetXY(32, 5);
                $this->Cell(0, 7, 'RAPPORT DES UTILISATEURS', 0, 1, 'L');

                $this->SetFont('helvetica', '', 9);
                $this->SetXY(32, 12);
                $this->Cell(0, 5, $this->hospitalName, 0, 1, 'L');

                // Ligne de séparation
                $this->SetDrawColor(255, 255, 255);
                $this->SetLineWidth(0.5);
                $this->Line(12, 22, $this->GetPageWidth() - 12, 22);

                $this->SetTextColor(0, 0, 0);
                $this->SetY(25);
            }

            public function Footer(): void
            {
                $this->SetY(-15);
                $this->SetFont('helvetica', 'I', 8);
                $this->SetTextColor(100, 100, 100);

                // Ligne de séparation
                $this->SetDrawColor(200, 200, 200);
                $this->SetLineWidth(0.3);
                $this->Line(12, $this->GetY() - 2, $this->GetPageWidth() - 12, $this->GetY() - 2);

                // Contenu du pied de page
                $this->Cell(0, 5, 'Généré le ' . date('d/m/Y à H:i:s'), 0, 0, 'L');
                $this->Cell(0, 5, 'Page ' . $this->getPage(), 0, 1, 'R');
            }
        };

        // Configuration du PDF
        $pdf->SetCreator('Rehoboth Hospital Management System');
        $pdf->SetAuthor('Rehoboth Hospital');
        $pdf->SetTitle('Rapport des Utilisateurs');
        $pdf->SetSubject('Liste des utilisateurs');
        $pdf->SetDefaultMonospacedFont(\PDF_FONT_MONOSPACED);
        $pdf->SetMargins(12, 35, 12);
        $pdf->SetAutoPageBreak(true, 25);

        // Passer les données à la classe
        $pdf->hospitalName = $hopital ? $hopital->getNom() : 'Rehoboth Hospital';
        $pdf->logoPath = $logoPath ?? '';
        $pdf->couleurPrimaire = $couleurPrimaire;

        // Ajouter une page
        $pdf->AddPage();

        // Section des filtres appliqués
        if ($hopitalId || $roleId || $actif !== null) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor($couleurSecondaire[0], $couleurSecondaire[1], $couleurSecondaire[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 6, 'FILTRES APPLIQUÉS', 0, 1, 'L', true);

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor($couleurFond[0], $couleurFond[1], $couleurFond[2]);

            if ($hopitalId && $hopital) {
                $pdf->Cell(0, 5, '• Hôpital: ' . $hopital->getNom(), 0, 1, 'L', true);
            }

            if ($roleId) {
                $role = $this->entityManager->getRepository(Roles::class)->find($roleId);
                if ($role) {
                    $pdf->Cell(0, 5, '• Rôle: ' . $role->getNom(), 0, 1, 'L', true);
                }
            }

            if ($actif !== null) {
                $pdf->Cell(0, 5, '• Statut: ' . (filter_var($actif, FILTER_VALIDATE_BOOLEAN) ? 'Actif' : 'Inactif'), 0, 1, 'L', true);
            }

            $pdf->Ln(3);
        }

        // Générer le contenu selon le format
        if ($format === 'detailed') {
            $this->generateDetailedPdfContent($pdf, $utilisateurs, $couleurPrimaire, $couleurSecondaire, $couleurFond);
        } else {
            $this->generateListPdfContent($pdf, $utilisateurs, $couleurPrimaire, $couleurSecondaire, $couleurFond);
        }

        return $pdf;
    }

    /**
     * Génère le contenu du PDF au format liste
     */
    private function generateListPdfContent(\TCPDF $pdf, array $utilisateurs, array $couleurPrimaire, array $couleurSecondaire, array $couleurFond): void
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);

        // En-têtes du tableau
        $pdf->Cell(8, 7, 'ID', 1, 0, 'C', true);
        $pdf->Cell(22, 7, 'Nom', 1, 0, 'L', true);
        $pdf->Cell(22, 7, 'Prénom', 1, 0, 'L', true);
        $pdf->Cell(32, 7, 'Email', 1, 0, 'L', true);
        $pdf->Cell(18, 7, 'Rôle', 1, 0, 'L', true);
        $pdf->Cell(18, 7, 'Profil', 1, 0, 'L', true);
        $pdf->Cell(12, 7, 'Actif', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(200, 200, 200);
        $fill = false;

        // Données
        foreach ($utilisateurs as $user) {
            $bgColor = $fill ? $couleurFond : [255, 255, 255];
            $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);

            $pdf->Cell(8, 6, $user->getId(), 1, 0, 'C', true);
            $pdf->Cell(22, 6, substr($user->getNom(), 0, 18), 1, 0, 'L', true);
            $pdf->Cell(22, 6, substr($user->getPrenom(), 0, 18), 1, 0, 'L', true);
            $pdf->Cell(32, 6, substr($user->getEmail(), 0, 28), 1, 0, 'L', true);
            $pdf->Cell(18, 6, substr($user->getRoleId()->getNom(), 0, 14), 1, 0, 'L', true);
            $pdf->Cell(18, 6, substr($user->getProfilId()->getNom(), 0, 14), 1, 0, 'L', true);
            
            // Statut avec couleur
            $statusColor = $user->getActif() ? [46, 204, 113] : [231, 76, 60]; // Vert ou Rouge
            $pdf->SetFillColor($statusColor[0], $statusColor[1], $statusColor[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(12, 6, $user->getActif() ? 'Oui' : 'Non', 1, 1, 'C', true);
            $pdf->SetTextColor(0, 0, 0);

            $fill = !$fill;

            // Vérifier si on a besoin d'une nouvelle page
            if ($pdf->GetY() > 260) {
                $pdf->AddPage();
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
                $pdf->SetTextColor(255, 255, 255);

                // Répéter les en-têtes
                $pdf->Cell(8, 7, 'ID', 1, 0, 'C', true);
                $pdf->Cell(22, 7, 'Nom', 1, 0, 'L', true);
                $pdf->Cell(22, 7, 'Prénom', 1, 0, 'L', true);
                $pdf->Cell(32, 7, 'Email', 1, 0, 'L', true);
                $pdf->Cell(18, 7, 'Rôle', 1, 0, 'L', true);
                $pdf->Cell(18, 7, 'Profil', 1, 0, 'L', true);
                $pdf->Cell(12, 7, 'Actif', 1, 1, 'C', true);

                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                $fill = false;
            }
        }

        // Pied de page avec statistiques
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor($couleurSecondaire[0], $couleurSecondaire[1], $couleurSecondaire[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 6, 'RÉSUMÉ', 0, 1, 'L', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $actifs = count(array_filter($utilisateurs, fn($u) => $u->getActif()));
        $inactifs = count($utilisateurs) - $actifs;

        $pdf->Cell(50, 5, 'Total: ' . count($utilisateurs) . ' utilisateur(s)', 0, 0);
        $pdf->Cell(50, 5, 'Actifs: ' . $actifs, 0, 0);
        $pdf->Cell(0, 5, 'Inactifs: ' . $inactifs, 0, 1);
    }

    /**
     * Génère le contenu du PDF au format détaillé
     */
    private function generateDetailedPdfContent(\TCPDF $pdf, array $utilisateurs, array $couleurPrimaire, array $couleurSecondaire, array $couleurFond): void
    {
        foreach ($utilisateurs as $index => $user) {
            if ($index > 0) {
                $pdf->AddPage();
            }

            // Titre avec nom et prénom
            $pdf->SetFont('helvetica', 'B', 13);
            $pdf->SetFillColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 8, strtoupper($user->getNom() . ' ' . $user->getPrenom()), 0, 1, 'L', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(2);

            // Informations personnelles
            $this->addModernSection($pdf, 'INFORMATIONS PERSONNELLES', $couleurPrimaire, $couleurSecondaire, [
                'Email' => $user->getEmail(),
                'Téléphone' => $user->getTelephone() ?? 'N/A',
                'Adresse' => $user->getAdresse() ?? 'N/A',
                'Ville' => $user->getVille() ?? 'N/A',
                'Code Postal' => $user->getCodePostal() ?? 'N/A',
                'Date de Naissance' => $user->getDateNaissance()?->format('d/m/Y') ?? 'N/A',
                'Sexe' => $user->getSexe() ?? 'N/A',
                'Nationalité' => $user->getNationalite() ?? 'N/A',
            ]);

            // Informations professionnelles
            $this->addModernSection($pdf, 'INFORMATIONS PROFESSIONNELLES', $couleurPrimaire, $couleurSecondaire, [
                'Login' => $user->getLogin(),
                'Numéro de Licence' => $user->getNumeroLicence() ?? 'N/A',
                'Numéro d\'Ordre' => $user->getNumeroOrdre() ?? 'N/A',
                'Date d\'Embauche' => $user->getDateEmbauche()?->format('d/m/Y') ?? 'N/A',
                'Spécialité' => $user->getSpecialiteId()?->getNom() ?? 'N/A',
            ]);

            // Informations administratives
            $this->addModernSection($pdf, 'INFORMATIONS ADMINISTRATIVES', $couleurPrimaire, $couleurSecondaire, [
                'Hôpital' => $user->getHopitalId()->getNom(),
                'Rôle' => $user->getRoleId()->getNom(),
                'Profil' => $user->getProfilId()->getNom(),
                'Actif' => $user->getActif() ? 'Oui' : 'Non',
                'Compte Verrouillé' => $user->getCompteVerrouille() ? 'Oui' : 'Non',
                'Authentification 2FA' => $user->getAuthentification2fa() ? 'Activée' : 'Désactivée',
            ]);

            // Historique
            $this->addModernSection($pdf, 'HISTORIQUE', $couleurPrimaire, $couleurSecondaire, [
                'Date de Création' => $user->getDateCreation()?->format('d/m/Y H:i') ?? 'N/A',
                'Date de Modification' => $user->getDateModification()?->format('d/m/Y H:i') ?? 'N/A',
                'Dernière Connexion' => $user->getDerniereConnexion()?->format('d/m/Y H:i') ?? 'N/A',
            ]);
        }
    }

    /**
     * Ajoute une section moderne au PDF
     */
    private function addModernSection(\TCPDF $pdf, string $title, array $couleurPrimaire, array $couleurSecondaire, array $data): void
    {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor($couleurSecondaire[0], $couleurSecondaire[1], $couleurSecondaire[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
        $pdf->SetLineWidth(0.5);

        $pdf->Cell(0, 6, ' ' . $title, 0, 1, 'L', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        $rowIndex = 0;
        foreach ($data as $label => $value) {
            $bgColor = ($rowIndex % 2 == 0) ? [247, 249, 251] : [255, 255, 255];
            $pdf->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
            $pdf->SetDrawColor(220, 220, 220);

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetTextColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
            $pdf->Cell(40, 5, $label . ':', 0, 0, 'L', true);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(40, 40, 40);
            $pdf->Cell(0, 5, substr($value, 0, 100), 0, 1, 'L', true);

            $rowIndex++;
        }

        $pdf->Ln(2);
    }

    /**
     * Génère un PDF pour un seul utilisateur avec toutes les informations détaillées
     */
    private function generateSingleUserPdf(Utilisateurs $utilisateur): \TCPDF
    {
        // Couleurs professionnelles (bleu médical)
        $couleurPrimaire = [41, 128, 185];      // Bleu
        $couleurSecondaire = [52, 152, 219];    // Bleu clair
        $couleurFond = [236, 240, 241];         // Gris clair

        // Chemin du logo
        $logoPath = null;
        try {
            $logoPath = $this->getParameter('kernel.project_dir') . '/public/assets/image_rehoboth.png';
            if (!file_exists($logoPath)) {
                $logoPath = null;
            }
        } catch (Exception $e) {
            $logoPath = null;
        }

        // Créer une classe TCPDF personnalisée pour les en-têtes/pieds de page
        $pdf = new class extends \TCPDF {
            public $hospitalName = '';
            public $logoPath = '';
            public $couleurPrimaire = [];

            public function Header(): void
            {
                $this->SetMargins(12, 12, 12);
                
                // Fond de l'en-tête
                $this->SetFillColor($this->couleurPrimaire[0], $this->couleurPrimaire[1], $this->couleurPrimaire[2]);
                $this->Rect(0, 0, $this->GetPageWidth(), 22, 'F');

                // Logo
                if ($this->logoPath && file_exists($this->logoPath)) {
                    try {
                        $this->Image($this->logoPath, 12, 3, 18, 18, 'PNG');
                    } catch (Exception $e) {
                        // Continuer sans logo
                    }
                }

                // Texte d'en-tête
                $this->SetFont('helvetica', 'B', 14);
                $this->SetTextColor(255, 255, 255);
                $this->SetXY(32, 5);
                $this->Cell(0, 7, 'FICHE UTILISATEUR', 0, 1, 'L');

                $this->SetFont('helvetica', '', 9);
                $this->SetXY(32, 12);
                $this->Cell(0, 5, $this->hospitalName, 0, 1, 'L');

                // Ligne de séparation
                $this->SetDrawColor(255, 255, 255);
                $this->SetLineWidth(0.5);
                $this->Line(12, 22, $this->GetPageWidth() - 12, 22);

                $this->SetTextColor(0, 0, 0);
                $this->SetY(25);
            }

            public function Footer(): void
            {
                $this->SetY(-15);
                $this->SetFont('helvetica', 'I', 8);
                $this->SetTextColor(100, 100, 100);

                // Ligne de séparation
                $this->SetDrawColor(200, 200, 200);
                $this->SetLineWidth(0.3);
                $this->Line(12, $this->GetY() - 2, $this->GetPageWidth() - 12, $this->GetY() - 2);

                // Contenu du pied de page
                $this->Cell(0, 5, 'Généré le ' . date('d/m/Y à H:i:s'), 0, 0, 'L');
                $this->Cell(0, 5, 'Page ' . $this->getPage(), 0, 1, 'R');
            }
        };

        // Configuration du PDF
        $pdf->SetCreator('Rehoboth Hospital Management System');
        $pdf->SetAuthor('Rehoboth Hospital');
        $pdf->SetTitle('Fiche Utilisateur');
        $pdf->SetSubject('Détails de l\'utilisateur');
        $pdf->SetDefaultMonospacedFont(\PDF_FONT_MONOSPACED);
        $pdf->SetMargins(12, 35, 12);
        $pdf->SetAutoPageBreak(true, 25);

        // Passer les données à la classe
        $pdf->hospitalName = $utilisateur->getHopitalId()->getNom();
        $pdf->logoPath = $logoPath ?? '';
        $pdf->couleurPrimaire = $couleurPrimaire;

        // Ajouter une page
        $pdf->AddPage();

        // Titre avec nom et prénom
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetFillColor($couleurPrimaire[0], $couleurPrimaire[1], $couleurPrimaire[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, strtoupper($utilisateur->getNom() . ' ' . $utilisateur->getPrenom()), 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(3);

        // Informations personnelles
        $this->addModernSection($pdf, 'INFORMATIONS PERSONNELLES', $couleurPrimaire, $couleurSecondaire, [
            'Email' => $utilisateur->getEmail(),
            'Téléphone' => $utilisateur->getTelephone() ?? 'N/A',
            'Adresse' => $utilisateur->getAdresse() ?? 'N/A',
            'Ville' => $utilisateur->getVille() ?? 'N/A',
            'Code Postal' => $utilisateur->getCodePostal() ?? 'N/A',
            'Date de Naissance' => $utilisateur->getDateNaissance()?->format('d/m/Y') ?? 'N/A',
            'Sexe' => $utilisateur->getSexe() ?? 'N/A',
            'Nationalité' => $utilisateur->getNationalite() ?? 'N/A',
            'Type d\'Identité' => $utilisateur->getTypeIdentite() ?? 'N/A',
            'Numéro d\'Identité' => $utilisateur->getNumeroIdentite() ?? 'N/A',
            'Contact d\'Urgence' => $utilisateur->getContactUrgenceNom() ?? 'N/A',
            'Téléphone d\'Urgence' => $utilisateur->getTelephoneUrgence() ?? 'N/A',
        ]);

        // Informations professionnelles
        $this->addModernSection($pdf, 'INFORMATIONS PROFESSIONNELLES', $couleurPrimaire, $couleurSecondaire, [
            'Login' => $utilisateur->getLogin(),
            'Numéro de Licence' => $utilisateur->getNumeroLicence() ?? 'N/A',
            'Numéro d\'Ordre' => $utilisateur->getNumeroOrdre() ?? 'N/A',
            'Date d\'Embauche' => $utilisateur->getDateEmbauche()?->format('d/m/Y') ?? 'N/A',
            'Spécialité' => $utilisateur->getSpecialiteId()?->getNom() ?? 'N/A',
        ]);

        // Informations administratives
        $this->addModernSection($pdf, 'INFORMATIONS ADMINISTRATIVES', $couleurPrimaire, $couleurSecondaire, [
            'Hôpital' => $utilisateur->getHopitalId()->getNom(),
            'Rôle' => $utilisateur->getRoleId()->getNom(),
            'Profil' => $utilisateur->getProfilId()->getNom(),
            'Actif' => $utilisateur->getActif() ? 'Oui' : 'Non',
            'Compte Verrouillé' => $utilisateur->getCompteVerrouille() ? 'Oui' : 'Non',
            'Authentification 2FA' => $utilisateur->getAuthentification2fa() ? 'Activée' : 'Désactivée',
        ]);

        // Historique
        $this->addModernSection($pdf, 'HISTORIQUE', $couleurPrimaire, $couleurSecondaire, [
            'Date de Création' => $utilisateur->getDateCreation()?->format('d/m/Y H:i') ?? 'N/A',
            'Date de Modification' => $utilisateur->getDateModification()?->format('d/m/Y H:i') ?? 'N/A',
            'Dernière Connexion' => $utilisateur->getDerniereConnexion()?->format('d/m/Y H:i') ?? 'N/A',
        ]);

        return $pdf;
    }

    /**
     * Génère un PDF au format liste (tableau)
     */
    private function generateListPdf(\TCPDF $pdf, array $utilisateurs): void
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(66, 139, 202);
        $pdf->SetTextColor(255, 255, 255);

        // En-têtes du tableau
        $pdf->Cell(8, 7, 'ID', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Nom', 1, 0, 'L', true);
        $pdf->Cell(25, 7, 'Prénom', 1, 0, 'L', true);
        $pdf->Cell(35, 7, 'Email', 1, 0, 'L', true);
        $pdf->Cell(20, 7, 'Rôle', 1, 0, 'L', true);
        $pdf->Cell(20, 7, 'Profil', 1, 0, 'L', true);
        $pdf->Cell(15, 7, 'Actif', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;

        // Données
        foreach ($utilisateurs as $user) {
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            
            $pdf->Cell(8, 6, $user->getId(), 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, substr($user->getNom(), 0, 20), 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, substr($user->getPrenom(), 0, 20), 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, substr($user->getEmail(), 0, 30), 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, substr($user->getRoleId()->getNom(), 0, 15), 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, substr($user->getProfilId()->getNom(), 0, 15), 1, 0, 'L', $fill);
            $pdf->Cell(15, 6, $user->getActif() ? 'Oui' : 'Non', 1, 1, 'C', $fill);
            
            $fill = !$fill;

            // Vérifier si on a besoin d'une nouvelle page
            if ($pdf->GetY() > 270) {
                $pdf->AddPage('P', 'A4');
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetFillColor(66, 139, 202);
                $pdf->SetTextColor(255, 255, 255);

                // Répéter les en-têtes
                $pdf->Cell(8, 7, 'ID', 1, 0, 'C', true);
                $pdf->Cell(25, 7, 'Nom', 1, 0, 'L', true);
                $pdf->Cell(25, 7, 'Prénom', 1, 0, 'L', true);
                $pdf->Cell(35, 7, 'Email', 1, 0, 'L', true);
                $pdf->Cell(20, 7, 'Rôle', 1, 0, 'L', true);
                $pdf->Cell(20, 7, 'Profil', 1, 0, 'L', true);
                $pdf->Cell(15, 7, 'Actif', 1, 1, 'C', true);

                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                $fill = false;
            }
        }

        // Pied de page avec statistiques
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Total: ' . count($utilisateurs) . ' utilisateur(s)', 0, 1, 'R');
    }

    /**
     * Génère un PDF au format détaillé (une page par utilisateur)
     */
    private function generateDetailedPdf(\TCPDF $pdf, array $utilisateurs): void
    {
        foreach ($utilisateurs as $index => $user) {
            if ($index > 0) {
                $pdf->AddPage('P', 'A4');
            }

            // Titre avec nom et prénom
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetFillColor(66, 139, 202);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 10, $user->getNom() . ' ' . $user->getPrenom(), 0, 1, 'L', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(3);

            // Informations personnelles
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetFillColor(200, 220, 240);
            $pdf->Cell(0, 7, 'INFORMATIONS PERSONNELLES', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);

            $this->addDetailRow($pdf, 'Email:', $user->getEmail());
            $this->addDetailRow($pdf, 'Téléphone:', $user->getTelephone() ?? 'N/A');
            $this->addDetailRow($pdf, 'Adresse:', $user->getAdresse() ?? 'N/A');
            $this->addDetailRow($pdf, 'Ville:', $user->getVille() ?? 'N/A');
            $this->addDetailRow($pdf, 'Code Postal:', $user->getCodePostal() ?? 'N/A');
            $this->addDetailRow($pdf, 'Date de Naissance:', $user->getDateNaissance()?->format('d/m/Y') ?? 'N/A');
            $this->addDetailRow($pdf, 'Sexe:', $user->getSexe() ?? 'N/A');
            $this->addDetailRow($pdf, 'Nationalité:', $user->getNationalite() ?? 'N/A');
            $this->addDetailRow($pdf, 'Type d\'Identité:', $user->getTypeIdentite() ?? 'N/A');
            $this->addDetailRow($pdf, 'Numéro d\'Identité:', $user->getNumeroIdentite() ?? 'N/A');
            $this->addDetailRow($pdf, 'Contact d\'Urgence:', $user->getContactUrgenceNom() ?? 'N/A');
            $this->addDetailRow($pdf, 'Téléphone d\'Urgence:', $user->getTelephoneUrgence() ?? 'N/A');

            $pdf->Ln(3);

            // Informations professionnelles
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetFillColor(200, 220, 240);
            $pdf->Cell(0, 7, 'INFORMATIONS PROFESSIONNELLES', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);

            $this->addDetailRow($pdf, 'Login:', $user->getLogin());
            $this->addDetailRow($pdf, 'Numéro de Licence:', $user->getNumeroLicence() ?? 'N/A');
            $this->addDetailRow($pdf, 'Numéro d\'Ordre:', $user->getNumeroOrdre() ?? 'N/A');
            $this->addDetailRow($pdf, 'Date d\'Embauche:', $user->getDateEmbauche()?->format('d/m/Y') ?? 'N/A');
            $this->addDetailRow($pdf, 'Spécialité:', $user->getSpecialiteId()?->getNom() ?? 'N/A');
            $this->addDetailRow($pdf, 'Bio:', substr($user->getBio() ?? 'N/A', 0, 100));

            $pdf->Ln(3);

            // Informations administratives
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetFillColor(200, 220, 240);
            $pdf->Cell(0, 7, 'INFORMATIONS ADMINISTRATIVES', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);

            $this->addDetailRow($pdf, 'Hôpital:', $user->getHopitalId()->getNom());
            $this->addDetailRow($pdf, 'Rôle:', $user->getRoleId()->getNom());
            $this->addDetailRow($pdf, 'Profil:', $user->getProfilId()->getNom());
            $this->addDetailRow($pdf, 'Actif:', $user->getActif() ? 'Oui' : 'Non');
            $this->addDetailRow($pdf, 'Compte Verrouillé:', $user->getCompteVerrouille() ? 'Oui' : 'Non');
            $this->addDetailRow($pdf, 'Authentification 2FA:', $user->getAuthentification2fa() ? 'Activée' : 'Désactivée');

            $pdf->Ln(3);

            // Historique
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetFillColor(200, 220, 240);
            $pdf->Cell(0, 7, 'HISTORIQUE', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);

            $this->addDetailRow($pdf, 'Date de Création:', $user->getDateCreation()?->format('d/m/Y H:i') ?? 'N/A');
            $this->addDetailRow($pdf, 'Date de Modification:', $user->getDateModification()?->format('d/m/Y H:i') ?? 'N/A');
            $this->addDetailRow($pdf, 'Dernière Connexion:', $user->getDerniereConnexion()?->format('d/m/Y H:i') ?? 'N/A');
            $this->addDetailRow($pdf, 'Dernier Changement MDP:', $user->getDateDernierChangementMdp()?->format('d/m/Y H:i') ?? 'N/A');
        }
    }

    /**
     * Ajoute une ligne de détail au PDF
     */
    private function addDetailRow(\TCPDF $pdf, string $label, string $value): void
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $labelWidth = 50;
        $pdf->Cell($labelWidth, 5, $label, 0, 0, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $valueWidth = $pdf->GetPageWidth() - 20 - $labelWidth;
        $pdf->MultiCell($valueWidth, 5, $value, 0, 'L');
    }

    /**
     * Génère un secret 2FA (TOTP) aléatoire
     */
    private function generate2FASecret(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $secret;
    }

    /**
     * Génère un QR code pour la 2FA avec OTPHP
     */
    private function generate2FAQRCode(Utilisateurs $utilisateur, string $secret): string
    {
        try {
            // Créer une instance TOTP avec le secret
            $totp = TOTP::create($secret);
            
            // Ajouter le label et l'issuer avec l'API fluente
            $totp = $totp->withLabel($utilisateur->getEmail());
            $totp = $totp->withIssuer('Rehoboth Hospital');
            
            // Obtenir l'URI d'authentification
            $provisioningUri = $totp->getProvisioningUri();
            
            // Utiliser une API de QR code publique et fiable
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . 
                urlencode($provisioningUri);
            
            // Essayer de récupérer l'image avec un timeout court
            $imageData = @file_get_contents(
                $qrUrl,
                false,
                stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'user_agent' => 'Rehoboth/1.0'
                    ]
                ])
            );
            
            // Si on a reçu une image valide
            if ($imageData !== false && !empty($imageData) && strlen($imageData) > 100) {
                return 'data:image/png;base64,' . base64_encode($imageData);
            }
            
            // En cas d'échec, retourner juste l'URI en base64
            return 'data:text/plain;base64,' . base64_encode($provisioningUri);
            
        } catch (Exception $e) {
            // En cas d'erreur, générer le provisioning URI manuellement
            try {
                $label = urlencode($utilisateur->getEmail());
                $issuer = urlencode('Rehoboth Hospital');
                $provisioningUri = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}";
                
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . 
                    urlencode($provisioningUri);
                
                $imageData = @file_get_contents($qrUrl, false, stream_context_create([
                    'http' => ['timeout' => 5]
                ]));
                
                if ($imageData !== false && !empty($imageData) && strlen($imageData) > 100) {
                    return 'data:image/png;base64,' . base64_encode($imageData);
                }
                
                return 'data:text/plain;base64,' . base64_encode($provisioningUri);
            } catch (Exception $e2) {
                return null;
            }
        }
    }

    /**
     * Vérifie un code TOTP 2FA avec OTPHP
     */
    private function verify2FACode(string $secret, string $code): bool
    {
        try {
            // Créer une instance TOTP avec le secret
            $totp = \OTPHP\TOTP::create($secret);
            
            // Vérifier le code avec une tolérance de ±1 intervalle (30 secondes)
            // Cela permet une marge d'erreur si l'horloge du client est légèrement désynchronisée
            return $totp->verify($code);
        } catch (Exception $e) {
            return false;
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

    /**
     * Génère un secret TOTP (Time-based One-Time Password) pour la 2FA
     * Utilise l'algorithme RFC 4648 Base32
     */
}
