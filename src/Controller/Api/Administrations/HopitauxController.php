<?php

namespace App\Controller\Api\Administrations;

use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTimeImmutable;
use Exception;

/**
 * Contrôleur API pour la gestion complète des hôpitaux
 * 
 * Gère:
 * - CRUD complet des hôpitaux (Create, Read, Update, Delete)
 * - Recherche et filtrage avancés
 * - Gestion des informations administratives
 * - Gestion des paramètres de branding (couleurs, logos)
 * - Statistiques des hôpitaux
 * - Récupération des services et utilisateurs par hôpital
 * 
 * Endpoints:
 * - GET /api/hopitaux - Lister tous les hôpitaux
 * - GET /api/hopitaux/{id} - Récupérer un hôpital
 * - POST /api/hopitaux - Créer un hôpital
 * - PUT /api/hopitaux/{id} - Modifier un hôpital
 * - DELETE /api/hopitaux/{id} - Supprimer un hôpital
 * - GET /api/hopitaux/{id}/details - Détails complets d'un hôpital
 * - GET /api/hopitaux/search - Recherche avancée
 * - GET /api/hopitaux/{id}/services - Services d'un hôpital
 * - GET /api/hopitaux/{id}/utilisateurs - Utilisateurs d'un hôpital
 * - GET /api/hopitaux/{id}/stats - Statistiques d'un hôpital
 */
