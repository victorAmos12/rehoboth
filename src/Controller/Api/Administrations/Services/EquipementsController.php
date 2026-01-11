<?php

namespace App\Controller\Api\Administrations\Services;

use App\Entity\Administration\Equipements;
use App\Entity\Administration\Services;
use App\Entity\Administration\Hopitaux;
use App\Entity\Administration\Fournisseurs;
use App\Entity\Administration\Devises;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

/**
 * Contrôleur API pour la gestion des équipements hospitaliers
 * 
 * Endpoints:
 * - GET /api/equipements - Lister tous les équipements
 * - GET /api/equipements/{id} - Récupérer un équipement
 * - POST /api/equipements - Créer un équipement
 * - PUT /api/equipements/{id} - Modifier un équipement
 * - DELETE /api/equipements/{id} - Supprimer un équipement
 * - GET /api/equipements/service/{serviceId} - Lister les équipements d'un service
 * - GET /api/equipements/hopital/{hopitalId} - Lister les équipements d'un hôpital
 * - GET /api/equipements/type/{typeEquipement} - Lister les équipements par type
 * - GET /api/equipements/statut/{statut} - Lister les équipements par statut
 */
#[Route('/api/equipements', name: 'api_equipements_')]
class EquipementsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Lister tous les équipements
     * GET /api/equipements
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);
            $serviceId = $request->query->getInt('service_id', 0);
            $hopitalId = $request->query->getInt('hopital_id', 0);
            $typeEquipement = $request->query->get('type_equipement', null);
            $statut = $request->query->get('statut', null);

            $equipementsRepo = $this->entityManager->getRepository(Equipements::class);
            $queryBuilder = $equipementsRepo->createQueryBuilder('e');

            if ($serviceId > 0) {
                $queryBuilder->where('e.serviceId = :serviceId')
                    ->setParameter('serviceId', $serviceId);
            }

            if ($hopitalId > 0) {
                $queryBuilder->andWhere('e.hopitalId = :hopitalId')
                    ->setParameter('hopitalId', $hopitalId);
            }

            if ($typeEquipement !== null) {
                $queryBuilder->andWhere('e.typeEquipement = :typeEquipement')
                    ->setParameter('typeEquipement', $typeEquipement);
            }

            if ($statut !== null) {
                $queryBuilder->andWhere('e.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            $total = count($queryBuilder->getQuery()->getResult());

            $equipements = $queryBuilder
                ->orderBy('e.nomEquipement', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($equipements as $equipement) {
                $data[] = $this->serializeEquipement($equipement);
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
                'error' => 'Erreur lors de la récupération des équipements: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer un équipement par ID
     * GET /api/equipements/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $equipement = $this->entityManager->getRepository(Equipements::class)->find($id);

            if (!$equipement) {
                return $this->json([
                    'success' => false,
                    'error' => 'Équipement non trouvé',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->serializeEquipement($equipement),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer un nouvel équipement
     * POST /api/equipements
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des champs requis
            $requiredFields = ['code_equipement', 'nom_equipement', 'service_id', 'hopital_id', 'fournisseur_id', 'devise_id'];
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

            // Vérifier que le fournisseur existe
            $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->find($data['fournisseur_id']);
            if (!$fournisseur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Fournisseur non trouvé',
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

            $equipement = new Equipements();
            $equipement->setCodeEquipement($data['code_equipement']);
            $equipement->setNomEquipement($data['nom_equipement']);
            $equipement->setServiceId($service);
            $equipement->setHopitalId($hopital);
            $equipement->setFournisseurId($fournisseur);
            $equipement->setDeviseId($devise);
            $equipement->setTypeEquipement($data['type_equipement'] ?? null);
            $equipement->setMarque($data['marque'] ?? null);
            $equipement->setModele($data['modele'] ?? null);
            $equipement->setNumeroSerie($data['numero_serie'] ?? null);
            $equipement->setDateAcquisition($data['date_acquisition'] ?? null);
            $equipement->setDateMiseEnService($data['date_mise_en_service'] ?? null);
            $equipement->setPrixAcquisition($data['prix_acquisition'] ?? null);
            $equipement->setDureeVieUtile($data['duree_vie_utile'] ?? null);
            $equipement->setStatut($data['statut'] ?? 'actif');
            $equipement->setLocalisation($data['localisation'] ?? null);

            $this->entityManager->persist($equipement);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Équipement créé avec succès',
                'data' => $this->serializeEquipement($equipement),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Modifier un équipement
     * PUT /api/equipements/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $equipement = $this->entityManager->getRepository(Equipements::class)->find($id);

            if (!$equipement) {
                return $this->json([
                    'success' => false,
                    'error' => 'Équipement non trouvé',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['code_equipement'])) {
                $equipement->setCodeEquipement($data['code_equipement']);
            }
            if (isset($data['nom_equipement'])) {
                $equipement->setNomEquipement($data['nom_equipement']);
            }
            if (isset($data['type_equipement'])) {
                $equipement->setTypeEquipement($data['type_equipement']);
            }
            if (isset($data['marque'])) {
                $equipement->setMarque($data['marque']);
            }
            if (isset($data['modele'])) {
                $equipement->setModele($data['modele']);
            }
            if (isset($data['numero_serie'])) {
                $equipement->setNumeroSerie($data['numero_serie']);
            }
            if (isset($data['date_acquisition'])) {
                $equipement->setDateAcquisition($data['date_acquisition']);
            }
            if (isset($data['date_mise_en_service'])) {
                $equipement->setDateMiseEnService($data['date_mise_en_service']);
            }
            if (isset($data['prix_acquisition'])) {
                $equipement->setPrixAcquisition($data['prix_acquisition']);
            }
            if (isset($data['duree_vie_utile'])) {
                $equipement->setDureeVieUtile($data['duree_vie_utile']);
            }
            if (isset($data['statut'])) {
                $equipement->setStatut($data['statut']);
            }
            if (isset($data['localisation'])) {
                $equipement->setLocalisation($data['localisation']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Équipement modifié avec succès',
                'data' => $this->serializeEquipement($equipement),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la modification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un équipement
     * DELETE /api/equipements/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $equipement = $this->entityManager->getRepository(Equipements::class)->find($id);

            if (!$equipement) {
                return $this->json([
                    'success' => false,
                    'error' => 'Équipement non trouvé',
                ], 404);
            }

            $this->entityManager->remove($equipement);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Équipement supprimé avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lister les équipements d'un service
     * GET /api/equipements/service/{serviceId}
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

            $equipementsRepo = $this->entityManager->getRepository(Equipements::class);
            $queryBuilder = $equipementsRepo->createQueryBuilder('e')
                ->where('e.serviceId = :serviceId')
                ->setParameter('serviceId', $serviceId);

            $total = count($queryBuilder->getQuery()->getResult());

            $equipements = $queryBuilder
                ->orderBy('e.nomEquipement', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($equipements as $equipement) {
                $data[] = $this->serializeEquipement($equipement);
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
     * Lister les équipements d'un hôpital
     * GET /api/equipements/hopital/{hopitalId}
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

            $equipementsRepo = $this->entityManager->getRepository(Equipements::class);
            $queryBuilder = $equipementsRepo->createQueryBuilder('e')
                ->where('e.hopitalId = :hopitalId')
                ->setParameter('hopitalId', $hopitalId);

            $total = count($queryBuilder->getQuery()->getResult());

            $equipements = $queryBuilder
                ->orderBy('e.nomEquipement', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($equipements as $equipement) {
                $data[] = $this->serializeEquipement($equipement);
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
     * Lister les équipements par type
     * GET /api/equipements/type/{typeEquipement}
     */
    #[Route('/type/{typeEquipement}', name: 'by_type', methods: ['GET'])]
    public function byType(string $typeEquipement, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $equipementsRepo = $this->entityManager->getRepository(Equipements::class);
            $queryBuilder = $equipementsRepo->createQueryBuilder('e')
                ->where('e.typeEquipement = :typeEquipement')
                ->setParameter('typeEquipement', $typeEquipement);

            $total = count($queryBuilder->getQuery()->getResult());

            $equipements = $queryBuilder
                ->orderBy('e.nomEquipement', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($equipements as $equipement) {
                $data[] = $this->serializeEquipement($equipement);
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
     * Lister les équipements par statut
     * GET /api/equipements/statut/{statut}
     */
    #[Route('/statut/{statut}', name: 'by_statut', methods: ['GET'])]
    public function byStatut(string $statut, Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 20);

            $equipementsRepo = $this->entityManager->getRepository(Equipements::class);
            $queryBuilder = $equipementsRepo->createQueryBuilder('e')
                ->where('e.statut = :statut')
                ->setParameter('statut', $statut);

            $total = count($queryBuilder->getQuery()->getResult());

            $equipements = $queryBuilder
                ->orderBy('e.nomEquipement', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($equipements as $equipement) {
                $data[] = $this->serializeEquipement($equipement);
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
     * Sérialiser un équipement pour la réponse JSON
     */
    private function serializeEquipement(Equipements $equipement): array
    {
        return [
            'id' => $equipement->getId(),
            'code_equipement' => $equipement->getCodeEquipement(),
            'nom_equipement' => $equipement->getNomEquipement(),
            'type_equipement' => $equipement->getTypeEquipement(),
            'marque' => $equipement->getMarque(),
            'modele' => $equipement->getModele(),
            'numero_serie' => $equipement->getNumeroSerie(),
            'date_acquisition' => $equipement->getDateAcquisition()?->format('Y-m-d'),
            'date_mise_en_service' => $equipement->getDateMiseEnService()?->format('Y-m-d'),
            'prix_acquisition' => $equipement->getPrixAcquisition(),
            'duree_vie_utile' => $equipement->getDureeVieUtile(),
            'statut' => $equipement->getStatut(),
            'localisation' => $equipement->getLocalisation(),
            'date_creation' => $equipement->getDateCreation()?->format('Y-m-d H:i:s'),
            'service_id' => $equipement->getServiceId()->getId(),
            'hopital_id' => $equipement->getHopitalId()->getId(),
            'fournisseur_id' => $equipement->getFournisseurId()->getId(),
            'devise_id' => $equipement->getDeviseId()->getId(),
        ];
    }
}
