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
use Exception;

/**
 * Contrôleur API pour le diagnostic et la configuration des rôles/permissions/menus
 * 
 * Utile pour:
 * - Vérifier les associations
 * - Corriger les problèmes d'affichage des menus
 * - Initialiser les données de base
 */
#[Route('/api/administrations', name: 'api_administrations_')]
class DiagnosticsController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Diagnostic complet du système de rôles/permissions/menus
     * GET /api/administrations/diagnostics
     */
    #[Route('/diagnostics', name: 'diagnostics', methods: ['GET'])]
    public function diagnostics(): JsonResponse
    {
        try {
            $rolesRepo = $this->entityManager->getRepository(Roles::class);
            $menusRepo = $this->entityManager->getRepository(Menus::class);
            $permissionsRepo = $this->entityManager->getRepository(Permissions::class);

            $roles = $rolesRepo->findAll();
            $menus = $menusRepo->findAll();
            $permissions = $permissionsRepo->findAll();

            $diagnostics = [
                'roles' => [],
                'menus' => [],
                'permissions' => [],
                'issues' => [],
            ];

            // Analyser les rôles
            foreach ($roles as $role) {
                $roleMenus = $menusRepo->createQueryBuilder('m')
                    ->innerJoin('m.roles', 'r')
                    ->where('r.id = :roleId')
                    ->setParameter('roleId', $role->getId())
                    ->getQuery()
                    ->getResult();

                $rolePermissions = $permissionsRepo->createQueryBuilder('p')
                    ->innerJoin('p.roles', 'r')
                    ->where('r.id = :roleId')
                    ->setParameter('roleId', $role->getId())
                    ->getQuery()
                    ->getResult();

                $diagnostics['roles'][] = [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'nom' => $role->getNom(),
                    'menus_count' => count($roleMenus),
                    'permissions_count' => count($rolePermissions),
                    'has_menus' => count($roleMenus) > 0,
                    'has_permissions' => count($rolePermissions) > 0,
                ];

                // Détecter les problèmes
                if (count($roleMenus) === 0) {
                    $diagnostics['issues'][] = [
                        'severity' => 'warning',
                        'type' => 'no_menus',
                        'message' => "Le rôle '{$role->getNom()}' n'a aucun menu associé",
                        'role_id' => $role->getId(),
                    ];
                }
            }

            // Analyser les menus
            foreach ($menus as $menu) {
                $menuRoles = $menu->getRoles();
                $diagnostics['menus'][] = [
                    'id' => $menu->getId(),
                    'code' => $menu->getCode(),
                    'nom' => $menu->getNom(),
                    'roles_count' => count($menuRoles),
                    'actif' => $menu->isActif(),
                    'has_roles' => count($menuRoles) > 0,
                ];

                if (count($menuRoles) === 0 && $menu->isActif()) {
                    $diagnostics['issues'][] = [
                        'severity' => 'warning',
                        'type' => 'no_roles',
                        'message' => "Le menu '{$menu->getNom()}' n'a aucun rôle associé",
                        'menu_id' => $menu->getId(),
                    ];
                }
            }

            // Analyser les permissions
            foreach ($permissions as $permission) {
                $permissionRoles = $permission->getRoles();
                $diagnostics['permissions'][] = [
                    'id' => $permission->getId(),
                    'code' => $permission->getCode(),
                    'nom' => $permission->getNom(),
                    'module' => $permission->getModule(),
                    'roles_count' => count($permissionRoles),
                    'has_roles' => count($permissionRoles) > 0,
                ];

                if (count($permissionRoles) === 0) {
                    $diagnostics['issues'][] = [
                        'severity' => 'info',
                        'type' => 'no_roles',
                        'message' => "La permission '{$permission->getNom()}' n'a aucun rôle associé",
                        'permission_id' => $permission->getId(),
                    ];
                }
            }

            return $this->json([
                'success' => true,
                'diagnostics' => $diagnostics,
                'summary' => [
                    'total_roles' => count($roles),
                    'total_menus' => count($menus),
                    'total_permissions' => count($permissions),
                    'total_issues' => count($diagnostics['issues']),
                    'warning_count' => count(array_filter($diagnostics['issues'], fn($i) => $i['severity'] === 'warning')),
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors du diagnostic: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Associe automatiquement tous les menus à un rôle
     * POST /api/administrations/fix/assign-all-menus-to-role
     */
    #[Route('/fix/assign-all-menus-to-role', name: 'fix_assign_all_menus', methods: ['POST'])]
    public function fixAssignAllMenusToRole(Request $request): JsonResponse
    {
        try {
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

            $menus = $this->entityManager->getRepository(Menus::class)
                ->findBy(['actif' => true]);

            $assignedCount = 0;
            foreach ($menus as $menu) {
                if (!$menu->getRoles()->contains($role)) {
                    $menu->addRole($role);
                    $assignedCount++;
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "Tous les menus ont été assignés au rôle '{$role->getNom()}'",
                'assigned_count' => $assignedCount,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'assignation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Associe automatiquement toutes les permissions à un rôle
     * POST /api/administrations/fix/assign-all-permissions-to-role
     */
    #[Route('/fix/assign-all-permissions-to-role', name: 'fix_assign_all_permissions', methods: ['POST'])]
    public function fixAssignAllPermissionsToRole(Request $request): JsonResponse
    {
        try {
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

            $permissions = $this->entityManager->getRepository(Permissions::class)->findAll();

            $assignedCount = 0;
            foreach ($permissions as $permission) {
                if (!$permission->getRoles()->contains($role)) {
                    $permission->addRole($role);
                    $assignedCount++;
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "Toutes les permissions ont été assignées au rôle '{$role->getNom()}'",
                'assigned_count' => $assignedCount,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'assignation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initialise les données de base (rôles, menus, permissions)
     * POST /api/administrations/init/setup-basic-data
     */
    #[Route('/init/setup-basic-data', name: 'init_setup_basic_data', methods: ['POST'])]
    public function initSetupBasicData(): JsonResponse
    {
        try {
            $rolesRepo = $this->entityManager->getRepository(Roles::class);
            $menusRepo = $this->entityManager->getRepository(Menus::class);
            $permissionsRepo = $this->entityManager->getRepository(Permissions::class);

            $createdRoles = [];
            $createdMenus = [];
            $createdPermissions = [];

            // Créer les rôles de base s'ils n'existent pas
            $rolesData = [
                ['code' => 'ADMIN', 'nom' => 'Administrateur', 'description' => 'Accès complet au système'],
                ['code' => 'MEDECIN', 'nom' => 'Médecin', 'description' => 'Accès aux fonctionnalités médicales'],
                ['code' => 'INFIRMIER', 'nom' => 'Infirmier', 'description' => 'Accès aux soins infirmiers'],
                ['code' => 'RECEPTIONNISTE', 'nom' => 'Réceptionniste', 'description' => 'Accès à la réception'],
                ['code' => 'PHARMACIEN', 'nom' => 'Pharmacien', 'description' => 'Accès à la pharmacie'],
            ];

            foreach ($rolesData as $roleData) {
                $existingRole = $rolesRepo->findOneBy(['code' => $roleData['code']]);
                if (!$existingRole) {
                    $role = new Roles();
                    $role->setCode($roleData['code']);
                    $role->setNom($roleData['nom']);
                    $role->setDescription($roleData['description']);
                    $role->setNiveauAcces(5);
                    $this->entityManager->persist($role);
                    $createdRoles[] = $roleData['code'];
                }
            }

            $this->entityManager->flush();

            // Créer les menus de base s'ils n'existent pas
            $menusData = [
                ['code' => 'DASHBOARD', 'nom' => 'Tableau de bord', 'route' => '/dashboard', 'ordre' => 1],
                ['code' => 'PATIENTS', 'nom' => 'Patients', 'route' => '/patients', 'ordre' => 2],
                ['code' => 'CONSULTATIONS', 'nom' => 'Consultations', 'route' => '/consultations', 'ordre' => 3],
                ['code' => 'UTILISATEURS', 'nom' => 'Utilisateurs', 'route' => '/utilisateurs', 'ordre' => 4],
                ['code' => 'PARAMETRES', 'nom' => 'Paramètres', 'route' => '/parametres', 'ordre' => 5],
            ];

            foreach ($menusData as $menuData) {
                $existingMenu = $menusRepo->findOneBy(['code' => $menuData['code']]);
                if (!$existingMenu) {
                    $menu = new Menus();
                    $menu->setCode($menuData['code']);
                    $menu->setNom($menuData['nom']);
                    $menu->setRoute($menuData['route']);
                    $menu->setOrdre($menuData['ordre']);
                    $menu->setActif(true);
                    $this->entityManager->persist($menu);
                    $createdMenus[] = $menuData['code'];
                }
            }

            $this->entityManager->flush();

            // Créer les permissions de base s'ils n'existent pas
            $permissionsData = [
                ['code' => 'VIEW_PATIENTS', 'nom' => 'Voir les patients', 'module' => 'patients', 'action' => 'view'],
                ['code' => 'CREATE_PATIENT', 'nom' => 'Créer un patient', 'module' => 'patients', 'action' => 'create'],
                ['code' => 'EDIT_PATIENT', 'nom' => 'Modifier un patient', 'module' => 'patients', 'action' => 'edit'],
                ['code' => 'DELETE_PATIENT', 'nom' => 'Supprimer un patient', 'module' => 'patients', 'action' => 'delete'],
                ['code' => 'VIEW_CONSULTATIONS', 'nom' => 'Voir les consultations', 'module' => 'consultations', 'action' => 'view'],
                ['code' => 'CREATE_CONSULTATION', 'nom' => 'Créer une consultation', 'module' => 'consultations', 'action' => 'create'],
                ['code' => 'VIEW_UTILISATEURS', 'nom' => 'Voir les utilisateurs', 'module' => 'utilisateurs', 'action' => 'view'],
                ['code' => 'MANAGE_UTILISATEURS', 'nom' => 'Gérer les utilisateurs', 'module' => 'utilisateurs', 'action' => 'manage'],
            ];

            foreach ($permissionsData as $permData) {
                $existingPerm = $permissionsRepo->findOneBy(['code' => $permData['code']]);
                if (!$existingPerm) {
                    $permission = new Permissions();
                    $permission->setCode($permData['code']);
                    $permission->setNom($permData['nom']);
                    $permission->setModule($permData['module']);
                    $permission->setAction($permData['action']);
                    $this->entityManager->persist($permission);
                    $createdPermissions[] = $permData['code'];
                }
            }

            $this->entityManager->flush();

            // Associer les menus et permissions aux rôles
            $adminRole = $rolesRepo->findOneBy(['code' => 'ADMIN']);
            $medecinRole = $rolesRepo->findOneBy(['code' => 'MEDECIN']);

            if ($adminRole) {
                $allMenus = $menusRepo->findAll();
                $allPermissions = $permissionsRepo->findAll();

                foreach ($allMenus as $menu) {
                    if (!$menu->getRoles()->contains($adminRole)) {
                        $menu->addRole($adminRole);
                    }
                }

                foreach ($allPermissions as $permission) {
                    if (!$permission->getRoles()->contains($adminRole)) {
                        $permission->addRole($adminRole);
                    }
                }
            }

            if ($medecinRole) {
                $menus = $menusRepo->findBy(['code' => ['DASHBOARD', 'PATIENTS', 'CONSULTATIONS']]);
                $permissions = $permissionsRepo->findBy(['module' => ['patients', 'consultations']]);

                foreach ($menus as $menu) {
                    if (!$menu->getRoles()->contains($medecinRole)) {
                        $menu->addRole($medecinRole);
                    }
                }

                foreach ($permissions as $permission) {
                    if (!$permission->getRoles()->contains($medecinRole)) {
                        $permission->addRole($medecinRole);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Données de base initialisées avec succès',
                'created' => [
                    'roles' => $createdRoles,
                    'menus' => $createdMenus,
                    'permissions' => $createdPermissions,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de l\'initialisation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les rôles sans menus
     * GET /api/administrations/roles-without-menus
     */
    #[Route('/roles-without-menus', name: 'roles_without_menus', methods: ['GET'])]
    public function rolesWithoutMenus(): JsonResponse
    {
        try {
            $rolesRepo = $this->entityManager->getRepository(Roles::class);
            $menusRepo = $this->entityManager->getRepository(Menus::class);

            $roles = $rolesRepo->findAll();
            $rolesWithoutMenus = [];

            foreach ($roles as $role) {
                $roleMenus = $menusRepo->createQueryBuilder('m')
                    ->innerJoin('m.roles', 'r')
                    ->where('r.id = :roleId')
                    ->setParameter('roleId', $role->getId())
                    ->getQuery()
                    ->getResult();

                if (count($roleMenus) === 0) {
                    $rolesWithoutMenus[] = [
                        'id' => $role->getId(),
                        'code' => $role->getCode(),
                        'nom' => $role->getNom(),
                    ];
                }
            }

            return $this->json([
                'success' => true,
                'roles_without_menus' => $rolesWithoutMenus,
                'count' => count($rolesWithoutMenus),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }
}
