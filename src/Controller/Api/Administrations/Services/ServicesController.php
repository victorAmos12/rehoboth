<?php

namespace App\Controller\Api\Administrations\Services;

use App\Entity\Administration\Services;
use App\Entity\Administration\PolesActivite;
use App\Entity\Administration\Hopitaux;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

/**
 * Contrôleur API amélioré pour la gestion complète des services hospitaliers
 * 
 * Gère:
 * - CRUD des services
 * - Gestion des pôles d'activité
 * - Gestion des ressources (budget, personnel)
 * - Gestion opérationnelle (horaires, accréditation)
 * - Coordination et planification
 * - Rapports et statistiques
 * 
 * Endpoints:
 * - GET /api/services - Lister tous les services
 * - GET /api/services/{id} - Récupérer un service
 * - POST /api/services - Créer un service
 * - PUT /api/services/{id} - Modifier un service
 * - DELETE /api/services/{id} - Supprimer un service
 * - GET /api/services/hopital/{hopitalId} - Lister les services d'un hôpital
 * - GET /api/services/type/{typeService} - Lister les services par type
 * - GET /api/services/{id}/details - Détails complets d'un service
 * - GET /api/services/{id}/statistiques - Statistiques d'un service
 * - POST /api/services/{id}/assigner-pole - Assigner un service à un pôle
 * - GET /api/services/pole/{poleId} - Lister les services d'un pôle
 */
