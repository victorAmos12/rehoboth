<?php

namespace App\Controller\Api;

use App\Service\PermissionService;
use App\Service\JwtService;
use App\Entity\Personnel\Utilisateurs;
use App\Entity\Administration\Menus;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur API pour l'authentification et les informations utilisateur
 * 
 * Fournit au login:
 * - Informations de l'utilisateur connecté
 * - Rôle et permissions (depuis la BD)
 * - Menus accessibles
 * - Capacités dynamiques par module
 * 
 * Tout est calculé dynamiquement depuis la base de données
 */
#[Route('/api/auth', name: 'api_auth_')]
class AuthInfoController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(
        private PermissionService $permissionService,
        private EntityManagerInterface $entityManager,
        private JwtService $jwtService,
    ) {
    }

    /**
     * Endpoint principal de login - retourne TOUTES les données nécessaires au frontend
     * GET /api/auth/me
     * 
     * Headers:
     * Authorization: Bearer {token}
     * 
     * Retourne:
     * - Informations utilisateur
     * - Rôle et permissions (depuis la BD)
     * - Menus accessibles
     * - Capacités par module (calculées dynamiquement)
     * 
     * C'est le seul endpoint à appeler après login
     */
    #[Route('/me', name: 'current_user', methods: ['GET'])]
    public function getCurrentUser(Request $request): JsonResponse
    {
        try {
            // Récupérer le token du header
            $authHeader = $request->headers->get('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json([
                    'success' => false,
                    'error' => 'Token manquant ou invalide',
                ], 401);
            }

            $token = substr($authHeader, 7);

            // Vérifier le token
            $utilisateurId = $this->verifyToken($token);

            if (!$utilisateurId) {
                return $this->json([
                    'success' => false,
                    'error' => 'Token invalide ou expiré',
                ], 401);
            }

            // Récupérer l'utilisateur
            $user = $this->entityManager->getRepository(Utilisateurs::class)
                ->find($utilisateurId);

            if (!$user) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $role = $user->getRoleId();
            
            // Récupérer les permissions depuis la BD
            $permissions = $this->permissionService->getUserPermissions();

            // Récupérer les menus accessibles
            $menus = $this->getAccessibleMenus($role->getId());

            // Calculer les capacités dynamiquement depuis les permissions
            $capabilities = $this->calculateCapabilitiesFromPermissions($permissions);

            return $this->json([
                'success' => true,
                'user' => [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'login' => $user->getLogin(),
                    'telephone' => $user->getTelephone(),
                    'photoProfil' => $user->getPhotoProfil(),
                    'actif' => $user->getActif(),
                    'hopital' => [
                        'id' => $user->getHopitalId()->getId(),
                        'nom' => $user->getHopitalId()->getNom(),
                    ],
                ],
                'role' => [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'nom' => $role->getNom(),
                    'niveauAcces' => $role->getNiveauAcces(),
                ],
                'permissions' => $permissions,
                'menus' => $menus,
                'capabilities' => $capabilities,
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les menus accessibles pour le rôle de l'utilisateur
     * 
     * @param int $roleId ID du rôle
     * @return array Arborescence des menus
     */
    private function getAccessibleMenus(int $roleId): array
    {
        $menus = $this->entityManager->getRepository(Menus::class)
            ->createQueryBuilder('m')
            ->innerJoin('m.roles', 'r')
            ->where('r.id = :roleId')
            ->andWhere('m.actif = true')
            ->setParameter('roleId', $roleId)
            ->orderBy('m.ordre', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->buildMenuTree($menus);
    }

    /**
     * Construit l'arborescence parent/enfant des menus
     * 
     * @param array $menus Menus à organiser
     * @return array Arborescence
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

    /**
     * Calcule les capacités (actions autorisées) dynamiquement depuis les permissions
     * 
     * Les permissions sont lues depuis la BD, donc aucun code statique
     * Chaque nouveau service/module ajouté doit avoir ses permissions en BD
     * 
     * @param array $permissionCodes Codes des permissions de l'utilisateur
     * @return array Capacités par module et action
     */
    private function calculateCapabilitiesFromPermissions(array $permissionCodes): array
    {
        // Récupérer toutes les permissions avec leurs modules et actions
        $allPermissions = $this->entityManager->getRepository(\App\Entity\Personnel\Permissions::class)
            ->createQueryBuilder('p')
            ->getQuery()
            ->getResult();

        // Organiser les permissions par module et action
        $permissionsByModule = [];
        foreach ($allPermissions as $permission) {
            $module = $permission->getModule() ?? 'general';
            $action = $permission->getAction() ?? 'access';
            
            if (!isset($permissionsByModule[$module])) {
                $permissionsByModule[$module] = [];
            }
            
            $permissionsByModule[$module][$action] = $permission->getCode();
        }

        // Construire les capacités basées sur les permissions de l'utilisateur
        $capabilities = [];
        foreach ($permissionsByModule as $module => $actions) {
            $capabilities[$module] = [];
            foreach ($actions as $action => $permissionCode) {
                $capabilities[$module][$action] = in_array($permissionCode, $permissionCodes);
            }
        }

        return $capabilities;
    }

    /**
     * Vérifie et décode un token JWT
     * 
     * @param string $token Token JWT à vérifier
     * @return int|null ID utilisateur si valide, null sinon
     */
    private function verifyToken(string $token): ?int
    {
        try {
            $payload = $this->jwtService->validateToken($token);
            return $payload->id ?? null;
        } catch (ExpiredException $e) {
            return null;
        } catch (SignatureInvalidException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * DEBUG ENDPOINT - Vérifier le secret 2FA stocké
     * GET /api/auth/debug/2fa/{userId}
     * 
     * ⚠️ À SUPPRIMER EN PRODUCTION !
     */
    // #[Route('/debug/2fa/{userId}', name: 'debug_2fa', methods: ['GET'])]
    // public function debug2FA(int $userId): JsonResponse
    // {
    //     try {
    //         $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($userId);
            
    //         if (!$utilisateur) {
    //             return $this->json(['error' => 'User not found'], 404);
    //         }
            
    //         $secret = $utilisateur->getSecret2fa();
            
    //         // Vérifier le secret
    //         if (!$secret) {
    //             return $this->json(['error' => '2FA not enabled for this user'], 400);
    //         }
            
    //         // Générer le code actuel
    //         try {
    //             $totp = \OTPHP\TOTP::create($secret);
    //             $currentCode = $totp->now();
                
    //             return $this->json([
    //                 'user_id' => $userId,
    //                 'login' => $utilisateur->getLogin(),
    //                 'email' => $utilisateur->getEmail(),
    //                 '2fa_enabled' => $utilisateur->getAuthentification2fa(),
    //                 'secret_stored' => $secret,
    //                 'secret_length' => strlen($secret),
    //                 'current_code' => $currentCode,
    //                 'server_time' => time(),
    //                 'server_datetime' => date('Y-m-d H:i:s'),
    //                 'message' => 'Enter the code above in Google Authenticator (should match)',
    //             ], 200);
    //         } catch (\Exception $e) {
    //             return $this->json([
    //                 'error' => 'Error generating TOTP: ' . $e->getMessage(),
    //                 'secret_stored' => $secret,
    //                 'secret_length' => strlen($secret),
    //             ], 500);
    //         }
            
    //     } catch (\Exception $e) {
    //         return $this->json(['error' => $e->getMessage()], 500);
    //     }
    // }

}

