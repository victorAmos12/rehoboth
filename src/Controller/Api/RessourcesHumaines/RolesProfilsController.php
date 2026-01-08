<?php

namespace App\Controller\Api\RessourcesHumaines;

use App\Entity\Personnel\Roles;
use App\Entity\Personnel\ProfilsUtilisateurs;
use App\Entity\Personnel\Permissions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTimeImmutable;
use Exception;

/**
 * Contrôleur API pour la gestion des rôles et profils utilisateurs
 * 
 * Gère:
 * - CRUD complet des rôles (Create, Read, Update, Delete)
 * - CRUD complet des profils (Create, Read, Update, Delete)
 * - Gestion des permissions par rôle
 * - Recherche et filtrage
 * - Statistiques
 */
#[Route('/api/roles-profils', name: 'api_roles_profils_')]
class RolesProfilsController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    // ==================== RÔLES ====================

    /**
     * Récupère la liste de tous les rôles
     * GET /api/roles-profils/roles
     */
    #[Route('/roles', name: 'roles_list', methods: ['GET'])]
    public function rolesList(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $search = trim($request->query->get('search', ''));
            $actif = $request->query->get('actif');

            $queryBuilder = $this->entityManager->getRepository(Roles::class)
                ->createQueryBuilder('r')
                ->leftJoin('r.permissions', 'p')
                ->addSelect('p');

            if (!empty($search)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('r.nom', ':search'),
                        $queryBuilder->expr()->like('r.code', ':search'),
                        $queryBuilder->expr()->like('r.description', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('r.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('r.dateCreation', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $roles = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Roles $role) {
                return $this->formatRoleData($role);
            }, $roles);

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
                'error' => 'Erreur lors de la récupération des rôles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails d'un rôle
     * GET /api/roles-profils/roles/{id}
     */
    #[Route('/roles/{id}', name: 'role_show', methods: ['GET'])]
    public function roleShow(int $id): JsonResponse
    {
        try {
            $role = $this->entityManager->getRepository(Roles::class)
                ->createQueryBuilder('r')
                ->leftJoin('r.permissions', 'p')
                ->addSelect('p')
                ->where('r.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatRoleData($role, true),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée un nouveau rôle
     * POST /api/roles-profils/roles
     * 
     * Champs requis:
     * - code: code unique du rôle
     * - nom: nom du rôle
     * 
     * Champs optionnels:
     * - description: description du rôle
     * - niveau_acces: niveau d'accès (0-10)
     * - actif: statut actif/inactif (défaut: true)
     */
    #[Route('/roles', name: 'role_create', methods: ['POST'])]
    public function roleCreate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Valider les champs requis
            if (!isset($data['code']) || empty($data['code']) || !isset($data['nom']) || empty($data['nom'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs code et nom sont requis',
                ], 400);
            }

            // Vérifier l'unicité du code
            $existingRole = $this->entityManager->getRepository(Roles::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingRole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Ce code de rôle est déjà utilisé',
                ], 409);
            }

            // Créer le rôle
            $role = new Roles();
            $role->setCode($data['code']);
            $role->setNom($data['nom']);

            if (isset($data['description'])) $role->setDescription($data['description']);
            if (isset($data['niveau_acces'])) $role->setNiveauAcces((int)$data['niveau_acces']);

            // Valider l'entité
            $errors = $this->validator->validate($role);
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

            $this->entityManager->persist($role);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Rôle créé avec succès',
                'data' => $this->formatRoleData($role),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour un rôle existant
     * PUT /api/roles-profils/roles/{id}
     */
    #[Route('/roles/{id}', name: 'role_update', methods: ['PUT'])]
    public function roleUpdate(int $id, Request $request): JsonResponse
    {
        try {
            $role = $this->entityManager->getRepository(Roles::class)->find($id);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['nom'])) $role->setNom($data['nom']);
            if (isset($data['description'])) $role->setDescription($data['description']);
            if (isset($data['niveau_acces'])) $role->setNiveauAcces((int)$data['niveau_acces']);
            if (isset($data['actif'])) $role->setActif($data['actif']);

            // Valider l'entité
            $errors = $this->validator->validate($role);
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
                'message' => 'Rôle mis à jour avec succès',
                'data' => $this->formatRoleData($role),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un rôle
     * DELETE /api/roles-profils/roles/{id}
     */
    #[Route('/roles/{id}', name: 'role_delete', methods: ['DELETE'])]
    public function roleDelete(int $id): JsonResponse
    {
        try {
            $role = $this->entityManager->getRepository(Roles::class)->find($id);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            // Soft delete - marquer comme inactif
            $role->setActif(false);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Rôle supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==================== PROFILS ====================

    /**
     * Récupère la liste de tous les profils
     * GET /api/roles-profils/profils
     */
    #[Route('/profils', name: 'profils_list', methods: ['GET'])]
    public function profilsList(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $search = trim($request->query->get('search', ''));
            $actif = $request->query->get('actif');

            $queryBuilder = $this->entityManager->getRepository(ProfilsUtilisateurs::class)
                ->createQueryBuilder('p');

            if (!empty($search)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('p.nom', ':search'),
                        $queryBuilder->expr()->like('p.code', ':search'),
                        $queryBuilder->expr()->like('p.description', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('p.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('p.dateCreation', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $profils = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (ProfilsUtilisateurs $profil) {
                return $this->formatProfilData($profil);
            }, $profils);

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
                'error' => 'Erreur lors de la récupération des profils: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails d'un profil
     * GET /api/roles-profils/profils/{id}
     */
    #[Route('/profils/{id}', name: 'profil_show', methods: ['GET'])]
    public function profilShow(int $id): JsonResponse
    {
        try {
            $profil = $this->entityManager->getRepository(ProfilsUtilisateurs::class)
                ->find($id);

            if (!$profil) {
                return $this->json([
                    'success' => false,
                    'error' => 'Profil non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatProfilData($profil, true),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du profil: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée un nouveau profil
     * POST /api/roles-profils/profils
     * 
     * Champs requis:
     * - code: code unique du profil
     * - nom: nom du profil
     * 
     * Champs optionnels:
     * - description: description du profil
     * - type_profil: type de profil
     * - icone: URL de l'icône
     * - couleur: couleur en format #RRGGBB
     * - actif: statut actif/inactif (défaut: true)
     */
    #[Route('/profils', name: 'profil_create', methods: ['POST'])]
    public function profilCreate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Valider les champs requis
            if (!isset($data['code']) || empty($data['code']) || !isset($data['nom']) || empty($data['nom'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs code et nom sont requis',
                ], 400);
            }

            // Vérifier l'unicité du code
            $existingProfil = $this->entityManager->getRepository(ProfilsUtilisateurs::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingProfil) {
                return $this->json([
                    'success' => false,
                    'error' => 'Ce code de profil est déjà utilisé',
                ], 409);
            }

            // Créer le profil
            $profil = new ProfilsUtilisateurs();
            $profil->setCode($data['code']);
            $profil->setNom($data['nom']);

            if (isset($data['description'])) $profil->setDescription($data['description']);
            if (isset($data['type_profil'])) $profil->setTypeProfil($data['type_profil']);
            if (isset($data['icone'])) $profil->setIcone($data['icone']);
            if (isset($data['couleur'])) $profil->setCouleur($data['couleur']);

            // Valider l'entité
            $errors = $this->validator->validate($profil);
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

            $this->entityManager->persist($profil);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Profil créé avec succès',
                'data' => $this->formatProfilData($profil),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création du profil: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour un profil existant
     * PUT /api/roles-profils/profils/{id}
     */
    #[Route('/profils/{id}', name: 'profil_update', methods: ['PUT'])]
    public function profilUpdate(int $id, Request $request): JsonResponse
    {
        try {
            $profil = $this->entityManager->getRepository(ProfilsUtilisateurs::class)->find($id);

            if (!$profil) {
                return $this->json([
                    'success' => false,
                    'error' => 'Profil non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['nom'])) $profil->setNom($data['nom']);
            if (isset($data['description'])) $profil->setDescription($data['description']);
            if (isset($data['type_profil'])) $profil->setTypeProfil($data['type_profil']);
            if (isset($data['icone'])) $profil->setIcone($data['icone']);
            if (isset($data['couleur'])) $profil->setCouleur($data['couleur']);
            if (isset($data['actif'])) $profil->setActif($data['actif']);

            // Valider l'entité
            $errors = $this->validator->validate($profil);
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
                'message' => 'Profil mis à jour avec succès',
                'data' => $this->formatProfilData($profil),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour du profil: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un profil
     * DELETE /api/roles-profils/profils/{id}
     */
    #[Route('/profils/{id}', name: 'profil_delete', methods: ['DELETE'])]
    public function profilDelete(int $id): JsonResponse
    {
        try {
            $profil = $this->entityManager->getRepository(ProfilsUtilisateurs::class)->find($id);

            if (!$profil) {
                return $this->json([
                    'success' => false,
                    'error' => 'Profil non trouvé',
                ], 404);
            }

            // Soft delete - marquer comme inactif
            $profil->setActif(false);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Profil supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression du profil: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des rôles et profils
     * GET /api/roles-profils/stats
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        try {
            $rolesRepository = $this->entityManager->getRepository(Roles::class);
            $profilsRepository = $this->entityManager->getRepository(ProfilsUtilisateurs::class);

            // Rôles
            $totalRoles = count($rolesRepository->findAll());
            $rolesActifs = count($rolesRepository->findBy(['actif' => true]));

            // Profils
            $totalProfils = count($profilsRepository->findAll());
            $profilsActifs = count($profilsRepository->findBy(['actif' => true]));

            return $this->json([
                'success' => true,
                'data' => [
                    'roles' => [
                        'total' => $totalRoles,
                        'actifs' => $rolesActifs,
                        'inactifs' => $totalRoles - $rolesActifs,
                    ],
                    'profils' => [
                        'total' => $totalProfils,
                        'actifs' => $profilsActifs,
                        'inactifs' => $totalProfils - $profilsActifs,
                    ],
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
     * Formate les données d'un rôle pour la réponse JSON
     */
    private function formatRoleData(Roles $role, bool $detailed = false): array
    {
        $data = [
            'id' => $role->getId(),
            'code' => $role->getCode(),
            'nom' => $role->getNom(),
            'description' => $role->getDescription(),
            'niveau_acces' => $role->getNiveauAcces(),
            'actif' => $role->getActif(),
            'date_creation' => $role->getDateCreation()?->format('c'),
        ];

        if ($detailed) {
            $permissions = $role->getPermissions();
            $data['permissions'] = array_map(function (Permissions $permission) {
                return [
                    'id' => $permission->getId(),
                    'code' => $permission->getCode(),
                    'nom' => $permission->getNom(),
                ];
            }, $permissions->toArray());
        }

        return $data;
    }

    /**
     * Formate les données d'un profil pour la réponse JSON
     */
    private function formatProfilData(ProfilsUtilisateurs $profil, bool $detailed = false): array
    {
        $data = [
            'id' => $profil->getId(),
            'code' => $profil->getCode(),
            'nom' => $profil->getNom(),
            'description' => $profil->getDescription(),
            'type_profil' => $profil->getTypeProfil(),
            'icone' => $profil->getIcone(),
            'couleur' => $profil->getCouleur(),
            'actif' => $profil->getActif(),
            'date_creation' => $profil->getDateCreation()?->format('c'),
        ];

        return $data;
    }
}