#[Route('/api/hopitaux', name: 'api_hopitaux_')]
class HopitauxController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Récupère la liste de tous les hôpitaux avec pagination et filtrage
     * GET /api/hopitaux
     * 
     * Paramètres de requête:
     * - page: numéro de page (défaut: 1)
     * - limit: nombre d'éléments par page (défaut: 20, max: 100)
     * - search: recherche par nom, code, ville, email
     * - actif: filtrer par statut actif/inactif
     * - type: filtrer par type d'hôpital
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
            $actif = $request->query->get('actif');
            $type = $request->query->get('type');
            $sort = $request->query->get('sort', 'dateCreation');
            $order = strtoupper($request->query->get('order', 'DESC'));

            if (!in_array($order, ['ASC', 'DESC'])) {
                $order = 'DESC';
            }

            $queryBuilder = $this->entityManager->getRepository(Hopitaux::class)
                ->createQueryBuilder('h');

            // Appliquer les filtres
            if (!empty($search)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('LOWER(h.nom)', ':search'),
                        $queryBuilder->expr()->like('LOWER(h.code)', ':search'),
                        $queryBuilder->expr()->like('LOWER(h.ville)', ':search'),
                        $queryBuilder->expr()->like('LOWER(h.email)', ':search')
                    )
                )
                ->setParameter('search', '%' . strtolower($search) . '%');
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('h.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            if ($type !== null) {
                $queryBuilder->andWhere('h.typeHopital = :type')
                    ->setParameter('type', $type);
            }

            // Compter le total AVANT la pagination
            $countQueryBuilder = clone $queryBuilder;
            $total = count($countQueryBuilder->getQuery()->getResult());

            // Appliquer le tri et la pagination
            $queryBuilder->orderBy('h.' . $sort, $order)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $hopitaux = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Hopitaux $hopital) {
                return $this->formatHopitalData($hopital);
            }, $hopitaux);

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
                'search' => $search,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des hôpitaux: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère un hôpital par ID
     * GET /api/hopitaux/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($id);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatHopitalData($hopital, true),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails complets d'un hôpital
     * GET /api/hopitaux/{id}/details
     */
    #[Route('/{id}/details', name: 'show_details', methods: ['GET'])]
    public function showDetails(int $id): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($id);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            // Récupérer les statistiques
            $utilisateurs = $this->entityManager->getRepository(Utilisateurs::class)
                ->findBy(['hopitalId' => $hopital]);
            
            $nombreUtilisateurs = count($utilisateurs);
            $nombreUtilisateursActifs = count(array_filter($utilisateurs, fn($u) => $u->getActif()));

            $data = $this->formatHopitalData($hopital, true);
            $data['statistiques'] = [
                'nombre_utilisateurs' => $nombreUtilisateurs,
                'nombre_utilisateurs_actifs' => $nombreUtilisateursActifs,
            ];

            return $this->json([
                'success' => true,
                'data' => $data,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée un nouvel hôpital
     * POST /api/hopitaux
     * 
     * Champs requis:
     * - code, nom
     * 
     * Champs optionnels:
     * - adresse, ville, codePostal, telephone, email
     * - directeurId, typeHopital, nombreLits
     * - logoUrl, iconeUrl, couleurPrimaire, couleurSecondaire
     * - siteWeb, numeroSiret, numeroTva
     * - actif
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Valider les champs requis
            $requiredFields = ['code', 'nom'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '$field' est requis",
                    ], 400);
                }
            }

            // Vérifier l'unicité du code
            $existingCode = $this->entityManager->getRepository(Hopitaux::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingCode) {
                return $this->json([
                    'success' => false,
                    'error' => 'Un hôpital avec ce code existe déjà',
                ], 409);
            }

            // Créer l'hôpital
            $hopital = new Hopitaux();
            $hopital->setCode($data['code']);
            $hopital->setNom($data['nom']);
            $hopital->setAdresse($data['adresse'] ?? null);
            $hopital->setVille($data['ville'] ?? null);
            $hopital->setCodePostal($data['codePostal'] ?? null);
            $hopital->setTelephone($data['telephone'] ?? null);
            $hopital->setEmail($data['email'] ?? null);
            $hopital->setDirecteurId($data['directeurId'] ?? null);
            $hopital->setTypeHopital($data['typeHopital'] ?? null);
            $hopital->setNombreLits($data['nombreLits'] ?? null);
            $hopital->setLogoUrl($data['logoUrl'] ?? null);
            $hopital->setIconeUrl($data['iconeUrl'] ?? null);
            $hopital->setCouleurPrimaire($data['couleurPrimaire'] ?? null);
            $hopital->setCouleurSecondaire($data['couleurSecondaire'] ?? null);
            $hopital->setSiteWeb($data['siteWeb'] ?? null);
            $hopital->setNumeroSiret($data['numeroSiret'] ?? null);
            $hopital->setNumeroTva($data['numeroTva'] ?? null);
            $hopital->setActif($data['actif'] ?? true);

            // Valider l'entité
            $errors = $this->validator->validate($hopital);
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

            $this->entityManager->persist($hopital);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Hôpital créé avec succès',
                'data' => $this->formatHopitalData($hopital),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifie un hôpital existant
     * PUT /api/hopitaux/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($id);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['code'])) {
                // Vérifier l'unicité
                $existing = $this->entityManager->getRepository(Hopitaux::class)
                    ->findOneBy(['code' => $data['code']]);
                if ($existing && $existing->getId() !== $id) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Un hôpital avec ce code existe déjà',
                    ], 409);
                }
                $hopital->setCode($data['code']);
            }
            if (isset($data['nom'])) $hopital->setNom($data['nom']);
            if (isset($data['adresse'])) $hopital->setAdresse($data['adresse']);
            if (isset($data['ville'])) $hopital->setVille($data['ville']);
            if (isset($data['codePostal'])) $hopital->setCodePostal($data['codePostal']);
            if (isset($data['telephone'])) $hopital->setTelephone($data['telephone']);
            if (isset($data['email'])) $hopital->setEmail($data['email']);
            if (isset($data['directeurId'])) $hopital->setDirecteurId($data['directeurId']);
            if (isset($data['typeHopital'])) $hopital->setTypeHopital($data['typeHopital']);
            if (isset($data['nombreLits'])) $hopital->setNombreLits($data['nombreLits']);
            if (isset($data['logoUrl'])) $hopital->setLogoUrl($data['logoUrl']);
            if (isset($data['iconeUrl'])) $hopital->setIconeUrl($data['iconeUrl']);
            if (isset($data['couleurPrimaire'])) $hopital->setCouleurPrimaire($data['couleurPrimaire']);
            if (isset($data['couleurSecondaire'])) $hopital->setCouleurSecondaire($data['couleurSecondaire']);
            if (isset($data['siteWeb'])) $hopital->setSiteWeb($data['siteWeb']);
            if (isset($data['numeroSiret'])) $hopital->setNumeroSiret($data['numeroSiret']);
            if (isset($data['numeroTva'])) $hopital->setNumeroTva($data['numeroTva']);
            if (isset($data['actif'])) $hopital->setActif($data['actif']);

            $hopital->setDateModification(new DateTimeImmutable());

            // Valider l'entité
            $errors = $this->validator->validate($hopital);
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
                'message' => 'Hôpital modifié avec succès',
                'data' => $this->formatHopitalData($hopital),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un hôpital (soft delete - marque comme inactif)
     * DELETE /api/hopitaux/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($id);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            // Soft delete
            $hopital->setActif(false);
            $hopital->setDateModification(new DateTimeImmutable());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Hôpital supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recherche avancée d'hôpitaux
     * POST /api/hopitaux/search
     */
    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $page = max(1, (int)($data['page'] ?? 1));
            $limit = min(100, max(1, (int)($data['limit'] ?? 20)));

            $queryBuilder = $this->entityManager->getRepository(Hopitaux::class)
                ->createQueryBuilder('h');

            // Appliquer les filtres
            if (isset($data['nom']) && !empty($data['nom'])) {
                $queryBuilder->andWhere('LOWER(h.nom) LIKE :nom')
                    ->setParameter('nom', '%' . strtolower($data['nom']) . '%');
            }

            if (isset($data['code']) && !empty($data['code'])) {
                $queryBuilder->andWhere('LOWER(h.code) LIKE :code')
                    ->setParameter('code', '%' . strtolower($data['code']) . '%');
            }

            if (isset($data['ville']) && !empty($data['ville'])) {
                $queryBuilder->andWhere('LOWER(h.ville) LIKE :ville')
                    ->setParameter('ville', '%' . strtolower($data['ville']) . '%');
            }

            if (isset($data['email']) && !empty($data['email'])) {
                $queryBuilder->andWhere('LOWER(h.email) LIKE :email')
                    ->setParameter('email', '%' . strtolower($data['email']) . '%');
            }

            if (isset($data['typeHopital'])) {
                $queryBuilder->andWhere('h.typeHopital = :typeHopital')
                    ->setParameter('typeHopital', $data['typeHopital']);
            }

            if (isset($data['actif'])) {
                $queryBuilder->andWhere('h.actif = :actif')
                    ->setParameter('actif', filter_var($data['actif'], FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('h.dateCreation', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $hopitaux = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Hopitaux $hopital) {
                return $this->formatHopitalData($hopital);
            }, $hopitaux);

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
     * Récupère les utilisateurs d'un hôpital
     * GET /api/hopitaux/{id}/utilisateurs
     */
    #[Route('/{id}/utilisateurs', name: 'utilisateurs', methods: ['GET'])]
    public function utilisateurs(int $id, Request $request): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($id);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $actif = $request->query->get('actif');

            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->addSelect('h', 'r', 'p')
                ->where('u.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopital);

            if ($actif !== null) {
                $queryBuilder->andWhere('u.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('u.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $utilisateurs = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Utilisateurs $user) {
                return [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'login' => $user->getLogin(),
                    'telephone' => $user->getTelephone(),
                    'role' => $user->getRoleId()->getNom(),
                    'profil' => $user->getProfilId()->getNom(),
                    'actif' => $user->getActif(),
                    'date_creation' => $user->getDateCreation()?->format('Y-m-d H:i:s'),
                ];
            }, $utilisateurs);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'hopital' => [
                    'id' => $hopital->getId(),
                    'nom' => $hopital->getNom(),
                    'code' => $hopital->getCode(),
                ],
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
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les statistiques d'un hôpital
     * GET /api/hopitaux/{id}/stats
     */
    #[Route('/{id}/stats', name: 'stats', methods: ['GET'])]
    public function stats(int $id): JsonResponse
    {
        try {
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($id);

            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            // Récupérer les utilisateurs
            $utilisateurs = $this->entityManager->getRepository(Utilisateurs::class)
                ->findBy(['hopitalId' => $hopital]);

            $totalUtilisateurs = count($utilisateurs);
            $utilisateursActifs = count(array_filter($utilisateurs, fn($u) => $u->getActif()));
            $utilisateursInactifs = $totalUtilisateurs - $utilisateursActifs;
            $comptesVerrouilles = count(array_filter($utilisateurs, fn($u) => $u->getCompteVerrouille()));
            $avec2fa = count(array_filter($utilisateurs, fn($u) => $u->getAuthentification2fa()));

            // Statistiques par rôle
            $parRole = [];
            foreach ($utilisateurs as $user) {
                $roleName = $user->getRoleId()->getNom();
                $parRole[$roleName] = ($parRole[$roleName] ?? 0) + 1;
            }

            // Statistiques par profil
            $parProfil = [];
            foreach ($utilisateurs as $user) {
                $profilName = $user->getProfilId()->getNom();
                $parProfil[$profilName] = ($parProfil[$profilName] ?? 0) + 1;
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'hopital' => [
                        'id' => $hopital->getId(),
                        'nom' => $hopital->getNom(),
                        'code' => $hopital->getCode(),
                        'type' => $hopital->getTypeHopital(),
                        'nombre_lits' => $hopital->getNombreLits(),
                    ],
                    'utilisateurs' => [
                        'total' => $totalUtilisateurs,
                        'actifs' => $utilisateursActifs,
                        'inactifs' => $utilisateursInactifs,
                        'comptes_verrouilles' => $comptesVerrouilles,
                        'avec_2fa' => $avec2fa,
                        'par_role' => $parRole,
                        'par_profil' => $parProfil,
                    ],
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formate les données d'un hôpital pour la réponse JSON
     */
    private function formatHopitalData(Hopitaux $hopital, bool $detailed = false): array
    {
        $data = [
            'id' => $hopital->getId(),
            'code' => $hopital->getCode(),
            'nom' => $hopital->getNom(),
            'adresse' => $hopital->getAdresse(),
            'ville' => $hopital->getVille(),
            'code_postal' => $hopital->getCodePostal(),
            'telephone' => $hopital->getTelephone(),
            'email' => $hopital->getEmail(),
            'directeur_id' => $hopital->getDirecteurId(),
            'type_hopital' => $hopital->getTypeHopital(),
            'nombre_lits' => $hopital->getNombreLits(),
            'logo_url' => $hopital->getLogoUrl(),
            'icone_url' => $hopital->getIconeUrl(),
            'couleur_primaire' => $hopital->getCouleurPrimaire(),
            'couleur_secondaire' => $hopital->getCouleurSecondaire(),
            'site_web' => $hopital->getSiteWeb(),
            'numero_siret' => $hopital->getNumeroSiret(),
            'numero_tva' => $hopital->getNumeroTva(),
            'actif' => $hopital->getActif(),
            'date_creation' => $hopital->getDateCreation()?->format('Y-m-d H:i:s'),
            'date_modification' => $hopital->getDateModification()?->format('Y-m-d H:i:s'),
        ];

        if ($detailed) {
            // Ajouter les informations détaillées
            $data['informations_administratives'] = [
                'numero_siret' => $hopital->getNumeroSiret(),
                'numero_tva' => $hopital->getNumeroTva(),
                'type_hopital' => $hopital->getTypeHopital(),
                'nombre_lits' => $hopital->getNombreLits(),
            ];

            $data['informations_contact'] = [
                'telephone' => $hopital->getTelephone(),
                'email' => $hopital->getEmail(),
                'site_web' => $hopital->getSiteWeb(),
                'adresse' => $hopital->getAdresse(),
                'ville' => $hopital->getVille(),
                'code_postal' => $hopital->getCodePostal(),
            ];

            $data['branding'] = [
                'logo_url' => $hopital->getLogoUrl(),
                'icone_url' => $hopital->getIconeUrl(),
                'couleur_primaire' => $hopital->getCouleurPrimaire(),
                'couleur_secondaire' => $hopital->getCouleurSecondaire(),
            ];
        }

        return $data;
    }
}
