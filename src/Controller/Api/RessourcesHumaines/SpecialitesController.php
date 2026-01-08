<?php

namespace App\Controller\Api\RessourcesHumaines;

use App\Entity\Personnel\Specialites;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTimeImmutable;
use Exception;

/**
 * Contrôleur API pour la gestion des spécialités médicales
 * 
 * Gère:
 * - CRUD complet des spécialités (Create, Read, Update, Delete)
 * - Recherche et filtrage
 * - Statistiques
 */
#[Route('/api/specialites', name: 'api_specialites_')]
class SpecialitesController extends AbstractController
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
     * Récupère la liste de toutes les spécialités
     * GET /api/specialites
     * 
     * Paramètres de requête:
     * - page: numéro de page (défaut: 1)
     * - limit: nombre d'éléments par page (défaut: 20, max: 100)
     * - search: recherche par nom, code, description
     * - actif: filtrer par statut actif/inactif
     * - sort: champ de tri (défaut: nom)
     * - order: ordre de tri ASC/DESC (défaut: ASC)
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));
            $search = trim($request->query->get('search', ''));
            $actif = $request->query->get('actif');
            $sort = $request->query->get('sort', 'nom');
            $order = strtoupper($request->query->get('order', 'ASC'));

            if (!in_array($order, ['ASC', 'DESC'])) {
                $order = 'ASC';
            }

            $queryBuilder = $this->entityManager->getRepository(Specialites::class)
                ->createQueryBuilder('s');

            // Appliquer les filtres
            if (!empty($search)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->like('s.nom', ':search'),
                        $queryBuilder->expr()->like('s.code', ':search'),
                        $queryBuilder->expr()->like('s.description', ':search'),
                        $queryBuilder->expr()->like('s.codeSnomed', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            if ($actif !== null) {
                $queryBuilder->andWhere('s.actif = :actif')
                    ->setParameter('actif', filter_var($actif, FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer le tri et la pagination
            $queryBuilder->orderBy('s.' . $sort, $order)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $specialites = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Specialites $specialite) {
                return $this->formatSpecialiteData($specialite);
            }, $specialites);

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
                'error' => 'Erreur lors de la récupération des spécialités: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails d'une spécialité
     * GET /api/specialites/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $specialite = $this->entityManager->getRepository(Specialites::class)
                ->find($id);

            if (!$specialite) {
                return $this->json([
                    'success' => false,
                    'error' => 'Spécialité non trouvée',
                ], 404);
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatSpecialiteData($specialite, true),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération de la spécialité: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée une nouvelle spécialité
     * POST /api/specialites
     * 
     * Champs requis:
     * - code: code unique de la spécialité
     * - nom: nom de la spécialité
     * 
     * Champs optionnels:
     * - description: description de la spécialité
     * - code_snomed: code SNOMED CT
     * - icone: URL de l'icône
     * - couleur: couleur en format #RRGGBB
     * - actif: statut actif/inactif (défaut: true)
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Valider les champs requis
            if (!isset($data['code']) || empty($data['code']) || !isset($data['nom']) || empty($data['nom'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs code et nom sont requis',
                ], 400);
            }

            // Vérifier l'unicité du code
            $existingSpecialite = $this->entityManager->getRepository(Specialites::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingSpecialite) {
                return $this->json([
                    'success' => false,
                    'error' => 'Ce code de spécialité est déjà utilisé',
                ], 409);
            }

            // Créer la spécialité
            $specialite = new Specialites();
            $specialite->setCode($data['code']);
            $specialite->setNom($data['nom']);

            if (isset($data['description'])) $specialite->setDescription($data['description']);
            if (isset($data['code_snomed'])) $specialite->setCodeSnomed($data['code_snomed']);
            if (isset($data['icone'])) $specialite->setIcone($data['icone']);
            if (isset($data['couleur'])) $specialite->setCouleur($data['couleur']);

            // Valider l'entité
            $errors = $this->validator->validate($specialite);
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

            $this->entityManager->persist($specialite);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Spécialité créée avec succès',
                'data' => $this->formatSpecialiteData($specialite),
            ], 201);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la création de la spécialité: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour une spécialité existante
     * PUT /api/specialites/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $specialite = $this->entityManager->getRepository(Specialites::class)->find($id);

            if (!$specialite) {
                return $this->json([
                    'success' => false,
                    'error' => 'Spécialité non trouvée',
                ], 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['nom'])) $specialite->setNom($data['nom']);
            if (isset($data['description'])) $specialite->setDescription($data['description']);
            if (isset($data['code_snomed'])) $specialite->setCodeSnomed($data['code_snomed']);
            if (isset($data['icone'])) $specialite->setIcone($data['icone']);
            if (isset($data['couleur'])) $specialite->setCouleur($data['couleur']);
            if (isset($data['actif'])) $specialite->setActif($data['actif']);

            // Valider l'entité
            $errors = $this->validator->validate($specialite);
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
                'message' => 'Spécialité mise à jour avec succès',
                'data' => $this->formatSpecialiteData($specialite),
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour de la spécialité: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime une spécialité
     * DELETE /api/specialites/{id}
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $specialite = $this->entityManager->getRepository(Specialites::class)->find($id);

            if (!$specialite) {
                return $this->json([
                    'success' => false,
                    'error' => 'Spécialité non trouvée',
                ], 404);
            }

            // Soft delete - marquer comme inactif
            $specialite->setActif(false);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Spécialité supprimée avec succès',
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de la spécialité: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recherche avancée de spécialités
     * POST /api/specialites/search
     */
    #[Route('/search', name: 'search', methods: ['POST'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $page = max(1, (int)($data['page'] ?? 1));
            $limit = min(100, max(1, (int)($data['limit'] ?? 20)));

            $queryBuilder = $this->entityManager->getRepository(Specialites::class)
                ->createQueryBuilder('s');

            // Appliquer les filtres
            if (isset($data['nom']) && !empty($data['nom'])) {
                $queryBuilder->andWhere('s.nom LIKE :nom')
                    ->setParameter('nom', '%' . $data['nom'] . '%');
            }

            if (isset($data['code']) && !empty($data['code'])) {
                $queryBuilder->andWhere('s.code LIKE :code')
                    ->setParameter('code', '%' . $data['code'] . '%');
            }

            if (isset($data['code_snomed']) && !empty($data['code_snomed'])) {
                $queryBuilder->andWhere('s.codeSnomed LIKE :codeSnomed')
                    ->setParameter('codeSnomed', '%' . $data['code_snomed'] . '%');
            }

            if (isset($data['actif'])) {
                $queryBuilder->andWhere('s.actif = :actif')
                    ->setParameter('actif', filter_var($data['actif'], FILTER_VALIDATE_BOOLEAN));
            }

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->orderBy('s.nom', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $specialites = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (Specialites $specialite) {
                return $this->formatSpecialiteData($specialite);
            }, $specialites);

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
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des spécialités
     * GET /api/specialites/stats
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        try {
            $repository = $this->entityManager->getRepository(Specialites::class);

            // Total
            $total = count($repository->findAll());

            // Actives/Inactives
            $actives = count($repository->findBy(['actif' => true]));
            $inactives = $total - $actives;

            return $this->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'actives' => $actives,
                    'inactives' => $inactives,
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
     * Formate les données d'une spécialité pour la réponse JSON
     */
    private function formatSpecialiteData(Specialites $specialite, bool $detailed = false): array
    {
        $data = [
            'id' => $specialite->getId(),
            'code' => $specialite->getCode(),
            'nom' => $specialite->getNom(),
            'description' => $specialite->getDescription(),
            'code_snomed' => $specialite->getCodeSnomed(),
            'icone' => $specialite->getIcone(),
            'couleur' => $specialite->getCouleur(),
            'actif' => $specialite->getActif(),
            'date_creation' => $specialite->getDateCreation()?->format('c'),
        ];

        return $data;
    }
}
