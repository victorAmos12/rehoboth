<?php

namespace App\Controller\Api;

use App\Entity\Personnel\Utilisateurs;
use App\Entity\Administration\Services;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Contrôleur de débogage pour les cartes de service
 */
#[Route('/api/debug', name: 'debug_')]
class DebugController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private \App\Service\ServiceCardGeneratorService $cardGenerator,
    ) {
    }

    /**
     * Test la génération d'une carte PDF
     * GET Je
     */
    #[Route('/card-pdf/{userId}/{serviceId}', name: 'card_pdf', methods: ['GET'])]
    public function testCardPdf(int $userId, int $serviceId): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($userId);
            if (!$utilisateur) {
                return $this->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            $service = $this->entityManager->getRepository(Services::class)->find($serviceId);
            if (!$service) {
                return $this->json(['error' => 'Service non trouvé'], 404);
            }

            // Test simple sans options
            $pdfContent = $this->cardGenerator->generateServiceCardPdf($service, $utilisateur, []);
            
            return $this->json([
                'success' => true,
                'message' => 'PDF généré avec succès',
                'size' => strlen($pdfContent),
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Test la génération d'une image
     * GET /api/debug/card-image/3/1
     */
    #[Route('/card-image/{userId}/{serviceId}', name: 'card_image', methods: ['GET'])]
    public function testCardImage(int $userId, int $serviceId): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($userId);
            if (!$utilisateur) {
                return $this->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            $service = $this->entityManager->getRepository(Services::class)->find($serviceId);
            if (!$service) {
                return $this->json(['error' => 'Service non trouvé'], 404);
            }

            // Test simple sans options
            $imageContent = $this->cardGenerator->generateServiceCardImage($service, $utilisateur, 'png', []);
            
            return $this->json([
                'success' => true,
                'message' => 'Image générée avec succès',
                'size' => strlen($imageContent),
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Vérifie les permissions
     * GET /api/debug/permissions/3/1
     */
    #[Route('/permissions/{userId}/{serviceId}', name: 'permissions', methods: ['GET'])]
    public function testPermissions(int $userId, int $serviceId): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)->find($userId);
            if (!$utilisateur) {
                return $this->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            $service = $this->entityManager->getRepository(Services::class)->find($serviceId);
            if (!$service) {
                return $this->json(['error' => 'Service non trouvé'], 404);
            }

            $permissionService = $this->container->get(\App\Service\ServiceCardPermissionService::class);
            
            return $this->json([
                'can_view' => $permissionService->canViewServiceCard($utilisateur, $service),
                'detail_level' => $permissionService->getDetailLevel($utilisateur, $service),
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
