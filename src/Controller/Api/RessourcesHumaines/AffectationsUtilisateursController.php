<?php

namespace App\Controller\Api\RessourcesHumaines;

use App\Entity\Personnel\AffectationsUtilisateurs;
use App\Entity\Personnel\Utilisateurs;
use App\Entity\Administration\Services;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTimeImmutable;
use Exception;

/**
 * Contrôleur API pour la gestion des affectations des utilisateurs aux services
 * 
 * Gère:
 * - CRUD complet des affectations (Create, Read, Update, Delete)
 * - Affectations multiples par utilisateur
 * - Gestion des pourcentages de temps
 * - Historique des affectations
 * - Recherche et filtrage
 * - Statistiques d'affectation
 */
#[Route('/api/affectations', name: 'api_affectations_')]
class AffectationsUtilisateursController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Récupère la liste de toutes les affectations avec pagination et filtrage
     * GET /api/affectations
     * 
     * Paramètres de requête:
     * - page: numéro de page (défaut: 1)
     * - limit: nombre d'éléments par page (défaut: 20, max: 100)
     * - utilisateur_id: filtrer par utilisateur
     * - service_id: filtrer par service
     * - actif: filtrer par statut actif/inactif
     * - sort: champ de tri (défaut: dateDebut)
     * - order: ordre de tri ASC/DESC (défaut: DESC)
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $utilisateurId = $request->query->get('utilisateur_id');
            $serviceId = $request->query->get('service_id');
            $actif = $request->query->get('actif');
            $sort = $request->query->get('sort', 'dateDebut');
            $order = strtoupper($request->query->get('order', 'DESC'));

            if (!in_array($order, ['ASC', 'DESC'])) {
                $order = 'DESC';
            }

            $queryBuilder = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->leftJoin('a.serviceId', 's')
                ->addSelect('u', 's');

            // Appliquer les filtres
            if ($utilisateurId) {
                $queryBuilder->andWhere('a.utilisateurId = :utilisateurId')
                    ->setParameter('utilisateurId', $utilisateurId);
            }

            if ($serviceId) {
                $queryBuilder->andWhere('a.serviceId = :serviceId')
                    ->setParameter('serviceId', $serviceId);
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('a.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer le tri et la pagination
            $queryBuilder->orderBy('a.' . $sort, $order)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $affectations = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (AffectationsUtilisateurs $affectation) {
                return $this->formatAffectationData($affectation);
            }, $affectations);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des affectations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails d'une affectation
     * GET /api/affectations/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $affectation = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->leftJoin('a.serviceId', 's')
                ->addSelect('u', 's')
                ->where('a.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$affectation) {
                return $this->json([
                    'success' => false,
                    'error' => 'Affectation non trouvée',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatAffectationData($affectation, true),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de l\'affectation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée une nouvelle affectation
     * POST /api/affectations
     * 
     * Champs requis:
     * - utilisateur_id: ID de l'utilisateur
     * - service_id: ID du service
     * - date_debut: date de début (format: YYYY-MM-DD)
     * 
     * Champs optionnels:
     * - date_fin: date de fin (format: YYYY-MM-DD)
     * - pourcentage_temps: pourcentage de temps (0-100)
     * - actif: statut actif/inactif (défaut: true)
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Valider les champs requis
            $requiredFields = ['utilisateur_id', 'service_id', 'date_debut'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'error' => "Le champ '$field' est requis",
                    ], 400);
                }
            }

            // Récupérer l'utilisateur
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)
                ->find($data['utilisateur_id']);
            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            // Récupérer le service
            $service = $this->entityManager->getRepository(Services::class)
                ->find($data['service_id']);
            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            // Vérifier les chevauchements d'affectations
            $existingAffectation = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->where('a.utilisateurId = :utilisateur')
                ->andWhere('a.serviceId = :service')
                ->andWhere('a.actif = true')
                ->andWhere('a.dateFin IS NULL OR a.dateFin > :dateDebut')
                ->setParameter('utilisateur', $utilisateur)
                ->setParameter('service', $service)
                ->setParameter('dateDebut', new \DateTime($data['date_debut']))
                ->getQuery()
                ->getOneOrNullResult();

            if ($existingAffectation) {
                return $this->json([
                    'success' => false,
                    'error' => 'Une affectation active existe déjà pour cet utilisateur dans ce service',
                ], 409);
            }

            // Créer l'affectation
            $affectation = new AffectationsUtilisateurs();
            $affectation->setUtilisateurId($utilisateur);
            $affectation->setServiceId($service);
            $affectation->setDateDebut(new \DateTime($data['date_debut']));

            if (isset($data['date_fin'])) {
                $affectation->setDateFin(new \DateTime($data['date_fin']));
            }

            if (isset($data['pourcentage_temps'])) {
                $pourcentage = (float)$data['pourcentage_temps'];
                if ($pourcentage < 0 || $pourcentage > 100) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Le pourcentage de temps doit être entre 0 et 100',
                    ], 400);
                }
                $affectation->setPourcentageTemps((string)$pourcentage);
            }

            if (isset($data['actif'])) {
                $affectation->setActif($data['actif']);
            }

            // Valider l'entité
            $errors = $this->validator->validate($affectation);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'details' => $errorMessages,
                ], 400);
            }

            $this->entityManager->persist($affectation);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Affectation créée avec succès',
                'data' => $this->formatAffectationData($affectation),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création de l\'affectation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour une affectation existante
     * PUT /api/affectations/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $affectation = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->find($id);

            if (!$affectation) {
                return $this->json([
                    'success' => false,
                    'error' => 'Affectation non trouvée',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs
            if (isset($data['date_debut'])) {
                $affectation->setDateDebut(new \DateTime($data['date_debut']));
            }

            if (isset($data['date_fin'])) {
                if ($data['date_fin'] === null) {
                    $affectation->setDateFin(null);
                } else {
                    $affectation->setDateFin(new \DateTime($data['date_fin']));
                }
            }

            if (isset($data['pourcentage_temps'])) {
                if ($data['pourcentage_temps'] === null) {
                    $affectation->setPourcentageTemps(null);
                } else {
                    $pourcentage = (float)$data['pourcentage_temps'];
                    if ($pourcentage < 0 || $pourcentage > 100) {
                        return $this->json([
                            'success' => false,
                            'error' => 'Le pourcentage de temps doit être entre 0 et 100',
                        ], 400);
                    }
                    $affectation->setPourcentageTemps((string)$pourcentage);
                }
            }

            if (isset($data['actif'])) {
                $affectation->setActif($data['actif']);
            }

            if (isset($data['service_id'])) {
                $service = $this->entityManager->getRepository(Services::class)
                    ->find($data['service_id']);
                if ($service) {
                    $affectation->setServiceId($service);
                }
            }

            // Valider l'entité
            $errors = $this->validator->validate($affectation);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'error' => 'Erreur de validation',
                    'details' => $errorMessages,
                ], 400);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Affectation mise à jour avec succès',
                'data' => $this->formatAffectationData($affectation),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour de l\'affectation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime une affectation
     * DELETE /api/affectations/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $affectation = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->find($id);

            if (!$affectation) {
                return $this->json([
                    'success' => false,
                    'error' => 'Affectation non trouvée',
                ], 404);
            }

            $this->entityManager->remove($affectation);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Affectation supprimée avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de l\'affectation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les affectations d'un utilisateur
     * GET /api/affectations/utilisateur/{utilisateurId}
     */
    #[Route('/utilisateur/{utilisateurId}', name: 'by_utilisateur', methods: ['GET'])]
    public function byUtilisateur(int $utilisateurId, Request $request): JsonResponse
    {
        try {
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)
                ->find($utilisateurId);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $actif = $request->query->get('actif');

            $queryBuilder = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.serviceId', 's')
                ->addSelect('s')
                ->where('a.utilisateurId = :utilisateur')
                ->setParameter('utilisateur', $utilisateur)
                ->orderBy('a.dateDebut', 'DESC');

            if ($actif !== null) {
                $queryBuilder->andWhere('a.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $affectations = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (AffectationsUtilisateurs $affectation) {
                return $this->formatAffectationData($affectation);
            }, $affectations);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'utilisateur' => $utilisateur->getNom() . ' ' . $utilisateur->getPrenom(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des affectations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les affectations d'un service
     * GET /api/affectations/service/{serviceId}
     */
    #[Route('/service/{serviceId}', name: 'by_service', methods: ['GET'])]
    public function byService(int $serviceId, Request $request): JsonResponse
    {
        try {
            $service = $this->entityManager->getRepository(Services::class)
                ->find($serviceId);

            if (!$service) {
                return $this->json([
                    'success' => false,
                    'error' => 'Service non trouvé',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $actif = $request->query->get('actif');

            $queryBuilder = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->addSelect('u')
                ->where('a.serviceId = :service')
                ->setParameter('service', $service)
                ->orderBy('a.dateDebut', 'DESC');

            if ($actif !== null) {
                $queryBuilder->andWhere('a.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $affectations = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (AffectationsUtilisateurs $affectation) {
                return $this->formatAffectationData($affectation);
            }, $affectations);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'service' => $service->getNom(),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des affectations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les affectations actuelles (actives et sans date de fin)
     * GET /api/affectations/actuelles
     */
    #[Route('/actuelles', name: 'current', methods: ['GET'])]
    public function current(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

            $queryBuilder = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->leftJoin('a.serviceId', 's')
                ->addSelect('u', 's')
                ->where('a.actif = true')
                ->andWhere('a.dateFin IS NULL OR a.dateFin > :now')
                ->setParameter('now', new \DateTime())
                ->orderBy('a.dateDebut', 'DESC');

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $affectations = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (AffectationsUtilisateurs $affectation) {
                return $this->formatAffectationData($affectation);
            }, $affectations);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $pages,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des affectations actuelles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les statistiques d'affectation
     * GET /api/affectations/stats
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        try {
            $repository = $this->entityManager->getRepository(AffectationsUtilisateurs::class);

            // Total
            $total = count($repository->findAll());

            // Actives/Inactives
            $actives = count($repository->findBy(['actif' => true]));
            $inactives = $total - $actives;

            // Affectations actuelles (sans date de fin)
            $actuelles = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->where('a.actif = true')
                ->andWhere('a.dateFin IS NULL')
                ->getQuery()
                ->getResult();
            $nbActuelles = count($actuelles);

            // Par service
            $queryBuilder = $this->entityManager->getRepository(AffectationsUtilisateurs::class)
                ->createQueryBuilder('a')
                ->select('s.nom, COUNT(a.id) as count')
                ->leftJoin('a.serviceId', 's')
                ->groupBy('s.id')
                ->getQuery()
                ->getResult();

            $parService = [];
            foreach ($queryBuilder as $row) {
                $parService[$row['nom']] = (int)$row['count'];
            }

            // Pourcentage de temps moyen
            $affectations = $repository->findAll();
            $pourcentageMoyen = 0;
            $affectationsAvecPourcentage = 0;
            foreach ($affectations as $affectation) {
                if ($affectation->getPourcentageTemps()) {
                    $pourcentageMoyen += (float)$affectation->getPourcentageTemps();
                    $affectationsAvecPourcentage++;
                }
            }
            if ($affectationsAvecPourcentage > 0) {
                $pourcentageMoyen = round($pourcentageMoyen / $affectationsAvecPourcentage, 2);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'actives' => $actives,
                    'inactives' => $inactives,
                    'actuelles' => $nbActuelles,
                    'pourcentage_temps_moyen' => $pourcentageMoyen,
                    'par_service' => $parService,
                ],
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Formate les données d'une affectation pour la réponse JSON
     */
    private function formatAffectationData(AffectationsUtilisateurs $affectation, bool $detailed = false): array
    {
        $data = [
            'id' => $affectation->getId(),
            'utilisateur' => [
                'id' => $affectation->getUtilisateurId()->getId(),
                'nom' => $affectation->getUtilisateurId()->getNom(),
                'prenom' => $affectation->getUtilisateurId()->getPrenom(),
            ],
            'service' => [
                'id' => $affectation->getServiceId()->getId(),
                'nom' => $affectation->getServiceId()->getNom(),
            ],
            'date_debut' => $affectation->getDateDebut()->format('Y-m-d'),
            'date_fin' => $affectation->getDateFin()?->format('Y-m-d'),
            'pourcentage_temps' => $affectation->getPourcentageTemps(),
            'actif' => $affectation->getActif(),
            'date_creation' => $affectation->getDateCreation()?->format('c'),
        ];

        if ($detailed) {
            $data['duree_jours'] = $affectation->getDateFin() 
                ? $affectation->getDateFin()->diff($affectation->getDateDebut())->days
                : null;
        }

        return $data;
    }
}
