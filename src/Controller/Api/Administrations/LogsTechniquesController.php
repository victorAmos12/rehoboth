<?php

namespace App\Controller\Api\Administrations;

use App\Entity\Administration\LogsAudit;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Endpoints de consultation des logs techniques
 * 
 * ✅ Lecture seule (GET)
 * ✅ Filtres avancés
 * ✅ Pagination
 * ✅ Accès RBAC strict
 * 
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
 */
#[Route('/api/administrations/logs-techniques', name: 'api_logs_techniques_')]
class LogsTechniquesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JwtService $jwtService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            // Vérifier permission ADMIN
            if (!$this->hasAdminRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopitalId = $request->query->getInt('hopitalId', 0);
            $niveau = $request->query->get('niveau');
            $categorie = $request->query->get('categorie');
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $endpoint = $request->query->get('endpoint');
            $codeHttp = $request->query->get('codeHttp');

            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(500, max(1, $request->query->getInt('limit', 100)));
            $offset = ($page - 1) * $limit;

            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->where('l.typeLog = :typeLog')
                ->setParameter('typeLog', 'TECHNIQUE')
                ->leftJoin('l.hopitalId', 'h')
                ->addSelect('h')
                ->orderBy('l.dateCreation', 'DESC');

            if ($hopitalId > 0) {
                $qb->andWhere('h.id = :hopitalId')->setParameter('hopitalId', $hopitalId);
            }
            if ($niveau) {
                $qb->andWhere('l.niveau = :niveau')->setParameter('niveau', $niveau);
            }
            if ($categorie) {
                $qb->andWhere('l.categorie = :categorie')->setParameter('categorie', $categorie);
            }
            if ($endpoint) {
                $qb->andWhere('l.endpoint LIKE :endpoint')->setParameter('endpoint', '%' . $endpoint . '%');
            }
            if ($codeHttp) {
                $qb->andWhere('l.codeHttp = :codeHttp')->setParameter('codeHttp', (int) $codeHttp);
            }

            if ($dateFrom) {
                $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) ?: new \DateTimeImmutable($dateFrom);
                $qb->andWhere('l.dateCreation >= :dateFrom')->setParameter('dateFrom', $dtFrom);
            }
            if ($dateTo) {
                $dtTo = (\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo) ?: new \DateTimeImmutable($dateTo))
                    ->setTime(23, 59, 59);
                $qb->andWhere('l.dateCreation <= :dateTo')->setParameter('dateTo', $dtTo);
            }

            $countQb = clone $qb;
            $total = (int) $countQb->select('COUNT(l.id)')->getQuery()->getSingleScalarResult();

            $items = $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(static function (LogsTechniques $log): array {
                return [
                    'id' => $log->getId(),
                    'niveau' => $log->getNiveau(),
                    'categorie' => $log->getCategorie(),
                    'message' => $log->getMessage(),
                    'contexte' => $log->getContexte(),
                    'endpoint' => $log->getEndpoint(),
                    'methodeHttp' => $log->getMethodeHttp(),
                    'codeHttp' => $log->getCodeHttp(),
                    'tempsReponseMs' => $log->getTempsReponseMs(),
                    'adresseIp' => $log->getAdresseIp(),
                    'dateCreation' => $log->getDateCreation()->format('c'),
                    'hopital' => $log->getHopitalId() ? [
                        'id' => $log->getHopitalId()->getId(),
                        'nom' => $log->getHopitalId()->getNom(),
                    ] : null,
                ];
            }, $items);

            return $this->json([
                'success' => true,
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAdminRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $log = $this->entityManager->getRepository(LogsTechniques::class)->find($id);
            if (!$log) {
                return $this->json(['success' => false, 'error' => 'Log non trouvé'], 404);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $log->getId(),
                    'niveau' => $log->getNiveau(),
                    'categorie' => $log->getCategorie(),
                    'message' => $log->getMessage(),
                    'contexte' => $log->getContexte(),
                    'stackTrace' => $log->getStackTrace(),
                    'endpoint' => $log->getEndpoint(),
                    'methodeHttp' => $log->getMethodeHttp(),
                    'codeHttp' => $log->getCodeHttp(),
                    'tempsReponseMs' => $log->getTempsReponseMs(),
                    'adresseIp' => $log->getAdresseIp(),
                    'userAgent' => $log->getUserAgent(),
                    'dateCreation' => $log->getDateCreation()->format('c'),
                    'hopital' => $log->getHopitalId() ? [
                        'id' => $log->getHopitalId()->getId(),
                        'nom' => $log->getHopitalId()->getNom(),
                    ] : null,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/stats/summary', name: 'stats_summary', methods: ['GET'])]
    public function statsSummary(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAdminRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopitalId = $request->query->getInt('hopitalId', 0);
            $dateFrom = $request->query->get('dateFrom');

            $qb = $this->entityManager->getRepository(LogsTechniques::class)
                ->createQueryBuilder('l');

            if ($hopitalId > 0) {
                $qb->andWhere('l.hopitalId = :hopitalId')->setParameter('hopitalId', $hopitalId);
            }

            if ($dateFrom) {
                $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) ?: new \DateTimeImmutable($dateFrom);
                $qb->andWhere('l.dateCreation >= :dateFrom')->setParameter('dateFrom', $dtFrom);
            }

            $stats = [
                'total' => (int) (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult(),
                'byNiveau' => $this->getCountByField($qb, 'niveau'),
                'byCategorie' => $this->getCountByField($qb, 'categorie'),
                'errorsCount' => (int) (clone $qb)->andWhere('l.niveau IN (:niveaux)')
                    ->setParameter('niveaux', ['ERROR', 'CRITICAL'])
                    ->select('COUNT(l.id)')
                    ->getQuery()
                    ->getSingleScalarResult(),
                'avgResponseTime' => (int) ((clone $qb)->select('AVG(l.tempsReponseMs)')
                    ->getQuery()
                    ->getSingleScalarResult() ?? 0),
            ];

            return $this->json(['success' => true, 'data' => $stats], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    private function getCountByField($qb, string $field): array
    {
        $results = (clone $qb)
            ->select("l.{$field}, COUNT(l.id) as cnt")
            ->groupBy("l.{$field}")
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($results as $result) {
            $data[$result[$field]] = (int) $result['cnt'];
        }

        return $data;
    }

    private function getAuthenticatedUser(Request $request): ?Utilisateurs
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);
        $userId = $this->verifyToken($token);
        if (!$userId) {
            return null;
        }

        return $this->entityManager->getRepository(Utilisateurs::class)->find($userId);
    }

    private function verifyToken(string $token): ?int
    {
        try {
            $payload = $this->jwtService->validateToken($token);
            return $payload->id ?? null;
        } catch (ExpiredException|SignatureInvalidException) {
            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function hasAdminRole(Utilisateurs $user): bool
    {
        $role = $user->getRoleId();
        return $role && in_array($role->getCode(), ['ADMIN', 'SUPER_ADMIN', 'SYSTEM'], true);
    }
}