#[Route('/api/services', name: 'api_services_')]
class ServicesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Lister tous les services
     * GET /api/services
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);
            $hopitalId = $request->query->getInt('hopital_id', 0);
            $typeService = $request->query->get('type_service', null);
            $actif = $request->query->get('actif', null);
            $poleId = $request->query->getInt('pole_id', 0);

            $servicesRepo = $this->entityManager->getRepository(Services::class);
            $queryBuilder = $servicesRepo->createQueryBuilder('s');

            if ($hopitalId > 0) {
                $queryBuilder->where('s.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($typeService !== null) {
                $queryBuilder->andWhere('s.typeService = :typeService')
                    ->setParameter('typeService', $typeService);
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('s.actif = :actif')
                    ->setParameter('actif', $actif === 'true' || $actif === '1');
            }

            if ($poleId > 0) {
                $queryBuilder->andWhere('s.poleId = :poleId')
                    ->setParameter('poleId', $poleId);
            }

            $total = count($queryBuilder->getQuery()->getResult());

            $services = $queryBuilder
                ->orderBy('s.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($services as $service) {
                $data[] = $this->serializeService($service);
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
                'error' => 'Erreur lors de la récupération des services: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer un service par ID
     * GET /api/services/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($id);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializeService($service),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer les détails complets d'un service
     * GET /api/services/{id}/details
     */
    #[Route('/{id}/details', name: 'show_details', methods: ['GET'])]
    public function showDetails(int $id): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($id);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializeServiceDetailed($service),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer un nouveau service
     * POST /api/services
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des champs requis
            $requiredFields = ['code', 'nom', 'hopital_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '{$field}' est requis",
                    ], 400);
                }
            }

            // Vérifier que l'hôpital existe
            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find($data['hopital_id']);
            if (!$hopital) {
                return $this->json([
                    'success' => false,
                    'error' => 'Hôpital non trouvé',
                ], 404);
            }

            // Vérifier l'unicité du code
            $existingService = $this->entityManager->getRepository(Services::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingService) {
                return $this->json([
                    'success' => false,
                    'error' => 'Un service avec ce code existe déjà',
                ], 409);
            }

            $service = new Services();
            $service->setCode($data['code']);
            $service->setNom($data['nom']);
            $service->setHopitalId($hopital);
            $service->setDescription($data['description'] ?? null);
            $service->setTypeService($data['type_service'] ?? null);
            $service->setChefServiceId($data['chef_service_id'] ?? null);
            $service->setNombreLits($data['nombre_lits'] ?? null);
            $service->setLocalisation($data['localisation'] ?? null);
            $service->setTelephone($data['telephone'] ?? null);
            $service->setEmail($data['email'] ?? null);
            $service->setLogoService($data['logo_service'] ?? null);
            $service->setCouleurService($data['couleur_service'] ?? null);
            $service->setActif($data['actif'] ?? true);
            
            // Champs de gestion
            $service->setBudgetAnnuel($data['budget_annuel'] ?? null);
            $service->setNombrePersonnel($data['nombre_personnel'] ?? null);
            $service->setHorairesOuverture($data['horaires_ouverture'] ?? null);
            $service->setNiveauAccreditation($data['niveau_accreditation'] ?? null);
            
            // Assigner à un pôle si fourni
            if (isset($data['pole_id']) && $data['pole_id']) {
                $pole = $this->entityManager->getRepository(PolesActivite::class)->find($data['pole_id']);
                if ($pole) {
                    $service->setPoleId($pole);
                }
            }

            $this->entityManager->persist($service);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Service créé avec succès',
                'data' => $this->serializeService($service),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifier un service
     * PUT /api/services/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($id);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['code'])) {
                $service->setCode($data['code']);
            }
            if (isset($data['nom'])) {
                $service->setNom($data['nom']);
            }
            if (isset($data['description'])) {
                $service->setDescription($data['description']);
            }
            if (isset($data['type_service'])) {
                $service->setTypeService($data['type_service']);
            }
            if (isset($data['chef_service_id'])) {
                $service->setChefServiceId($data['chef_service_id']);
            }
            if (isset($data['nombre_lits'])) {
                $service->setNombreLits($data['nombre_lits']);
            }
            if (isset($data['localisation'])) {
                $service->setLocalisation($data['localisation']);
            }
            if (isset($data['telephone'])) {
                $service->setTelephone($data['telephone']);
            }
            if (isset($data['email'])) {
                $service->setEmail($data['email']);
            }
            if (isset($data['logo_service'])) {
                $service->setLogoService($data['logo_service']);
            }
            if (isset($data['couleur_service'])) {
                $service->setCouleurService($data['couleur_service']);
            }
            if (isset($data['actif'])) {
                $service->setActif($data['actif']);
            }
            
            // Champs de gestion
            if (isset($data['budget_annuel'])) {
                $service->setBudgetAnnuel($data['budget_annuel']);
            }
            if (isset($data['nombre_personnel'])) {
                $service->setNombrePersonnel($data['nombre_personnel']);
            }
            if (isset($data['horaires_ouverture'])) {
                $service->setHorairesOuverture($data['horaires_ouverture']);
            }
            if (isset($data['niveau_accreditation'])) {
                $service->setNiveauAccreditation($data['niveau_accreditation']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Service modifié avec succès',
                'data' => $this->serializeService($service),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un service
     * DELETE /api/services/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($id);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            $this->entityManager->remove($service);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Service supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les services d'un hôpital
     * GET /api/services/hopital/{hopitalId}
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

            $servicesRepo = $this->entityManager->getRepository(Services::class);
            $queryBuilder = $servicesRepo->createQueryBuilder('s')
                ->where('s.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId);

            $total = count($queryBuilder->getQuery()->getResult());

            $services = $queryBuilder
                ->orderBy('s.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($services as $service) {
                $data[] = $this->serializeService($service);
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
     * Lister les services par type
     * GET /api/services/type/{typeService}
     */
    #[Route('/type/{typeService}', name: 'by_type', methods: ['GET'])]
    public function byType(string $typeService, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $servicesRepo = $this->entityManager->getRepository(Services::class);
            $queryBuilder = $servicesRepo->createQueryBuilder('s')
                ->where('s.typeService = :typeService')
                ->setParameter('typeService', $typeService);

            $total = count($queryBuilder->getQuery()->getResult());

            $services = $queryBuilder
                ->orderBy('s.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($services as $service) {
                $data[] = $this->serializeService($service);
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
     * Lister les services d'un pôle
     * GET /api/services/pole/{poleId}
     */
    #[Route('/pole/{poleId}', name: 'by_pole', methods: ['GET'])]
    public function byPole(int $poleId, Request $request): JsonResponse
    {
        try {
            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($poleId);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $servicesRepo = $this->entityManager->getRepository(Services::class);
            $queryBuilder = $servicesRepo->createQueryBuilder('s')
                ->where('s.poleId = :poleId')
                ->setParameter('poleId', $poleId);

            $total = count($queryBuilder->getQuery()->getResult());

            $services = $queryBuilder
                ->orderBy('s.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($services as $service) {
                $data[] = $this->serializeService($service);
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
     * Assigner un service à un pôle
     * POST /api/services/{id}/assigner-pole
     */
    #[Route('/{id}/assigner-pole', name: 'assign_pole', methods: ['POST'])]
    public function assignPole(int $id, Request $request): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)->find($id);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['pole_id'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "pole_id" est requis',
                ], 400);
            }

            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($data['pole_id']);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            $service->setPoleId($pole);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Service assigné au pôle avec succès',
                'data' => $this->serializeService($service),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sérialiser un service pour la réponse JSON
     */
    private function serializeService(Services $service): array
    {
        try {
            $poleId = null;
            $pole = $service->getPoleId();
            if ($pole) {
                $poleId = $pole->getId();
            }
        } catch (Exception $e) {
            $poleId = null;
        }

        return [
            'id' => $service->getId(),
            'code' => $service->getCode(),
            'nom' => $service->getNom(),
            'description' => $service->getDescription(),
            'type_service' => $service->getTypeService(),
            'chef_service_id' => $service->getChefServiceId(),
            'nombre_lits' => $service->getNombreLits(),
            'localisation' => $service->getLocalisation(),
            'telephone' => $service->getTelephone(),
            'email' => $service->getEmail(),
            'logo_service' => $service->getLogoService(),
            'couleur_service' => $service->getCouleurService(),
            'actif' => $service->getActif(),
            'date_creation' => $service->getDateCreation()?->format('Y-m-d H:i:s'),
            'hopital_id' => $service->getHopitalId()->getId(),
            'pole_id' => $poleId,
        ];
    }

    /**
     * Sérialiser un service avec tous les détails
     */
    private function serializeServiceDetailed(Services $service): array
    {
        $baseData = $this->serializeService($service);
        
        try {
            $poleNom = null;
            $poleCode = null;
            $pole = $service->getPoleId();
            if ($pole) {
                $poleNom = $pole->getNom();
                $poleCode = $pole->getCode();
            }
        } catch (Exception $e) {
            $poleNom = null;
            $poleCode = null;
        }

        return array_merge($baseData, [
            'budget_annuel' => $service->getBudgetAnnuel(),
            'nombre_personnel' => $service->getNombrePersonnel(),
            'horaires_ouverture' => $service->getHorairesOuverture(),
            'niveau_accreditation' => $service->getNiveauAccreditation(),
            'pole_nom' => $poleNom,
            'pole_code' => $poleCode,
        ]);
    }
}
