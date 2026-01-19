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
 * Endpoints de gestion des logs (audit trail + logs techniques)
 * 
 * 🚫 AUCUN DELETE
 * 👉 L'audit est IMMUABLE
 * ✅ Lecture seule (GET)
 * ✅ Traçabilité légale: QUI/QUOI/QUAND/OÙ
 * ✅ Logs techniques: DEBUG, INFO, WARNING, ERROR, CRITICAL
 * 
 * @see https://owasp.org/www-project-application-security-verification-standard/
 * @see https://www.iso.org/standard/27001
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
 */
#[Route('/api/administrations/logs-audit', name: 'api_logs_audit_')]
class LogsAuditController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JwtService $jwtService,
    ) {}

    /**
     * Liste les logs (audit trail ou techniques)
     * 
     * Filtres disponibles:
     * - typeLog: AUDIT ou TECHNIQUE
     * - actionType: CREATE, READ, UPDATE, DELETE, EXPORT, IMPORT, LOGIN, LOGOUT
     * - niveau: DEBUG, INFO, WARNING, ERROR, CRITICAL (pour logs techniques)
     * - categorie: APPLICATION, HTTP, DATABASE, SECURITY, PERFORMANCE, SYSTEM
     * - entiteType: Patient, Utilisateur, Service, etc.
     * - statut: SUCCESS, FAILURE, PARTIAL
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopitalId = $request->query->getInt('hopitalId', 0);
            $typeLog = $request->query->get('typeLog');
            $actionType = $request->query->get('actionType');
            $niveau = $request->query->get('niveau');
            $categorie = $request->query->get('categorie');
            $entiteType = $request->query->get('entiteType');
            $entiteId = $request->query->get('entiteId');
            $utilisateurId = $request->query->get('utilisateurId');
            $statut = $request->query->get('statut');
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $endpoint = $request->query->get('endpoint');
            $codeHttp = $request->query->get('codeHttp');

            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(500, max(1, $request->query->getInt('limit', 100)));
            $offset = ($page - 1) * $limit;

            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->leftJoin('l.utilisateurId', 'u')
                ->leftJoin('l.hopitalId', 'h')
                ->addSelect('u', 'h')
                ->orderBy('l.dateCreation', 'DESC');

            if ($hopitalId > 0) {
                $qb->andWhere('h.id = :hopitalId')->setParameter('hopitalId', $hopitalId);
            }
            if ($typeLog) {
                $qb->andWhere('l.typeLog = :typeLog')->setParameter('typeLog', $typeLog);
            }
            if ($actionType) {
                $qb->andWhere('l.actionType = :actionType')->setParameter('actionType', $actionType);
            }
            if ($niveau) {
                $qb->andWhere('l.niveau = :niveau')->setParameter('niveau', $niveau);
            }
            if ($categorie) {
                $qb->andWhere('l.categorie = :categorie')->setParameter('categorie', $categorie);
            }
            if ($entiteType) {
                $qb->andWhere('l.entiteType = :entiteType')->setParameter('entiteType', $entiteType);
            }
            if ($entiteId) {
                $qb->andWhere('l.entiteId = :entiteId')->setParameter('entiteId', (int) $entiteId);
            }
            if ($utilisateurId) {
                $qb->andWhere('u.id = :utilisateurId')->setParameter('utilisateurId', (int) $utilisateurId);
            }
            if ($statut) {
                $qb->andWhere('l.statut = :statut')->setParameter('statut', $statut);
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

            $data = array_map(static function (LogsAudit $log): array {
                $u = $log->getUtilisateurId();
                $h = $log->getHopitalId();

                return [
                    'id' => $log->getId(),
                    'typeLog' => $log->getTypeLog(),
                    'actionType' => $log->getActionType(),
                    'entiteType' => $log->getEntiteType(),
                    'entiteId' => $log->getEntiteId(),
                    'description' => $log->getDescription(),
                    'niveau' => $log->getNiveau(),
                    'categorie' => $log->getCategorie(),
                    'message' => $log->getMessage(),
                    'statut' => $log->getStatut(),
                    'messageErreur' => $log->getMessageErreur(),
                    'endpoint' => $log->getEndpoint(),
                    'methodeHttp' => $log->getMethodeHttp(),
                    'codeHttp' => $log->getCodeHttp(),
                    'tempsReponseMs' => $log->getTempsReponseMs(),
                    'adresseIp' => $log->getAdresseIp(),
                    'dateCreation' => $log->getDateCreation()->format('c'),
                    'utilisateur' => [
                        'id' => $u?->getId(),
                        'nom' => method_exists($u, 'getNom') ? $u->getNom() : null,
                        'prenom' => method_exists($u, 'getPrenom') ? $u->getPrenom() : null,
                        'username' => method_exists($u, 'getUsername') ? $u->getUsername() : null,
                        'email' => method_exists($u, 'getEmail') ? $u->getEmail() : null,
                    ],
                    'hopital' => [
                        'id' => $h?->getId(),
                        'nom' => method_exists($h, 'getNom') ? $h->getNom() : null,
                    ],
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

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $log = $this->entityManager->getRepository(LogsAudit::class)->find($id);
            if (!$log) {
                return $this->json(['success' => false, 'error' => 'Log non trouvé'], 404);
            }

            $u = $log->getUtilisateurId();
            $h = $log->getHopitalId();

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $log->getId(),
                    'typeLog' => $log->getTypeLog(),
                    'actionType' => $log->getActionType(),
                    'entiteType' => $log->getEntiteType(),
                    'entiteId' => $log->getEntiteId(),
                    'description' => $log->getDescription(),
                    'ancienneValeur' => $log->getAncienneValeurArray(),
                    'nouvelleValeur' => $log->getNouvelleValeurArray(),
                    'niveau' => $log->getNiveau(),
                    'categorie' => $log->getCategorie(),
                    'message' => $log->getMessage(),
                    'contexte' => $log->getContexte(),
                    'stackTrace' => $log->getStackTrace(),
                    'statut' => $log->getStatut(),
                    'messageErreur' => $log->getMessageErreur(),
                    'endpoint' => $log->getEndpoint(),
                    'methodeHttp' => $log->getMethodeHttp(),
                    'codeHttp' => $log->getCodeHttp(),
                    'tempsReponseMs' => $log->getTempsReponseMs(),
                    'adresseIp' => $log->getAdresseIp(),
                    'userAgent' => $log->getUserAgent(),
                    'signature' => $log->getSignature(),
                    'dateCreation' => $log->getDateCreation()->format('c'),
                    'utilisateur' => [
                        'id' => $u?->getId(),
                        'nom' => method_exists($u, 'getNom') ? $u->getNom() : null,
                        'prenom' => method_exists($u, 'getPrenom') ? $u->getPrenom() : null,
                        'username' => method_exists($u, 'getUsername') ? $u->getUsername() : null,
                        'email' => method_exists($u, 'getEmail') ? $u->getEmail() : null,
                    ],
                    'hopital' => [
                        'id' => $h?->getId(),
                        'nom' => method_exists($h, 'getNom') ? $h->getNom() : null,
                    ],
                ],
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Historique d'une entité (audit trail)
     */
    #[Route('/entite/{entiteType}/{entiteId}', name: 'entity_history', methods: ['GET'])]
    public function entityHistory(string $entiteType, int $entiteId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $limit = min(500, max(1, $request->query->getInt('limit', 50)));

            $logs = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->leftJoin('l.utilisateurId', 'u')
                ->addSelect('u')
                ->where('l.entiteType = :entiteType')
                ->andWhere('l.entiteId = :entiteId')
                ->setParameter('entiteType', $entiteType)
                ->setParameter('entiteId', $entiteId)
                ->orderBy('l.dateCreation', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(static function (LogsAudit $log): array {
                $u = $log->getUtilisateurId();

                return [
                    'id' => $log->getId(),
                    'actionType' => $log->getActionType(),
                    'description' => $log->getDescription(),
                    'ancienneValeur' => $log->getAncienneValeurArray(),
                    'nouvelleValeur' => $log->getNouvelleValeurArray(),
                    'statut' => $log->getStatut(),
                    'dateCreation' => $log->getDateCreation()->format('c'),
                    'utilisateur' => [
                        'id' => $u?->getId(),
                        'nom' => method_exists($u, 'getNom') ? $u->getNom() : null,
                        'prenom' => method_exists($u, 'getPrenom') ? $u->getPrenom() : null,
                    ],
                ];
            }, $logs);

            return $this->json([
                'success' => true,
                'entiteType' => $entiteType,
                'entiteId' => $entiteId,
                'total' => count($data),
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Historique d'un utilisateur
     */
    #[Route('/utilisateur/{utilisateurId}', name: 'user_history', methods: ['GET'])]
    public function userHistory(int $utilisateurId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $limit = min(500, max(1, $request->query->getInt('limit', 50)));

            $logs = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->where('l.utilisateurId = :utilisateur')
                ->setParameter('utilisateur', $utilisateurId)
                ->orderBy('l.dateCreation', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(static function (LogsAudit $log): array {
                return [
                    'id' => $log->getId(),
                    'actionType' => $log->getActionType(),
                    'entiteType' => $log->getEntiteType(),
                    'entiteId' => $log->getEntiteId(),
                    'description' => $log->getDescription(),
                    'statut' => $log->getStatut(),
                    'dateCreation' => $log->getDateCreation()->format('c'),
                ];
            }, $logs);

            return $this->json([
                'success' => true,
                'utilisateurId' => $utilisateurId,
                'total' => count($data),
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Statistiques des logs
     */
    #[Route('/stats/summary', name: 'stats_summary', methods: ['GET'])]
    public function statsSummary(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasAuditRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopitalId = $request->query->getInt('hopitalId', 0);
            $typeLog = $request->query->get('typeLog');
            $dateFrom = $request->query->get('dateFrom');

            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l');

            if ($hopitalId > 0) {
                $qb->andWhere('l.hopitalId = :hopitalId')->setParameter('hopitalId', $hopitalId);
            }
            if ($typeLog) {
                $qb->andWhere('l.typeLog = :typeLog')->setParameter('typeLog', $typeLog);
            }

            if ($dateFrom) {
                $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) ?: new \DateTimeImmutable($dateFrom);
                $qb->andWhere('l.dateCreation >= :dateFrom')->setParameter('dateFrom', $dtFrom);
            }

            $stats = [
                'total' => (int) (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult(),
                'byTypeLog' => $this->getCountByField($qb, 'typeLog'),
                'byNiveau' => $this->getCountByField($qb, 'niveau'),
                'byCategorie' => $this->getCountByField($qb, 'categorie'),
                'byActionType' => $this->getCountByField($qb, 'actionType'),
                'errorsCount' => (int) (clone $qb)->andWhere('l.niveau IN (:niveaux)')
                    ->setParameter('niveaux', ['ERROR', 'CRITICAL'])
                    ->select('COUNT(l.id)')
                    ->getQuery()
                    ->getSingleScalarResult(),
                'failureCount' => (int) (clone $qb)->andWhere('l.statut = :statut')
                    ->setParameter('statut', 'FAILURE')
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

    /**
     * Créer un log audit
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            $payload = json_decode($request->getContent(), true);
            if (!is_array($payload)) {
                return $this->json(['success' => false, 'error' => 'Payload JSON invalide'], 400);
            }

            $hopitalId = $payload['hopitalId'] ?? null;
            if (!$hopitalId) {
                return $this->json(['success' => false, 'error' => 'hopitalId est requis'], 400);
            }

            $hopital = $this->entityManager->getRepository(Hopitaux::class)->find((int) $hopitalId);
            if (!$hopital) {
                return $this->json(['success' => false, 'error' => 'Hôpital non trouvé'], 404);
            }

            $log = new LogsAudit();
            $log->setTypeLog($payload['typeLog'] ?? 'AUDIT');
            $log->setActionType($payload['actionType'] ?? null);
            $log->setEntiteType($payload['entiteType'] ?? null);
            $log->setEntiteId(isset($payload['entiteId']) ? (int) $payload['entiteId'] : null);
            $log->setDescription($payload['description'] ?? null);
            $log->setNiveau($payload['niveau'] ?? null);
            $log->setCategorie($payload['categorie'] ?? null);
            $log->setMessage($payload['message'] ?? null);
            $log->setContexte($payload['contexte'] ?? null);
            $log->setAncienneValeurArray($payload['ancienneValeur'] ?? null);
            $log->setNouvelleValeurArray($payload['nouvelleValeur'] ?? null);
            $log->setStatut($payload['statut'] ?? 'SUCCESS');
            $log->setMessageErreur($payload['messageErreur'] ?? null);
            $log->setAdresseIp($request->getClientIp());
            $log->setUserAgent($request->headers->get('User-Agent'));
            $log->setEndpoint($request->getPathInfo());
            $log->setMethodeHttp($request->getMethod());
            $log->setUtilisateurId($user);
            $log->setHopitalId($hopital);

            $this->entityManager->persist($log);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Log créé',
                'id' => $log->getId(),
            ], 201);
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

    private function hasAuditRole(Utilisateurs $user): bool
    {
        $role = $user->getRoleId();
        if (!$role) {
            return false;
        }
        $code = strtoupper($role->getCode());
        // Accepter les codes avec ou sans préfixe ROLE_
        return in_array($code, ['ADMIN', 'SUPER_ADMIN', 'AUDIT', 'COMPLIANCE', 'SYSTEM', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_AUDIT', 'ROLE_COMPLIANCE', 'ROLE_SYSTEM'], true);
    }
}
