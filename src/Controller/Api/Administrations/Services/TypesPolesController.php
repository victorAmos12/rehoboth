<?php

namespace App\Controller\Api\Administrations\Services;

use App\Entity\Administration\TypesPoles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

/**
 * Contrôleur API pour la gestion des types de pôles d'activité
 * 
 * Endpoints:
 * - GET /api/types-poles - Lister tous les types
 * - GET /api/types-poles/{id} - Récupérer un type
 * - POST /api/types-poles - Créer un type
 * - PUT /api/types-poles/{id} - Modifier un type
 * - DELETE /api/types-poles/{id} - Supprimer un type
 */
#[Route('/api/types-poles', name: 'api_types_poles_')]
class TypesPolesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Lister tous les types de pôles
     * GET /api/types-poles
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);
            $actif = $request->query->get('actif', null);

            $typesRepo = $this->entityManager->getRepository(TypesPoles::class);
            $queryBuilder = $typesRepo->createQueryBuilder('tp');

            if ($actif !== null) {
                $queryBuilder->where('tp.actif = :actif')
                    ->setParameter('actif', $actif === 'true' || $actif === '1');
            }

            $total = count($queryBuilder->getQuery()->getResult());

            $types = $queryBuilder
                ->orderBy('tp.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($types as $type) {
                $data[] = $this->serializeType($type);
            }

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
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
     * Récupérer un type par ID
     * GET /api/types-poles/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $type = $this->entityManager->getRepository(TypesPoles::class)->find($id);

            if (!$type) {
                return $this->json([
                    'success' => false,
                    'error' => 'Type non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializeType($type),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer un nouveau type
     * POST /api/types-poles
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $requiredFields = ['code', 'nom'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '{$field}' est requis",
                    ], 400);
                }
            }

            $existingType = $this->entityManager->getRepository(TypesPoles::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingType) {
                return $this->json([
                    'success' => false,
                    'error' => 'Un type avec ce code existe déjà',
                ], 409);
            }

            $type = new TypesPoles();
            $type->setCode($data['code']);
            $type->setNom($data['nom']);
            $type->setDescription($data['description'] ?? null);
            $type->setActif($data['actif'] ?? true);

            $this->entityManager->persist($type);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Type créé avec succès',
                'data' => $this->serializeType($type),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifier un type
     * PUT /api/types-poles/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $type = $this->entityManager->getRepository(TypesPoles::class)->find($id);

            if (!$type) {
                return $this->json([
                    'success' => false,
                    'error' => 'Type non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['code'])) {
                $type->setCode($data['code']);
            }
            if (isset($data['nom'])) {
                $type->setNom($data['nom']);
            }
            if (isset($data['description'])) {
                $type->setDescription($data['description']);
            }
            if (isset($data['actif'])) {
                $type->setActif($data['actif']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Type modifié avec succès',
                'data' => $this->serializeType($type),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un type
     * DELETE /api/types-poles/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $type = $this->entityManager->getRepository(TypesPoles::class)->find($id);

            if (!$type) {
                return $this->json([
                    'success' => false,
                    'error' => 'Type non trouvé',
                ], 404);
            }

            $this->entityManager->remove($type);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Type supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function serializeType(TypesPoles $type): array
    {
        return [
            'id' => $type->getId(),
            'code' => $type->getCode(),
            'nom' => $type->getNom(),
            'description' => $type->getDescription(),
            'actif' => $type->isActif(),
            'date_creation' => $type->getDateCreation()?->format('Y-m-d H:i:s'),
            'nombre_poles' => count($type->getPoles()),
        ];
    }
}
