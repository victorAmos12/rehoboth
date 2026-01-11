<?php

namespace App\Controller\Api\Administrations\Services;

use App\Entity\Patients\Chambres;
use App\Entity\Administration\Services;
use App\Entity\Administration\Hopitaux;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

/**
 * Contrôleur API pour la gestion des chambres hospitalières
 * 
 * Endpoints:
 * - GET /api/chambres - Lister toutes les chambres
 * - GET /api/chambres/{id} - Récupérer une chambre
 * - POST /api/chambres - Créer une chambre
 * - PUT /api/chambres/{id} - Modifier une chambre
 * - DELETE /api/chambres/{id} - Supprimer une chambre
 * - GET /api/chambres/service/{serviceId} - Lister les chambres d'un service
 * - GET /api/chambres/hopital/{hopitalId} - Lister les chambres d'un hôpital
 * - GET /api/chambres/type/{typeChambre} - Lister les chambres par type
 * - GET /api/chambres/statut/{statut} - Lister les chambres par statut
 */
#[Route('/api/chambres', name: 'api_chambres_')]
class ChambresController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Lister toutes les chambres
     * GET /api/chambres
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);
            $serviceId = $request->query->getInt('service_id', 0);
            $hopitalId = $request->query->getInt('hopital_id', 0);
            $typeChambre = $request->query->get('type_chambre', null);
            $statut = $request->query->get('statut', null);

            $chambresRepo = $this->entityManager->getRepository(Chambres::class);
            $queryBuilder = $chambresRepo->createQueryBuilder('c');

            if ($serviceId > 0) {
                $queryBuilder->where('c.serviceId = :serviceId')
                    ->setParameter('serviceId', $serviceId);
            }

            if ($hopitalId > 0) {
                $queryBuilder->andWhere('c.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($typeChambre !== null) {
                $queryBuilder->andWhere('c.typeChambre = :typeChambre')
                    ->setParameter('typeChambre', $typeChambre);
            }

            if ($statut !== null) {
                $queryBuilder->andWhere('c.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            $total = count($queryBuilder->getQuery()->getResult());

            $chambres = $queryBuilder
                ->orderBy('c.numeroChambre', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($chambres as $chambre) {
                $data[] = $this->serializeChambre($chambre);
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
                'error' => 'Erreur lors de la récupération des chambres: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer une chambre par ID
     * GET /api/chambres/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $chambre = $this->entityManager->getRepository(Chambres::class)->find($id);

            if (!$chambre) {
                return $this->json([
                    'success' => false,
                    'error' => 'Chambre non trouvée',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializeChambre($chambre),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer une nouvelle chambre
     * POST /api/chambres
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des champs requis
            $requiredFields = ['numero_chambre', 'service_id', 'hopital_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '{$field}' est requis",
                    ], 400);
                }
            }

            // Vérifier que le service existe
            $service = $this->entityManager->getRepository(Services::class)->find($data['service_id']);
            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            // Vérifier que l'hôpital existe
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($data['hopital_id']);
            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            $chambre = new Chambres();
            $chambre->setNumeroChambre($data['numero_chambre']);
            $chambre->setServiceId($service);
            $chambre->setHopitalId($hopital);
            $chambre->setEtage($data['etage'] ?? null);
            $chambre->setNombreLits($data['nombre_lits'] ?? null);
            $chambre->setTypeChambre($data['type_chambre'] ?? null);
            $chambre->setStatut($data['statut'] ?? 'disponible');
            $chambre->setDescription($data['description'] ?? null);
            $chambre->setLocalisation($data['localisation'] ?? null);
            $chambre->setClimatisee($data['climatisee'] ?? false);
            $chambre->setSanitairesPrives($data['sanitaires_prives'] ?? false);
            $chambre->setTelevision($data['television'] ?? false);
            $chambre->setTelephone($data['telephone'] ?? false);

            $this->entityManager->persist($chambre);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Chambre créée avec succès',
                'data' => $this->serializeChambre($chambre),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifier une chambre
     * PUT /api/chambres/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $chambre = $this->entityManager->getRepository(Chambres::class)->find($id);

            if (!$chambre) {
                return $this->json([
                    'success' => false,
                    'error' => 'Chambre non trouvée',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['numero_chambre'])) {
                $chambre->setNumeroChambre($data['numero_chambre']);
            }
            if (isset($data['etage'])) {
                $chambre->setEtage($data['etage']);
            }
            if (isset($data['nombre_lits'])) {
                $chambre->setNombreLits($data['nombre_lits']);
            }
            if (isset($data['type_chambre'])) {
                $chambre->setTypeChambre($data['type_chambre']);
            }
            if (isset($data['statut'])) {
                $chambre->setStatut($data['statut']);
            }
            if (isset($data['description'])) {
                $chambre->setDescription($data['description']);
            }
            if (isset($data['localisation'])) {
                $chambre->setLocalisation($data['localisation']);
            }
            if (isset($data['climatisee'])) {
                $chambre->setClimatisee($data['climatisee']);
            }
            if (isset($data['sanitaires_prives'])) {
                $chambre->setSanitairesPrives($data['sanitaires_prives']);
            }
            if (isset($data['television'])) {
                $chambre->setTelevision($data['television']);
            }
            if (isset($data['telephone'])) {
                $chambre->setTelephone($data['telephone']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Chambre modifiée avec succès',
                'data' => $this->serializeChambre($chambre),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer une chambre
     * DELETE /api/chambres/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $chambre = $this->entityManager->getRepository(Chambres::class)->find($id);

            if (!$chambre) {
                return $this->json([
                    'success' => false,
                    'error' => 'Chambre non trouvée',
                ], 404);
            }

            $this->entityManager->remove($chambre);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Chambre supprimée avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les chambres d'un service
     * GET /api/chambres/service/{serviceId}
     */
    #[Route('/service/{serviceId}', name: 'by_service', methods: ['GET'])]
    public function byService(int $serviceId, Request $request): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($serviceId);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $chambresRepo = $this->entityManager->getRepository(Chambres::class);
            $queryBuilder = $chambresRepo->createQueryBuilder('c')
                ->where('c.serviceId = :serviceId')
                ->setParameter('serviceId', $serviceId);

            $total = count($queryBuilder->getQuery()->getResult());

            $chambres = $queryBuilder
                ->orderBy('c.numeroChambre', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($chambres as $chambre) {
                $data[] = $this->serializeChambre($chambre);
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
     * Lister les chambres d'un hôpital
     * GET /api/chambres/hopital/{hopitalId}
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

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $chambresRepo = $this->entityManager->getRepository(Chambres::class);
            $queryBuilder = $chambresRepo->createQueryBuilder('c')
                ->where('c.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId);

            $total = count($queryBuilder->getQuery()->getResult());

            $chambres = $queryBuilder
                ->orderBy('c.numeroChambre', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($chambres as $chambre) {
                $data[] = $this->serializeChambre($chambre);
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
     * Lister les chambres par type
     * GET /api/chambres/type/{typeChambre}
     */
    #[Route('/type/{typeChambre}', name: 'by_type', methods: ['GET'])]
    public function byType(string $typeChambre, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $chambresRepo = $this->entityManager->getRepository(Chambres::class);
            $queryBuilder = $chambresRepo->createQueryBuilder('c')
                ->where('c.typeChambre = :typeChambre')
                ->setParameter('typeChambre', $typeChambre);

            $total = count($queryBuilder->getQuery()->getResult());

            $chambres = $queryBuilder
                ->orderBy('c.numeroChambre', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($chambres as $chambre) {
                $data[] = $this->serializeChambre($chambre);
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
     * Lister les chambres par statut
     * GET /api/chambres/statut/{statut}
     */
    #[Route('/statut/{statut}', name: 'by_statut', methods: ['GET'])]
    public function byStatut(string $statut, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $chambresRepo = $this->entityManager->getRepository(Chambres::class);
            $queryBuilder = $chambresRepo->createQueryBuilder('c')
                ->where('c.statut = :statut')
                ->setParameter('statut', $statut);

            $total = count($queryBuilder->getQuery()->getResult());

            $chambres = $queryBuilder
                ->orderBy('c.numeroChambre', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($chambres as $chambre) {
                $data[] = $this->serializeChambre($chambre);
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
     * Sérialiser une chambre pour la réponse JSON
     */
    private function serializeChambre(Chambres $chambre): array
    {
        return [
            'id' => $chambre->getId(),
            'numero_chambre' => $chambre->getNumeroChambre(),
            'etage' => $chambre->getEtage(),
            'nombre_lits' => $chambre->getNombreLits(),
            'type_chambre' => $chambre->getTypeChambre(),
            'statut' => $chambre->getStatut(),
            'description' => $chambre->getDescription(),
            'localisation' => $chambre->getLocalisation(),
            'climatisee' => $chambre->isClimatisee(),
            'sanitaires_prives' => $chambre->isSanitairesPrives(),
            'television' => $chambre->isTelevision(),
            'telephone' => $chambre->isTelephone(),
            'date_creation' => $chambre->getDateCreation()?->format('Y-m-d H:i:s'),
            'service_id' => $chambre->getServiceId()->getId(),
            'hopital_id' => $chambre->getHopitalId()->getId(),
            'nombre_lits_occupes' => count($chambre->getLits()),
        ];
    }
}
