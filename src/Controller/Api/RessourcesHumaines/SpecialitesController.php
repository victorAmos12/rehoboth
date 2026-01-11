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
 * Contrôleur API pour la gestion des spécialités
 * 
 * Gère:
 * - CRUD complet des spécialités (Create, Read, Update, Delete)
 * - Recherche et filtrage
 * - Statistiques
 * 
 * Tous les endpoints retournent du JSON
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
     * Récupère la liste de toutes les spécialités avec pagination et filtrage
     * GET /api/specialites
     * 
     * Paramètres de requête:
     * - page: numéro de page (défaut: 1)
     * - limit: nombre d'éléments par page (défaut: 20, max: 100)
     * - search: recherche par nom ou code
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
                        $queryBuilder->expr()->like('LOWER(s.nom)', ':search'),
                        $queryBuilder->expr()->like('LOWER(s.code)', ':search')
                    )
                )
                ->setParameter('search', '%' . strtolower($search) . '%');
            }

            // Compter le total AVANT la pagination
            $countQueryBuilder = clone $queryBuilder;
            $total = count($countQueryBuilder->getQuery()->getResult());

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
                'search' => $search,
            ], 200);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des spécialités: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails complets d'une spécialité
     * GET /api/specialites/{id}
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $specialite = $this->entityManager->getRepository(Specialites::class)->find($id);

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
     * - nom: nom de la spécialité
     * - code: code unique de la spécialité
     * 
     * Champs optionnels:
     * - description: description de la spécialité
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les données doivent être au format JSON',
                ], 400);
            }

            // Valider les champs requis
            if (!isset($data['nom']) || empty($data['nom'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "nom" est requis',
                ], 400);
            }

            if (!isset($data['code']) || empty($data['code'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "code" est requis',
                ], 400);
            }

            // Vérifier l'unicité du code
            $existingCode = $this->entityManager->getRepository(Specialites::class)
                ->findOneBy(['code' => $data['code']]);
            if ($existingCode) {
                return $this->json([
                    'success' => false,
                    'error' => 'Ce code de spécialité est déjà utilisé',
                ], 409);
            }

            // Créer la spécialité
            $specialite = new Specialites();
            $specialite->setNom($data['nom']);
            $specialite->setCode($data['code']);

            // Champs optionnels
            if (isset($data['description'])) {
                $specialite->setDescription($data['description']);
            }

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

            // Mettre à jour les champs
            if (isset($data['nom'])) {
                $specialite->setNom($data['nom']);
            }

            if (isset($data['code'])) {
                // Vérifier l'unicité du code
                $existing = $this->entityManager->getRepository(Specialites::class)
                    ->findOneBy(['code' => $data['code']]);
                if ($existing && $existing->getId() !== $id) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Ce code de spécialité est déjà utilisé',
                    ], 409);
                }
                $specialite->setCode($data['code']);
            }

            if (isset($data['description'])) {
                $specialite->setDescription($data['description']);
            }

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

            // Vérifier si la spécialité est utilisée par des utilisateurs
            $utilisateurs = $this->entityManager->getRepository(\App\Entity\Personnel\Utilisateurs::class)
                ->findBy(['specialiteId' => $specialite]);

            if (!empty($utilisateurs)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Cette spécialité est utilisée par ' . count($utilisateurs) . ' utilisateur(s) et ne peut pas être supprimée',
                ], 409);
            }

            $this->entityManager->remove($specialite);
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

            // Spécialités avec utilisateurs
            $queryBuilder = $this->entityManager->getRepository(\App\Entity\Personnel\Utilisateurs::class)
                ->createQueryBuilder('u')
                ->select('s.id, s.nom, COUNT(u.id) as count')
                ->leftJoin('u.specialiteId', 's')
                ->where('s.id IS NOT NULL')
                ->groupBy('s.id')
                ->orderBy('count', 'DESC')
                ->getQuery()
                ->getResult();

            $specialitesAvecUtilisateurs = [];
            foreach ($queryBuilder as $row) {
                $specialitesAvecUtilisateurs[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom'],
                    'nombre_utilisateurs' => (int)$row['count'],
                ];
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'specialites_avec_utilisateurs' => $specialitesAvecUtilisateurs,
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
     * Récupère les utilisateurs d'une spécialité
     * GET /api/specialites/{id}/utilisateurs
     */
    #[Route('/{id}/utilisateurs', name: 'utilisateurs', methods: ['GET'])]
    public function utilisateurs(int $id, Request $request): JsonResponse
    {
        try {
            $specialite = $this->entityManager->getRepository(Specialites::class)->find($id);

            if (!$specialite) {
                return $this->json([
                    'success' => false,
                    'error' => 'Spécialité non trouvée',
                ], 404);
            }

            $page = max(1, (int)$request->query->get('page', 1));
            $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

            $queryBuilder = $this->entityManager->getRepository(\App\Entity\Personnel\Utilisateurs::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.hopitalId', 'h')
                ->leftJoin('u.roleId', 'r')
                ->leftJoin('u.profilId', 'p')
                ->addSelect('h', 'r', 'p')
                ->where('u.specialiteId = :specialiteId')
                ->setParameter('specialiteId', $specialite)
                ->orderBy('u.nom', 'ASC');

            // Compter le total
            $countQuery = clone $queryBuilder;
            $total = count($countQuery->getQuery()->getResult());

            // Appliquer la pagination
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $utilisateurs = $queryBuilder->getQuery()->getResult();

            $data = array_map(function (\App\Entity\Personnel\Utilisateurs $user) {
                return [
                    'id' => $user->getId(),
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'login' => $user->getLogin(),
                    'telephone' => $user->getTelephone(),
                    'role' => $user->getRoleId()->getNom(),
                    'profil' => $user->getProfilId()->getNom(),
                    'hopital' => $user->getHopitalId()->getNom(),
                    'actif' => $user->getActif(),
                ];
            }, $utilisateurs);

            $pages = ceil($total / $limit);

            return $this->json([
                'success' => true,
                'data' => $data,
                'specialite' => $this->formatSpecialiteData($specialite),
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
                'error' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage(),
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
            'nom' => $specialite->getNom(),
            'code' => $specialite->getCode(),
        ];

        if ($detailed) {
            $data['description'] = $specialite->getDescription();
            
            // Compter les utilisateurs
            $utilisateurs = $this->entityManager->getRepository(\App\Entity\Personnel\Utilisateurs::class)
                ->findBy(['specialiteId' => $specialite]);
            $data['nombre_utilisateurs'] = count($utilisateurs);
        }

        return $data;
    }
}
