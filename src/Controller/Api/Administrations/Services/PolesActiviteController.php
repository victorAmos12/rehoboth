<?php

namespace App\Controller\Api\Administrations\Services;

use App\Entity\Administration\PolesActivite;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

/**
 * Contrôleur API pour la gestion des pôles d'activité hospitaliers
 * 
 * Un pôle regroupe plusieurs services par type de pathologies ou fonctions
 * Exemple: Pôle Cardio (Cardiologie + Chirurgie Cardiaque)
 * 
 * Endpoints:
 * - GET /api/poles - Lister tous les pôles
 * - GET /api/poles/{id} - Récupérer un pôle
 * - POST /api/poles - Créer un pôle
 * - PUT /api/poles/{id} - Modifier un pôle
 * - DELETE /api/poles/{id} - Supprimer un pôle
 * - GET /api/poles/hopital/{hopitalId} - Lister les pôles d'un hôpital
 * - POST /api/poles/{id}/assigner-responsable - Assigner un responsable
 * - GET /api/poles/{id}/services - Lister les services d'un pôle
 * - GET /api/poles/{id}/statistiques - Statistiques d'un pôle
 */
#[Route('/api/poles', name: 'api_poles_')]
class PolesActiviteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Lister tous les pôles
     * GET /api/poles
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);
            $hopitalId = $request->query->getInt('hopital_id', 0);
            $actif = $request->query->get('actif', null);

            $polesRepo = $this->entityManager->getRepository(PolesActivite::class);
            $queryBuilder = $polesRepo->createQueryBuilder('p');

            if ($hopitalId > 0) {
                $queryBuilder->where('p.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('p.actif = :actif')
                    ->setParameter('actif', $actif === 'true' || $actif === '1');
            }

            $total = count($queryBuilder->getQuery()->getResult());

            $poles = $queryBuilder
                ->orderBy('p.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($poles as $pole) {
                $data[] = $this->serializePole($pole);
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
                'error' => 'Erreur lors de la récupération des pôles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer un pôle par ID
     * GET /api/poles/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($id);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializePoleDetailed($pole),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer un nouveau pôle
     * POST /api/poles
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
            $existingPole = $this->entityManager->getRepository(PolesActivite::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingPole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Un pôle avec ce code existe déjà',
                ], 409);
            }

            $pole = new PolesActivite();
            $pole->setCode($data['code']);
            $pole->setNom($data['nom']);
            $pole->setHopitalId($hopital);
            $pole->setDescription($data['description'] ?? null);
            $pole->setTypePole($data['type_pole'] ?? null);
            $pole->setBudgetAnnuel($data['budget_annuel'] ?? null);
            $pole->setActif($data['actif'] ?? true);

            // Assigner un responsable si fourni
            if (isset($data['responsable_id']) && $data['responsable_id']) {
                $responsable = $this->entityManager->getRepository(Utilisateurs::class)->find($data['responsable_id']);
                if ($responsable) {
                    $pole->setResponsableId($responsable);
                }
            }

            $this->entityManager->persist($pole);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Pôle créé avec succès',
                'data' => $this->serializePole($pole),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifier un pôle
     * PUT /api/poles/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($id);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['code'])) {
                $pole->setCode($data['code']);
            }
            if (isset($data['nom'])) {
                $pole->setNom($data['nom']);
            }
            if (isset($data['description'])) {
                $pole->setDescription($data['description']);
            }
            if (isset($data['type_pole'])) {
                $pole->setTypePole($data['type_pole']);
            }
            if (isset($data['budget_annuel'])) {
                $pole->setBudgetAnnuel($data['budget_annuel']);
            }
            if (isset($data['actif'])) {
                $pole->setActif($data['actif']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Pôle modifié avec succès',
                'data' => $this->serializePole($pole),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un pôle
     * DELETE /api/poles/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($id);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            $this->entityManager->remove($pole);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Pôle supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les pôles d'un hôpital
     * GET /api/poles/hopital/{hopitalId}
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

            $polesRepo = $this->entityManager->getRepository(PolesActivite::class);
            $queryBuilder = $polesRepo->createQueryBuilder('p')
                ->where('p.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId);

            $total = count($queryBuilder->getQuery()->getResult());

            $poles = $queryBuilder
                ->orderBy('p.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($poles as $pole) {
                $data[] = $this->serializePole($pole);
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
     * Assigner un responsable à un pôle
     * POST /api/poles/{id}/assigner-responsable
     */
    #[Route('/{id}/assigner-responsable', name: 'assign_responsable', methods: ['POST'])]
    public function assignResponsable(int $id, Request $request): JsonResponse
    {
        try {
            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($id);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['responsable_id'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "responsable_id" est requis',
                ], 400);
            }

            $responsable = $this->entityManager->getRepository(Utilisateurs::class)->find($data['responsable_id']);

            if (!$responsable) {
                return $this->json([
                    'success' => false,
                    'error' => 'Responsable non trouvé',
                ], 404);
            }

            $pole->setResponsableId($responsable);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Responsable assigné avec succès',
                'data' => $this->serializePole($pole),
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
     * GET /api/poles/{id}/services
     */
    #[Route('/{id}/services', name: 'services', methods: ['GET'])]
    public function services(int $id, Request $request): JsonResponse
    {
        try {
            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($id);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $services = $pole->getServices();
            $total = count($services);

            $servicesArray = $services->slice(($page - 1) * $limit, $limit);

            $data = [];
            foreach ($servicesArray as $service) {
                $data[] = [
                    'id' => $service->getId(),
                    'code' => $service->getCode(),
                    'nom' => $service->getNom(),
                    'type_service' => $service->getTypeService(),
                    'nombre_lits' => $service->getNombreLits(),
                    'nombre_personnel' => $service->getNombrePersonnel(),
                    'budget_annuel' => $service->getBudgetAnnuel(),
                ];
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
     * Obtenir les statistiques d'un pôle
     * GET /api/poles/{id}/statistiques
     */
    #[Route('/{id}/statistiques', name: 'statistiques', methods: ['GET'])]
    public function statistiques(int $id): JsonResponse
    {
        try {
            $pole = $this->entityManager->getRepository(PolesActivite::class)->find($id);

            if (!$pole) {
                return $this->json([
                    'success' => false,
                    'error' => 'Pôle non trouvé',
                ], 404);
            }

            $services = $pole->getServices();
            
            $totalServices = count($services);
            $totalLits = 0;
            $totalPersonnel = 0;
            $totalBudget = 0;

            foreach ($services as $service) {
                $totalLits += $service->getNombreLits() ?? 0;
                $totalPersonnel += $service->getNombrePersonnel() ?? 0;
                $totalBudget += (float)($service->getBudgetAnnuel() ?? 0);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'pole_id' => $pole->getId(),
                    'pole_nom' => $pole->getNom(),
                    'total_services' => $totalServices,
                    'total_lits' => $totalLits,
                    'total_personnel' => $totalPersonnel,
                    'total_budget' => $totalBudget,
                    'budget_moyen_par_service' => $totalServices > 0 ? $totalBudget / $totalServices : 0,
                    'personnel_moyen_par_service' => $totalServices > 0 ? $totalPersonnel / $totalServices : 0,
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
     * Sérialiser un pôle pour la réponse JSON
     */
    private function serializePole(PolesActivite $pole): array
    {
        try {
            $responsableId = null;
            $responsable = $pole->getResponsableId();
            if ($responsable) {
                $responsableId = $responsable->getId();
            }
        } catch (Exception $e) {
            $responsableId = null;
        }

        return [
            'id' => $pole->getId(),
            'code' => $pole->getCode(),
            'nom' => $pole->getNom(),
            'description' => $pole->getDescription(),
            'type_pole' => $pole->getTypePole(),
            'budget_annuel' => $pole->getBudgetAnnuel(),
            'actif' => $pole->isActif(),
            'date_creation' => $pole->getDateCreation()?->format('Y-m-d H:i:s'),
            'hopital_id' => $pole->getHopitalId()->getId(),
            'responsable_id' => $responsableId,
            'nombre_services' => count($pole->getServices()),
        ];
    }

    /**
     * Sérialiser un pôle avec tous les détails
     */
    private function serializePoleDetailed(PolesActivite $pole): array
    {
        $baseData = $this->serializePole($pole);
        
        try {
            $responsableNom = null;
            $responsableEmail = null;
            $responsable = $pole->getResponsableId();
            if ($responsable) {
                $responsableNom = $responsable->getNom() . ' ' . $responsable->getPrenom();
                $responsableEmail = $responsable->getEmail();
            }
        } catch (Exception $e) {
            $responsableNom = null;
            $responsableEmail = null;
        }

        return array_merge($baseData, [
            'responsable_nom' => $responsableNom,
            'responsable_email' => $responsableEmail,
        ]);
    }
}
