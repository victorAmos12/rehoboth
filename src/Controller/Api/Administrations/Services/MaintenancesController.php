<?php

namespace App\Controller\Api\Administrations\Services;

use App\Entity\Administration\InterventionsMaintenance;
use App\Entity\Administration\Equipements;
use App\Entity\Administration\Hopitaux;
use App\Entity\Administration\Devises;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

/**
 * Contrôleur API pour la gestion des interventions de maintenance
 * 
 * Endpoints:
 * - GET /api/maintenances - Lister toutes les interventions
 * - GET /api/maintenances/{id} - Récupérer une intervention
 * - POST /api/maintenances - Créer une intervention
 * - PUT /api/maintenances/{id} - Modifier une intervention
 * - DELETE /api/maintenances/{id} - Supprimer une intervention
 * - GET /api/maintenances/equipement/{equipementId} - Lister les interventions d'un équipement
 * - GET /api/maintenances/hopital/{hopitalId} - Lister les interventions d'un hôpital
 * - GET /api/maintenances/technicien/{technicienId} - Lister les interventions d'un technicien
 * - GET /api/maintenances/statut/{statut} - Lister les interventions par statut
 * - GET /api/maintenances/type/{typeIntervention} - Lister les interventions par type
 */
#[Route('/api/maintenances', name: 'api_maintenances_')]
class MaintenancesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Lister toutes les interventions de maintenance
     * GET /api/maintenances
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);
            $equipementId = $request->query->getInt('equipement_id', 0);
            $hopitalId = $request->query->getInt('hopital_id', 0);
            $technicienId = $request->query->getInt('technicien_id', 0);
            $statut = $request->query->get('statut', null);
            $typeIntervention = $request->query->get('type_intervention', null);

            $maintenancesRepo = $this->entityManager->getRepository(InterventionsMaintenance::class);
            $queryBuilder = $maintenancesRepo->createQueryBuilder('m');

            if ($equipementId > 0) {
                $queryBuilder->where('m.equipementId = :equipementId')
                    ->setParameter('equipementId', $equipementId);
            }

            if ($hopitalId > 0) {
                $queryBuilder->andWhere('m.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($technicienId > 0) {
                $queryBuilder->andWhere('m.technicienId = :technicienId')
                    ->setParameter('technicienId', $technicienId);
            }

            if ($statut !== null) {
                $queryBuilder->andWhere('m.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            if ($typeIntervention !== null) {
                $queryBuilder->andWhere('m.typeIntervention = :typeIntervention')
                    ->setParameter('typeIntervention', $typeIntervention);
            }

            $total = count($queryBuilder->getQuery()->getResult());

            $maintenances = $queryBuilder
                ->orderBy('m.dateIntervention', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($maintenances as $maintenance) {
                $data[] = $this->serializeMaintenance($maintenance);
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
                'error' => 'Erreur lors de la récupération des interventions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer une intervention par ID
     * GET /api/maintenances/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $maintenance = $this->entityManager->getRepository(InterventionsMaintenance::class)->find($id);

            if (!$maintenance) {
                return $this->json([
                    'success' => false,
                    'error' => 'Intervention non trouvée',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializeMaintenance($maintenance),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer une nouvelle intervention de maintenance
     * POST /api/maintenances
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des champs requis
            $requiredFields = ['numero_intervention', 'date_intervention', 'equipement_id', 'hopital_id', 'technicien_id', 'devise_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '{$field}' est requis",
                    ], 400);
                }
            }

            // Vérifier que l'équipement existe
            $equipement = $this->entityManager->getRepository(Equipements::class)->find($data['equipement_id']);
            if (!$equipement) {
                return $this->json([
                    'success' => false,
                    'error' => 'Équipement non trouvé',
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

            // Vérifier que le technicien existe
            $technicien = $this->entityManager->getRepository(Utilisateurs::class)->find($data['technicien_id']);
            if (!$technicien) {
                return $this->json([
                    'success' => false,
                    'error' => 'Technicien non trouvé',
                ], 404);
            }

            // Vérifier que la devise existe
            $devise = $this->entityManager->getRepository(Devises::class)->find($data['devise_id']);
            if (!$devise) {
                return $this->json([
                    'success' => false,
                    'error' => 'Devise non trouvée',
                ], 404);
            }

            $maintenance = new InterventionsMaintenance();
            $maintenance->setNumeroIntervention($data['numero_intervention']);
            $maintenance->setDateIntervention(new \DateTime($data['date_intervention']));
            $maintenance->setEquipementId($equipement);
            $maintenance->setHopitalId($hopital);
            $maintenance->setTechnicienId($technicien);
            $maintenance->setDeviseId($devise);
            $maintenance->setTypeIntervention($data['type_intervention'] ?? null);
            $maintenance->setDescriptionIntervention($data['description_intervention'] ?? null);
            $maintenance->setPiecesRemplacees($data['pieces_remplacees'] ?? null);
            $maintenance->setDureeIntervention($data['duree_intervention'] ?? null);
            $maintenance->setCoutIntervention($data['cout_intervention'] ?? null);
            $maintenance->setStatut($data['statut'] ?? 'planifiée');

            $this->entityManager->persist($maintenance);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Intervention créée avec succès',
                'data' => $this->serializeMaintenance($maintenance),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifier une intervention de maintenance
     * PUT /api/maintenances/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $maintenance = $this->entityManager->getRepository(InterventionsMaintenance::class)->find($id);

            if (!$maintenance) {
                return $this->json([
                    'success' => false,
                    'error' => 'Intervention non trouvée',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['numero_intervention'])) {
                $maintenance->setNumeroIntervention($data['numero_intervention']);
            }
            if (isset($data['date_intervention'])) {
                $maintenance->setDateIntervention(new \DateTime($data['date_intervention']));
            }
            if (isset($data['type_intervention'])) {
                $maintenance->setTypeIntervention($data['type_intervention']);
            }
            if (isset($data['description_intervention'])) {
                $maintenance->setDescriptionIntervention($data['description_intervention']);
            }
            if (isset($data['pieces_remplacees'])) {
                $maintenance->setPiecesRemplacees($data['pieces_remplacees']);
            }
            if (isset($data['duree_intervention'])) {
                $maintenance->setDureeIntervention($data['duree_intervention']);
            }
            if (isset($data['cout_intervention'])) {
                $maintenance->setCoutIntervention($data['cout_intervention']);
            }
            if (isset($data['statut'])) {
                $maintenance->setStatut($data['statut']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Intervention modifiée avec succès',
                'data' => $this->serializeMaintenance($maintenance),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer une intervention de maintenance
     * DELETE /api/maintenances/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $maintenance = $this->entityManager->getRepository(InterventionsMaintenance::class)->find($id);

            if (!$maintenance) {
                return $this->json([
                    'success' => false,
                    'error' => 'Intervention non trouvée',
                ], 404);
            }

            $this->entityManager->remove($maintenance);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Intervention supprimée avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les interventions d'un équipement
     * GET /api/maintenances/equipement/{equipementId}
     */
    #[Route('/equipement/{equipementId}', name: 'by_equipement', methods: ['GET'])]
    public function byEquipement(int $equipementId, Request $request): JsonResponse
    {
        try {
            $equipement = $this->entityManager->getRepository(Equipements::class)->find($equipementId);

            if (!$equipement) {
                return $this->json([
                    'success' => false,
                    'error' => 'Équipement non trouvé',
                ], 404);
            }

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $maintenancesRepo = $this->entityManager->getRepository(InterventionsMaintenance::class);
            $queryBuilder = $maintenancesRepo->createQueryBuilder('m')
                ->where('m.equipementId = :equipementId')
                ->setParameter('equipementId', $equipementId);

            $total = count($queryBuilder->getQuery()->getResult());

            $maintenances = $queryBuilder
                ->orderBy('m.dateIntervention', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($maintenances as $maintenance) {
                $data[] = $this->serializeMaintenance($maintenance);
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
     * Lister les interventions d'un hôpital
     * GET /api/maintenances/hopital/{hopitalId}
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

            $maintenancesRepo = $this->entityManager->getRepository(InterventionsMaintenance::class);
            $queryBuilder = $maintenancesRepo->createQueryBuilder('m')
                ->where('m.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId);

            $total = count($queryBuilder->getQuery()->getResult());

            $maintenances = $queryBuilder
                ->orderBy('m.dateIntervention', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($maintenances as $maintenance) {
                $data[] = $this->serializeMaintenance($maintenance);
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
     * Lister les interventions d'un technicien
     * GET /api/maintenances/technicien/{technicienId}
     */
    #[Route('/technicien/{technicienId}', name: 'by_technicien', methods: ['GET'])]
    public function byTechnicien(int $technicienId, Request $request): JsonResponse
    {
        try {
            $technicien = $this->entityManager->getRepository(Utilisateurs::class)->find($technicienId);

            if (!$technicien) {
                return $this->json([
                    'success' => false,
                    'error' => 'Technicien non trouvé',
                ], 404);
            }

            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $maintenancesRepo = $this->entityManager->getRepository(InterventionsMaintenance::class);
            $queryBuilder = $maintenancesRepo->createQueryBuilder('m')
                ->where('m.technicienId = :technicienId')
                ->setParameter('technicienId', $technicienId);

            $total = count($queryBuilder->getQuery()->getResult());

            $maintenances = $queryBuilder
                ->orderBy('m.dateIntervention', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($maintenances as $maintenance) {
                $data[] = $this->serializeMaintenance($maintenance);
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
     * Lister les interventions par statut
     * GET /api/maintenances/statut/{statut}
     */
    #[Route('/statut/{statut}', name: 'by_statut', methods: ['GET'])]
    public function byStatut(string $statut, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $maintenancesRepo = $this->entityManager->getRepository(InterventionsMaintenance::class);
            $queryBuilder = $maintenancesRepo->createQueryBuilder('m')
                ->where('m.statut = :statut')
                ->setParameter('statut', $statut);

            $total = count($queryBuilder->getQuery()->getResult());

            $maintenances = $queryBuilder
                ->orderBy('m.dateIntervention', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($maintenances as $maintenance) {
                $data[] = $this->serializeMaintenance($maintenance);
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
     * Lister les interventions par type
     * GET /api/maintenances/type/{typeIntervention}
     */
    #[Route('/type/{typeIntervention}', name: 'by_type', methods: ['GET'])]
    public function byType(string $typeIntervention, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $maintenancesRepo = $this->entityManager->getRepository(InterventionsMaintenance::class);
            $queryBuilder = $maintenancesRepo->createQueryBuilder('m')
                ->where('m.typeIntervention = :typeIntervention')
                ->setParameter('typeIntervention', $typeIntervention);

            $total = count($queryBuilder->getQuery()->getResult());

            $maintenances = $queryBuilder
                ->orderBy('m.dateIntervention', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($maintenances as $maintenance) {
                $data[] = $this->serializeMaintenance($maintenance);
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
     * Sérialiser une intervention de maintenance pour la réponse JSON
     */
    private function serializeMaintenance(InterventionsMaintenance $maintenance): array
    {
        return [
            'id' => $maintenance->getId(),
            'numero_intervention' => $maintenance->getNumeroIntervention(),
            'date_intervention' => $maintenance->getDateIntervention()->format('Y-m-d'),
            'type_intervention' => $maintenance->getTypeIntervention(),
            'description_intervention' => $maintenance->getDescriptionIntervention(),
            'pieces_remplacees' => $maintenance->getPiecesRemplacees(),
            'duree_intervention' => $maintenance->getDureeIntervention(),
            'cout_intervention' => $maintenance->getCoutIntervention(),
            'statut' => $maintenance->getStatut(),
            'date_creation' => $maintenance->getDateCreation()?->format('Y-m-d H:i:s'),
            'equipement_id' => $maintenance->getEquipementId()->getId(),
            'hopital_id' => $maintenance->getHopitalId()->getId(),
            'technicien_id' => $maintenance->getTechnicienId()->getId(),
            'devise_id' => $maintenance->getDeviseId()->getId(),
        ];
    }
}
