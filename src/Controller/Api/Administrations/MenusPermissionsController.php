<?php

namespace App\Controller\Api\Administrations;

use App\Entity\Administration\Menus;
use App\Entity\Personnel\Roles;
use App\Entity\Personnel\Permissions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Exception;

/**
 * Contrôleur API pour la gestion des menus et permissions
 * 
 * Gère:
 * - CRUD des menus
 * - Association des menus aux rôles
 * - Association des permissions aux rôles
 * - Récupération des menus par rôle
 * - Récupération des permissions par rôle
 */
#[Route('/api/administrations', name: 'api_administrations_')]
class MenusPermissionsController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    // ==================== MENUS ====================

    /**
     * Récupère la liste de tous les menus
     * GET /api/administrations/menus
     */
    #[Route('/menus', name: 'menus_list', methods: ['GET'])]
    public function menusList(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $actif = $request->query->get('actif');
            $roleId = $request->query->get('role_id');

            $queryBuilder = $this->entityManager->getRepository(Menus::class)
                ->createQueryBuilder('m')
                ->leftJoin('m.roles', 'r')
                ->addSelect('r');

            if ($actif !== null) {
                $queryBuilder->andWhere('m.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            if ($roleId) {
                $queryBuilder->andWhere(':roleId MEMBER OF m.roles')
                    ->setParameter('roleId', $roleId);
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('m.ordre', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $menus = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Menus $menu) {
                return $this->formatMenuData($menu);
            }, $menus);

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
                'error' => 'Erreur lors de la récupération des menus: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails d'un menu
     * GET /api/administrations/menus/{id}
     */
    #[Route('/menus/{id}', name: 'menu_show', methods: ['GET'])]
    public function menuShow(int $id): JsonResponse
    {
        try {
            $menu = $this->entityManager->getRepository(Menus::class)
                ->createQueryBuilder('m')
                ->leftJoin('m.roles', 'r')
                ->addSelect('r')
                ->where('m.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$menu) {
                return $this->json([
                    'success' => false,
                    'error' => 'Menu non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatMenuData($menu, true),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération du menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée un nouveau menu
     * POST /api/administrations/menus
     */
    #[Route('/menus', name: 'menu_create', methods: ['POST'])]
    public function menuCreate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['code']) || empty($data['code']) || !isset($data['nom']) || empty($data['nom'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs code et nom sont requis',
                ], 400);
            }

            $menu = new Menus();
            $menu->setCode($data['code']);
            $menu->setNom($data['nom']);

            if (isset($data['description'])) $menu->setDescription($data['description']);
            if (isset($data['icone'])) $menu->setIcone($data['icone']);
            if (isset($data['route'])) $menu->setRoute($data['route']);
            if (isset($data['module'])) $menu->setModule($data['module']);
            if (isset($data['parent_id'])) $menu->setParentId($data['parent_id']);
            if (isset($data['ordre'])) $menu->setOrdre((int)$data['ordre']);
            if (isset($data['actif'])) $menu->setActif($data['actif']);

            // Ajouter les rôles
            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                foreach ($data['role_ids'] as $roleId) {
                    $role = $this->entityManager->getRepository(Roles::class)->find($roleId);
                    if ($role) {
                        $menu->addRole($role);
                    }
                }
            }

            $errors = $this->validator->validate($menu);
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

            $this->entityManager->persist($menu);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Menu créé avec succès',
                'data' => $this->formatMenuData($menu),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création du menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour un menu
     * PUT /api/administrations/menus/{id}
     */
    #[Route('/menus/{id}', name: 'menu_update', methods: ['PUT'])]
    public function menuUpdate(int $id, Request $request): JsonResponse
    {
        try {
            $menu = $this->entityManager->getRepository(Menus::class)->find($id);

            if (!$menu) {
                return $this->json([
                    'success' => false,
                    'error' => 'Menu non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['nom'])) $menu->setNom($data['nom']);
            if (isset($data['description'])) $menu->setDescription($data['description']);
            if (isset($data['icone'])) $menu->setIcone($data['icone']);
            if (isset($data['route'])) $menu->setRoute($data['route']);
            if (isset($data['module'])) $menu->setModule($data['module']);
            if (isset($data['parent_id'])) $menu->setParentId($data['parent_id']);
            if (isset($data['ordre'])) $menu->setOrdre((int)$data['ordre']);
            if (isset($data['actif'])) $menu->setActif($data['actif']);

            // Mettre à jour les rôles
            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                // Supprimer tous les rôles actuels
                foreach ($menu->getRoles() as $role) {
                    $menu->removeRole($role);
                }
                // Ajouter les nouveaux rôles
                foreach ($data['role_ids'] as $roleId) {
                    $role = $this->entityManager->getRepository(Roles::class)->find($roleId);
                    if ($role) {
                        $menu->addRole($role);
                    }
                }
            }

            $errors = $this->validator->validate($menu);
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
                'message' => 'Menu mis à jour avec succès',
                'data' => $this->formatMenuData($menu),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour du menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un menu
     * DELETE /api/administrations/menus/{id}
     */
    #[Route('/menus/{id}', name: 'menu_delete', methods: ['DELETE'])]
    public function menuDelete(int $id): JsonResponse
    {
        try {
            $menu = $this->entityManager->getRepository(Menus::class)->find($id);

            if (!$menu) {
                return $this->json([
                    'success' => false,
                    'error' => 'Menu non trouvé',
                ], 404);
            }

            // Soft delete
            $menu->setActif(false);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Menu supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression du menu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Associe un rôle à un menu
     * POST /api/administrations/menus/{id}/add-role
     */
    #[Route('/menus/{id}/add-role', name: 'menu_add_role', methods: ['POST'])]
    public function menuAddRole(int $id, Request $request): JsonResponse
    {
        try {
            $menu = $this->entityManager->getRepository(Menus::class)->find($id);

            if (!$menu) {
                return $this->json([
                    'success' => false,
                    'error' => 'Menu non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['role_id'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ role_id est requis',
                ], 400);
            }

            $role = $this->entityManager->getRepository(Roles::class)->find($data['role_id']);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            if (!$menu->getRoles()->contains($role)) {
                $menu->addRole($role);
                $this->entityManager->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Rôle ajouté au menu avec succès',
                'data' => $this->formatMenuData($menu),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'ajout du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retire un rôle d'un menu
     * POST /api/administrations/menus/{id}/remove-role
     */
    #[Route('/menus/{id}/remove-role', name: 'menu_remove_role', methods: ['POST'])]
    public function menuRemoveRole(int $id, Request $request): JsonResponse
    {
        try {
            $menu = $this->entityManager->getRepository(Menus::class)->find($id);

            if (!$menu) {
                return $this->json([
                    'success' => false,
                    'error' => 'Menu non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['role_id'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ role_id est requis',
                ], 400);
            }

            $role = $this->entityManager->getRepository(Roles::class)->find($data['role_id']);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            if ($menu->getRoles()->contains($role)) {
                $menu->removeRole($role);
                $this->entityManager->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Rôle retiré du menu avec succès',
                'data' => $this->formatMenuData($menu),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors du retrait du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==================== PERMISSIONS ====================

    /**
     * Récupère la liste de toutes les permissions
     * GET /api/administrations/permissions
     */
    #[Route('/permissions', name: 'permissions_list', methods: ['GET'])]
    public function permissionsList(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $search = trim($request->query->get('search', ''));
            $module = $request->query->get('module');
            $roleId = $request->query->get('role_id');

            $queryBuilder = $this->entityManager->getRepository(Permissions::class)
                ->createQueryBuilder('p')
                ->leftJoin('p.roles', 'r')
                ->addSelect('r');

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

            if ($module) {
                $queryBuilder->andWhere('p.module = :module')
                    ->setParameter('module', $module);
            }

            if ($roleId) {
                $queryBuilder->andWhere(':roleId MEMBER OF p.roles')
                    ->setParameter('roleId', $roleId);
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('p.module', 'ASC')
                ->addOrderBy('p.code', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $permissions = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Permissions $permission) {
                return $this->formatPermissionData($permission);
            }, $permissions);

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
                'error' => 'Erreur lors de la récupération des permissions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails d'une permission
     * GET /api/administrations/permissions/{id}
     */
    #[Route('/permissions/{id}', name: 'permission_show', methods: ['GET'])]
    public function permissionShow(int $id): JsonResponse
    {
        try {
            $permission = $this->entityManager->getRepository(Permissions::class)
                ->createQueryBuilder('p')
                ->leftJoin('p.roles', 'r')
                ->addSelect('r')
                ->where('p.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$permission) {
                return $this->json([
                    'success' => false,
                    'error' => 'Permission non trouvée',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatPermissionData($permission, true),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de la permission: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée une nouvelle permission
     * POST /api/administrations/permissions
     */
    #[Route('/permissions', name: 'permission_create', methods: ['POST'])]
    public function permissionCreate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['code']) || empty($data['code']) || !isset($data['nom']) || empty($data['nom'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs code et nom sont requis',
                ], 400);
            }

            $permission = new Permissions();
            $permission->setCode($data['code']);
            $permission->setNom($data['nom']);

            if (isset($data['description'])) $permission->setDescription($data['description']);
            if (isset($data['module'])) $permission->setModule($data['module']);
            if (isset($data['action'])) $permission->setAction($data['action']);

            // Ajouter les rôles
            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                foreach ($data['role_ids'] as $roleId) {
                    $role = $this->entityManager->getRepository(Roles::class)->find($roleId);
                    if ($role) {
                        $permission->addRole($role);
                    }
                }
            }

            $errors = $this->validator->validate($permission);
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

            $this->entityManager->persist($permission);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Permission créée avec succès',
                'data' => $this->formatPermissionData($permission),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création de la permission: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour une permission
     * PUT /api/administrations/permissions/{id}
     */
    #[Route('/permissions/{id}', name: 'permission_update', methods: ['PUT'])]
    public function permissionUpdate(int $id, Request $request): JsonResponse
    {
        try {
            $permission = $this->entityManager->getRepository(Permissions::class)->find($id);

            if (!$permission) {
                return $this->json([
                    'success' => false,
                    'error' => 'Permission non trouvée',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['nom'])) $permission->setNom($data['nom']);
            if (isset($data['description'])) $permission->setDescription($data['description']);
            if (isset($data['module'])) $permission->setModule($data['module']);
            if (isset($data['action'])) $permission->setAction($data['action']);

            // Mettre à jour les rôles
            if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                foreach ($permission->getRoles() as $role) {
                    $permission->removeRole($role);
                }
                foreach ($data['role_ids'] as $roleId) {
                    $role = $this->entityManager->getRepository(Roles::class)->find($roleId);
                    if ($role) {
                        $permission->addRole($role);
                    }
                }
            }

            $errors = $this->validator->validate($permission);
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
                'message' => 'Permission mise à jour avec succès',
                'data' => $this->formatPermissionData($permission),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour de la permission: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime une permission
     * DELETE /api/administrations/permissions/{id}
     */
    #[Route('/permissions/{id}', name: 'permission_delete', methods: ['DELETE'])]
    public function permissionDelete(int $id): JsonResponse
    {
        try {
            $permission = $this->entityManager->getRepository(Permissions::class)->find($id);

            if (!$permission) {
                return $this->json([
                    'success' => false,
                    'error' => 'Permission non trouvée',
                ], 404);
            }

            $this->entityManager->remove($permission);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Permission supprimée avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de la permission: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Associe un rôle à une permission
     * POST /api/administrations/permissions/{id}/add-role
     */
    #[Route('/permissions/{id}/add-role', name: 'permission_add_role', methods: ['POST'])]
    public function permissionAddRole(int $id, Request $request): JsonResponse
    {
        try {
            $permission = $this->entityManager->getRepository(Permissions::class)->find($id);

            if (!$permission) {
                return $this->json([
                    'success' => false,
                    'error' => 'Permission non trouvée',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['role_id'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ role_id est requis',
                ], 400);
            }

            $role = $this->entityManager->getRepository(Roles::class)->find($data['role_id']);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            if (!$permission->getRoles()->contains($role)) {
                $permission->addRole($role);
                $this->entityManager->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Rôle ajouté à la permission avec succès',
                'data' => $this->formatPermissionData($permission),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'ajout du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retire un rôle d'une permission
     * POST /api/administrations/permissions/{id}/remove-role
     */
    #[Route('/permissions/{id}/remove-role', name: 'permission_remove_role', methods: ['POST'])]
    public function permissionRemoveRole(int $id, Request $request): JsonResponse
    {
        try {
            $permission = $this->entityManager->getRepository(Permissions::class)->find($id);

            if (!$permission) {
                return $this->json([
                    'success' => false,
                    'error' => 'Permission non trouvée',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['role_id'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ role_id est requis',
                ], 400);
            }

            $role = $this->entityManager->getRepository(Roles::class)->find($data['role_id']);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            if ($permission->getRoles()->contains($role)) {
                $permission->removeRole($role);
                $this->entityManager->flush();
            }

            return $this->json([
                'success' => true,
                'message' => 'Rôle retiré de la permission avec succès',
                'data' => $this->formatPermissionData($permission),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors du retrait du rôle: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les permissions d'un rôle
     * GET /api/administrations/roles/{roleId}/permissions
     */
    #[Route('/roles/{roleId}/permissions', name: 'role_permissions', methods: ['GET'])]
    public function rolePermissions(int $roleId): JsonResponse
    {
        try {
            $role = $this->entityManager->getRepository(Roles::class)->find($roleId);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            $permissions = $role->getPermissions();

            $data = array_map(function (Permissions $permission) {
                return $this->formatPermissionData($permission);
            }, $permissions->toArray());

            return $this->json([
                'success' => true,
                'role' => [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'nom' => $role->getNom(),
                ],
                'permissions' => $data,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des permissions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les menus d'un rôle
     * GET /api/administrations/roles/{roleId}/menus
     */
    #[Route('/roles/{roleId}/menus', name: 'role_menus', methods: ['GET'])]
    public function roleMenus(int $roleId): JsonResponse
    {
        try {
            $role = $this->entityManager->getRepository(Roles::class)->find($roleId);

            if (!$role) {
                return $this->json([
                    'success' => false,
                    'error' => 'Rôle non trouvé',
                ], 404);
            }

            $menus = $this->entityManager->getRepository(Menus::class)
                ->createQueryBuilder('m')
                ->innerJoin('m.roles', 'r')
                ->where('r.id = :roleId')
                ->andWhere('m.actif = true')
                ->setParameter('roleId', $roleId)
                ->orderBy('m.ordre', 'ASC')
                ->getQuery()
                ->getResult();

            $menuTree = $this->buildMenuTree($menus);

            return $this->json([
                'success' => true,
                'role' => [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'nom' => $role->getNom(),
                ],
                'menus' => $menuTree,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des menus: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formate les données d'un menu pour la réponse JSON
     */
    private function formatMenuData(Menus $menu, bool $detailed = false): array
    {
        $data = [
            'id' => $menu->getId(),
            'code' => $menu->getCode(),
            'nom' => $menu->getNom(),
            'description' => $menu->getDescription(),
            'icone' => $menu->getIcone(),
            'route' => $menu->getRoute(),
            'module' => $menu->getModule(),
            'parent_id' => $menu->getParentId(),
            'ordre' => $menu->getOrdre(),
            'actif' => $menu->isActif(),
        ];

        if ($detailed) {
            $roles = $menu->getRoles();
            $data['roles'] = array_map(function (Roles $role) {
                return [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'nom' => $role->getNom(),
                ];
            }, $roles->toArray());
        }

        return $data;
    }

    /**
     * Formate les données d'une permission pour la réponse JSON
     */
    private function formatPermissionData(Permissions $permission, bool $detailed = false): array
    {
        $data = [
            'id' => $permission->getId(),
            'code' => $permission->getCode(),
            'nom' => $permission->getNom(),
            'description' => $permission->getDescription(),
            'module' => $permission->getModule(),
            'action' => $permission->getAction(),
        ];

        if ($detailed) {
            $roles = $permission->getRoles();
            $data['roles'] = array_map(function (Roles $role) {
                return [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'nom' => $role->getNom(),
                ];
            }, $roles->toArray());
        }

        return $data;
    }

    /**
     * Construit l'arborescence parent/enfant des menus
     */
    private function buildMenuTree(array $menus): array
    {
        $menuMap = [];
        $tree = [];

        foreach ($menus as $menu) {
            $menuMap[$menu->getId()] = [
                'id' => $menu->getId(),
                'code' => $menu->getCode(),
                'nom' => $menu->getNom(),
                'description' => $menu->getDescription(),
                'icone' => $menu->getIcone(),
                'route' => $menu->getRoute(),
                'module' => $menu->getModule(),
                'ordre' => $menu->getOrdre(),
                'children' => [],
            ];
        }

        foreach ($menus as $menu) {
            $parentId = $menu->getParentId();
            if ($parentId === null) {
                $tree[] = &$menuMap[$menu->getId()];
            } else {
                if (isset($menuMap[$parentId])) {
                    $menuMap[$parentId]['children'][] = &$menuMap[$menu->getId()];
                }
            }
        }

        usort($tree, fn($a, $b) => ($a['ordre'] ?? 0) <=> ($b['ordre'] ?? 0));
        foreach ($tree as &$item) {
            usort($item['children'], fn($a, $b) => ($a['ordre'] ?? 0) <=> ($b['ordre'] ?? 0));
        }

        return $tree;
    }
}
