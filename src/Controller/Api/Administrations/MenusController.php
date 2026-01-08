<?php

namespace App\Controller\Api\Administrations;

use App\Entity\Personnel\Utilisateurs;
use App\Entity\Administration\Menus;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/administrations', name: 'api_administrations_')]
class MenusController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JwtService $jwtService,
    ) {}

    #[Route('/menus', name: 'get_menus', methods: ['GET'])]
    public function getMenus(Request $request): JsonResponse
    {
        try {
            $authHeader = $request->headers->get('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json(['success' => false, 'error' => 'Token manquant ou invalide'], 401);
            }

            $token = substr($authHeader, 7);
            $utilisateurId = $this->verifyToken($token);
            if (!$utilisateurId) {
                return $this->json(['success' => false, 'error' => 'Token invalide ou expiré'], 401);
            }

            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($utilisateurId);
            if (!$utilisateur) {
                return $this->json(['success' => false, 'error' => 'Utilisateur non trouvé'], 404);
            }

            $role = $utilisateur->getRoleId();
            if (!$role) {
                return $this->json(['success' => false, 'error' => 'Rôle non assigné à l\'utilisateur'], 403);
            }

            // Récupérer tous les menus actifs du rôle
            $menus = $this->entityManager->getRepository(Menus::class)
                ->createQueryBuilder('m')
                ->innerJoin('m.roles', 'r')
                ->where('r.id = :roleId')
                ->andWhere('m.actif = true')
                ->setParameter('roleId', $role->getId())
                ->orderBy('m.ordre', 'ASC')
                ->getQuery()
                ->getResult();

            // Construire l'arborescence parent/enfant
            $menuTree = $this->buildMenuTree($menus);

            return $this->json([
                'success' => true,
                'menus' => $menuTree,
            ], 200);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/with-permissions', name: 'get_menus_with_permissions', methods: ['GET'])]
    public function getMenusWithPermissions(Request $request): JsonResponse
    {
        try {
            $authHeader = $request->headers->get('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json(['success' => false, 'error' => 'Token manquant ou invalide'], 401);
            }

            $token = substr($authHeader, 7);
            $utilisateurId = $this->verifyToken($token);
            if (!$utilisateurId) {
                return $this->json(['success' => false, 'error' => 'Token invalide ou expiré'], 401);
            }

            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($utilisateurId);
            if (!$utilisateur) {
                return $this->json(['success' => false, 'error' => 'Utilisateur non trouvé'], 404);
            }

            $role = $utilisateur->getRoleId();
            if (!$role) {
                return $this->json(['success' => false, 'error' => 'Rôle non assigné à l\'utilisateur'], 403);
            }

            $menus = $this->entityManager->getRepository(Menus::class)
                ->createQueryBuilder('m')
                ->innerJoin('m.roles', 'r')
                ->where('r.id = :roleId')
                ->andWhere('m.actif = true')
                ->setParameter('roleId', $role->getId())
                ->orderBy('m.ordre', 'ASC')
                ->getQuery()
                ->getResult();

            $permissions = $this->entityManager->getRepository(\App\Entity\Personnel\Permissions::class)
                ->createQueryBuilder('p')
                ->innerJoin('p.roles', 'r')
                ->where('r.id = :roleId')
                ->setParameter('roleId', $role->getId())
                ->getQuery()
                ->getResult();

            $permissionCodes = array_map(fn($p) => $p->getCode(), $permissions);

            $menuTree = $this->buildMenuTree($menus);

            return $this->json([
                'success' => true,
                'menus' => $menuTree,
                'permissions' => $permissionCodes,
                'role' => [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'nom' => $role->getNom(),
                ],
            ], 200);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Construire l'arborescence parent/enfant avec parent_id
     */
    private function buildMenuTree(array $menus): array
    {
        $menuMap = [];
        $tree = [];

        // Créer une map par ID
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

        // Associer enfants à leur parent
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

        // Trier parent et enfants
        usort($tree, fn($a, $b) => ($a['ordre'] ?? 0) <=> ($b['ordre'] ?? 0));
        foreach ($tree as &$item) {
            usort($item['children'], fn($a, $b) => ($a['ordre'] ?? 0) <=> ($b['ordre'] ?? 0));
        }

        return $tree;
    }

    private function verifyToken(string $token): ?int
    {
        try {
            $payload = $this->jwtService->validateToken($token);
            return $payload->id ?? null;
        } catch (ExpiredException|SignatureInvalidException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
