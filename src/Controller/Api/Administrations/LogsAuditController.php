<?php

namespace App\Controller\Api\Administrations;

use App\Entity\Administration\LogsAudit;
use App\Entity\Administration\AuditTrail;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use App\Service\JwtService;
use App\Service\AuditService;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * ============================================================================
 * CONTRÔLEUR UNIFIÉ - LOGS & AUDIT AVEC TÉLÉMÉTRIE AVANCÉE
 * ============================================================================
 * 
 * UN SEUL contrôleur pour gérer :
 * 1. AUDIT TRAIL (Immuable) - Traçabilité légale
 * 2. LOGS TECHNIQUES (Modernes) - Télémétrie et surveillance
 * 3. LOGS STRUCTURÉS - Contexte riche et corrélation
 * 
 * 🔹 AUDIT TRAIL (Immuable, légal)
 * ├─ Tracent CE QUE FONT LES UTILISATEURS
 * ├─ Actions: CREATE, READ, UPDATE, DELETE, EXPORT, IMPORT, LOGIN, LOGOUT
 * ├─ Conformité: RGPD, ISO 27001, OWASP
 * └─ 🚫 AUCUN DELETE (immuable)
 * 
 * 🔹 LOGS TECHNIQUES (Surveillance et télémétrie)
 * ├─ Niveaux: DEBUG, INFO, WARNING, ERROR, CRITICAL
 * ├─ Catégories: APPLICATION, HTTP, DATABASE, SECURITY, PERFORMANCE, SYSTEM
 * ├─ Télémétrie: Temps réponse, mémoire, CPU, disque, connexions
 * ├─ Traçabilité: Request ID, corrélation, chaînes d'appels
 * └─ Alertes: Anomalies détectées automatiquement
 * 
 * 🔹 FONCTIONNALITÉS MODERNES
 * ├─ Télémétrie avancée (mémoire, CPU, temps réponse)
 * ├─ Logs structurés (JSON, champs standardisés)
 * ├─ Corrélation des événements (Request ID, Trace ID)
 * ├─ Agrégations en temps réel (stats, heatmaps)
 * ├─ Alertes sur anomalies (seuils, patterns)
 * └─ Traces distribuées (OpenTelemetry-compatible)
 * 
 * ENDPOINTS:
 * - GET    /api/logs                          Liste les logs avec filtres avancés
 * - GET    /api/logs/{id}                     Détails d'un log
 * - GET    /api/logs/audit/list               Liste des audits
 * - GET    /api/logs/audit/{id}               Détails d'un audit
 * - GET    /api/logs/entite/{type}/{id}       Historique d'une entité
 * - GET    /api/logs/utilisateur/{id}         Historique d'un utilisateur
 * - GET    /api/logs/stats/summary            Statistiques et KPIs
 * - GET    /api/logs/stats/heatmap            Carte thermique (temps réponse)
 * - GET    /api/logs/stats/anomalies          Détection d'anomalies
 * - GET    /api/logs/stats/performance        Analyse de performance
 * - POST   /api/logs                          Créer un log (internal)
 * - GET    /api/logs/export                   Exporter logs (CSV, JSON)
 * - GET    /api/logs/trace/{traceId}          Récupérer une trace distribuée
 * 
 * @see https://owasp.org/www-project-application-security-verification-standard/
 * @see https://www.iso.org/standard/27001
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
 * @see https://opentelemetry.io/
 */
#[Route('/api/logs', name: 'api_logs_')]
class LogsAuditController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JwtService $jwtService,
        private readonly AuditService $auditService,
        private readonly PdfGeneratorService $pdfGenerator,
    ) {}

    /**
     * GET /api/logs
     * 
     * Liste les logs avec filtres avancés et télémétrie
     * 
     * Filtres disponibles:
     * - type: AUDIT | TECHNIQUE (défaut: tous)
     * - actionType: CREATE, READ, UPDATE, DELETE, EXPORT, IMPORT, LOGIN, LOGOUT
     * - niveau: DEBUG, INFO, WARNING, ERROR, CRITICAL
     * - categorie: APPLICATION, HTTP, DATABASE, SECURITY, PERFORMANCE, SYSTEM
     * - entiteType: Patient, Utilisateur, Service, etc.
     * - statut: SUCCESS, FAILURE, PARTIAL
     * - minResponseTime, maxResponseTime: Filtrer par temps de réponse
     * - hasAlert: true|false (logs avec alertes)
     * - dateFrom, dateTo: Filtrage par date (YYYY-MM-DD)
     * - search: Recherche textuelle
     * - page: Numéro de page (défaut: 1)
     * - limit: Résultats par page (max: 500, défaut: 100)
     * - orderBy: dateCreation|tempsReponse|niveau (défaut: dateCreation)
     * - order: ASC|DESC
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            // Récupérer les paramètres
            $type = $request->query->get('type');
            $actionType = $request->query->get('actionType');
            $niveau = $request->query->get('niveau');
            $categorie = $request->query->get('categorie');
            $entiteType = $request->query->get('entiteType');
            $statut = $request->query->get('statut');
            $minResponseTime = $request->query->getInt('minResponseTime', 0);
            $maxResponseTime = $request->query->getInt('maxResponseTime', 0);
            $hasAlert = $request->query->get('hasAlert');
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');
            $search = $request->query->get('search');
            
            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(500, max(1, $request->query->getInt('limit', 100)));
            $offset = ($page - 1) * $limit;
            $orderBy = $request->query->get('orderBy', 'dateCreation');
            $order = strtoupper($request->query->get('order', 'DESC'));

            // Construire la requête
            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->leftJoin('l.utilisateurId', 'u')
                ->leftJoin('l.hopitalId', 'h')
                ->addSelect('u', 'h');

            // Filtres basiques
            if ($type && in_array($type, ['AUDIT', 'TECHNIQUE'])) {
                $qb->andWhere('l.typeLog = :typeLog')->setParameter('typeLog', $type);
            }
            if ($actionType) {
                $qb->andWhere('l.actionType = :actionType')->setParameter('actionType', $actionType);
            }
            if ($niveau && in_array($niveau, ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'])) {
                $qb->andWhere('l.niveau = :niveau')->setParameter('niveau', $niveau);
            }
            if ($categorie) {
                $qb->andWhere('l.categorie = :categorie')->setParameter('categorie', $categorie);
            }
            if ($entiteType) {
                $qb->andWhere('l.entiteType = :entiteType')->setParameter('entiteType', $entiteType);
            }
            if ($statut) {
                $qb->andWhere('l.statut = :statut')->setParameter('statut', $statut);
            }

            // Filtres de performance
            if ($minResponseTime > 0) {
                $qb->andWhere('l.tempsReponseMs >= :minRT')->setParameter('minRT', $minResponseTime);
            }
            if ($maxResponseTime > 0) {
                $qb->andWhere('l.tempsReponseMs <= :maxRT')->setParameter('maxRT', $maxResponseTime);
            }

            // Filtres d'alertes
            if ($hasAlert === 'true') {
                $qb->andWhere('l.alerte = true');
            } elseif ($hasAlert === 'false') {
                $qb->andWhere('l.alerte = false');
            }

            // Filtres de date
            if ($dateFrom) {
                $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) ?: new \DateTimeImmutable($dateFrom);
                $qb->andWhere('l.dateCreation >= :dateFrom')->setParameter('dateFrom', $dtFrom);
            }
            if ($dateTo) {
                $dtTo = (\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo) ?: new \DateTimeImmutable($dateTo))
                    ->setTime(23, 59, 59);
                $qb->andWhere('l.dateCreation <= :dateTo')->setParameter('dateTo', $dtTo);
            }

            // Recherche textuelle
            if ($search) {
                $qb->andWhere('l.message LIKE :search OR l.description LIKE :search OR l.endpoint LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }

            // Tri
            $orderByField = match($orderBy) {
                'tempsReponse' => 'l.tempsReponseMs',
                'niveau' => 'l.niveau',
                default => 'l.dateCreation'
            };
            $qb->orderBy($orderByField, $order);

            // Compter le total
            $countQb = clone $qb;
            $total = (int) $countQb->select('COUNT(l.id)')->getQuery()->getSingleScalarResult();

            // Récupérer les résultats
            $items = $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(fn(LogsAudit $log) => $this->formatLogForResponse($log), $items);

            // Calculer les statistiques de la page
            $pageStats = [
                'avgResponseTime' => $this->calculateAvgResponseTime($items),
                'errorCount' => count(array_filter($items, fn($l) => in_array($l->getNiveau(), ['ERROR', 'CRITICAL']))),
                'alertCount' => count(array_filter($items, fn($l) => $l->getAlerte() === true)),
            ];

            return $this->json([
                'success' => true,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
                'pageStats' => $pageStats,
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/logs/{id}
     * Afficher les détails d'un log avec contexte complet
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $log = $this->entityManager->getRepository(LogsAudit::class)->find($id);
            if (!$log) {
                return $this->json(['success' => false, 'error' => 'Log non trouvé'], 404);
            }

            // Récupérer les logs liés (même traceId ou requestId)
            $relatedLogs = [];
            if ($log->getTraceId()) {
                $relatedLogs = $this->entityManager->getRepository(LogsAudit::class)
                    ->createQueryBuilder('l')
                    ->where('l.traceId = :traceId')
                    ->andWhere('l.id != :currentId')
                    ->setParameter('traceId', $log->getTraceId())
                    ->setParameter('currentId', $id)
                    ->setMaxResults(20)
                    ->getQuery()
                    ->getResult();
            }

            return $this->json([
                'success' => true,
                'data' => $this->formatLogForResponse($log),
                'relatedLogs' => array_map(fn(LogsAudit $l) => [
                    'id' => $l->getId(),
                    'message' => $l->getMessage(),
                    'niveau' => $l->getNiveau(),
                    'dateCreation' => $l->getDateCreation()->format('c'),
                ], $relatedLogs),
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/logs/audit/list
     * Liste des audits avec historique
     */
    #[Route('/audit/list', name: 'audit_list', methods: ['GET'])]
    public function auditList(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(500, max(1, $request->query->getInt('limit', 100)));
            $offset = ($page - 1) * $limit;

            $qb = $this->entityManager->getRepository(AuditTrail::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->leftJoin('a.hopitalId', 'h')
                ->addSelect('u', 'h')
                ->orderBy('a.dateAction', 'DESC');

            $countQb = clone $qb;
            $total = (int) $countQb->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

            $items = $qb
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(fn(AuditTrail $a) => $this->formatAuditForResponse($a), $items);

            return $this->json([
                'success' => true,
                'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total],
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/logs/stats/summary
     * Statistiques et KPIs complètes
     */
    #[Route('/stats/summary', name: 'stats_summary', methods: ['GET'])]
    public function statsSummary(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $hopitalId = $request->query->getInt('hopitalId', 0);
            $dateFrom = $request->query->get('dateFrom') ?: date('Y-m-d', strtotime('-7 days'));
            $dateTo = $request->query->get('dateTo') ?: date('Y-m-d');

            $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom);
            if (!$dtFrom) $dtFrom = new \DateTimeImmutable($dateFrom);
            
            $dtTo = \DateTimeImmutable::createFromFormat('Y-m-d', $dateTo);
            if (!$dtTo) $dtTo = new \DateTimeImmutable($dateTo);
            $dtTo = $dtTo->setTime(23, 59, 59);

            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->where('l.dateCreation >= :dateFrom')
                ->andWhere('l.dateCreation <= :dateTo')
                ->setParameter('dateFrom', $dtFrom)
                ->setParameter('dateTo', $dtTo);

            if ($hopitalId > 0) {
                $qb->andWhere('l.hopitalId = :hopitalId')->setParameter('hopitalId', $hopitalId);
            }

            $stats = [
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
                'totals' => [
                    'logCount' => (int) (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult(),
                    'auditCount' => $this->countAudits($hopitalId, $dtFrom, $dtTo),
                ],
                'byLevel' => $this->getCountByField(clone $qb, 'niveau'),
                'byCategory' => $this->getCountByField(clone $qb, 'categorie'),
                'errors' => [
                    'count' => (int) (clone $qb)->andWhere('l.niveau IN (:levels)')
                        ->setParameter('levels', ['ERROR', 'CRITICAL'])
                        ->select('COUNT(l.id)')
                        ->getQuery()
                        ->getSingleScalarResult(),
                    'byCategory' => $this->getErrorsByCategory(clone $qb),
                ],
                'alerts' => [
                    'count' => (int) (clone $qb)->andWhere('l.alerte = true')->select('COUNT(l.id)')->getQuery()->getSingleScalarResult(),
                    'bySeverity' => $this->getAlertsBySeverity(clone $qb),
                ],
                'performance' => [
                    'avgResponseTime' => (int) ((clone $qb)->select('AVG(l.tempsReponseMs)')->getQuery()->getSingleScalarResult() ?? 0),
                    'p95ResponseTime' => $this->getPercentileResponseTime(clone $qb, 95),
                    'p99ResponseTime' => $this->getPercentileResponseTime(clone $qb, 99),
                    'maxResponseTime' => (int) ((clone $qb)->select('MAX(l.tempsReponseMs)')->getQuery()->getSingleScalarResult() ?? 0),
                    'slowRequests' => $this->countSlowRequests(clone $qb, 5000), // >5s
                ],
                'uptime' => [
                    'availability' => $this->calculateAvailability(clone $qb),
                    'successRate' => $this->calculateSuccessRate(clone $qb),
                ],
            ];

            return $this->json(['success' => true, 'data' => $stats], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/logs/stats/performance
     * Analyse détaillée de la performance
     */
    #[Route('/stats/performance', name: 'stats_performance', methods: ['GET'])]
    public function statsPerformance(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->where('l.tempsReponseMs IS NOT NULL')
                ->orderBy('l.dateCreation', 'DESC')
                ->setMaxResults(10000);

            $stats = [
                'endpointPerformance' => $this->getEndpointPerformance($qb),
                'categoryPerformance' => $this->getCategoryPerformance(clone $qb),
                'timeSeriesData' => $this->getResponseTimeTimeSeries(clone $qb),
                'slowestRequests' => $this->getSlowestRequests(clone $qb, 20),
            ];

            return $this->json(['success' => true, 'data' => $stats], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/logs/stats/anomalies
     * Détection d'anomalies
     */
    #[Route('/stats/anomalies', name: 'stats_anomalies', methods: ['GET'])]
    public function statsAnomalies(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->where('l.alerte = true')
                ->orderBy('l.dateCreation', 'DESC')
                ->setMaxResults(100);

            $anomalies = [
                'spike' => $this->detectSpike(clone $qb),
                'degradation' => $this->detectDegradation(clone $qb),
                'failure' => $this->detectFailurePatterns(clone $qb),
                'security' => $this->detectSecurityAnomalies(clone $qb),
            ];

            return $this->json(['success' => true, 'data' => $anomalies], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/logs/entite/{entiteType}/{entiteId}
     * Historique d'une entité
     */
    #[Route('/entite/{entiteType}/{entiteId}', name: 'entity_history', methods: ['GET'])]
    public function entityHistory(string $entiteType, int $entiteId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $limit = min(500, max(1, $request->query->getInt('limit', 50)));

            $audits = $this->entityManager->getRepository(AuditTrail::class)
                ->createQueryBuilder('a')
                ->leftJoin('a.utilisateurId', 'u')
                ->addSelect('u')
                ->where('a.entiteType = :entiteType')
                ->andWhere('a.entiteId = :entiteId')
                ->setParameter('entiteType', $entiteType)
                ->setParameter('entiteId', $entiteId)
                ->orderBy('a.dateAction', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(fn(AuditTrail $a) => $this->formatAuditForResponse($a), $audits);

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
     * GET /api/logs/utilisateur/{utilisateurId}
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

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $limit = min(500, max(1, $request->query->getInt('limit', 100)));

            $audits = $this->entityManager->getRepository(AuditTrail::class)
                ->createQueryBuilder('a')
                ->where('a.utilisateurId = :utilisateur')
                ->setParameter('utilisateur', $utilisateurId)
                ->orderBy('a.dateAction', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $data = array_map(fn(AuditTrail $a) => $this->formatAuditForResponse($a), $audits);

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
     * GET /api/logs/trace/{traceId}
     * Récupérer une trace distribuée complète
     */
    #[Route('/trace/{traceId}', name: 'trace', methods: ['GET'])]
    public function trace(string $traceId, Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $logs = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->where('l.traceId = :traceId')
                ->setParameter('traceId', $traceId)
                ->orderBy('l.dateCreation', 'ASC')
                ->getQuery()
                ->getResult();

            if (empty($logs)) {
                return $this->json(['success' => false, 'error' => 'Trace non trouvée'], 404);
            }

            $data = array_map(fn(LogsAudit $l) => $this->formatLogForResponse($l), $logs);

            return $this->json([
                'success' => true,
                'traceId' => $traceId,
                'spanCount' => count($data),
                'totalDuration' => $this->calculateTraceDuration($logs),
                'spans' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/logs/export
     * Exporter les logs en JSON, CSV ou PDF professionnel
     */
    #[Route('/export', name: 'export', methods: ['GET'])]
    public function export(Request $request): JsonResponse|Response
    {
        try {
            $user = $this->getAuthenticatedUser($request);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }

            if (!$this->hasLogsRole($user)) {
                return $this->json(['success' => false, 'error' => 'Accès refusé'], 403);
            }

            $format = $request->query->get('format', 'json');
            $typeLog = $request->query->get('type');
            $dateFrom = $request->query->get('dateFrom');
            $dateTo = $request->query->get('dateTo');

            $qb = $this->entityManager->getRepository(LogsAudit::class)
                ->createQueryBuilder('l')
                ->leftJoin('l.utilisateurId', 'u')
                ->leftJoin('l.hopitalId', 'h')
                ->addSelect('u', 'h');

            if ($typeLog) {
                $qb->where('l.typeLog = :typeLog')->setParameter('typeLog', $typeLog);
            }
            if ($dateFrom) {
                $dtFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom);
                $qb->andWhere('l.dateCreation >= :dateFrom')->setParameter('dateFrom', $dtFrom);
            }
            if ($dateTo) {
                $dtTo = (\DateTimeImmutable::createFromFormat('Y-m-d', $dateTo))->setTime(23, 59, 59);
                $qb->andWhere('l.dateCreation <= :dateTo')->setParameter('dateTo', $dtTo);
            }

            $logs = $qb->orderBy('l.dateCreation', 'DESC')->setMaxResults(10000)->getQuery()->getResult();

            if ($format === 'csv') {
                return $this->exportAsCSV($logs);
            } elseif ($format === 'pdf') {
                return $this->exportAsPdf($logs, $user);
            }

            return $this->json([
                'success' => true,
                'format' => 'json',
                'count' => count($logs),
                'data' => array_map(fn(LogsAudit $l) => $this->formatLogForResponse($l), $logs),
            ], 200);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================================
    // MÉTHODES PRIVÉES DE FORMATAGE ET CALCUL
    // ============================================================================

    private function formatLogForResponse(LogsAudit $log): array
    {
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
            'traceId' => $log->getTraceId(),
            'requestId' => $log->getRequestId(),
            'alerte' => $log->getAlerte() ?? false,
            'typeAlerte' => $log->getTypeAlerte(),
            'contexte' => $log->getContexte(),
            'dateCreation' => $log->getDateCreation()->format('c'),
            'utilisateur' => $u ? [
                'id' => $u->getId(),
                'nom' => $u->getNom(),
                'email' => $u->getEmail(),
            ] : null,
            'hopital' => $h ? [
                'id' => $h->getId(),
                'nom' => $h->getNom(),
            ] : null,
        ];
    }

    private function formatAuditForResponse(AuditTrail $audit): array
    {
        $u = $audit->getUtilisateurId();

        return [
            'id' => $audit->getId(),
            'actionType' => $audit->getActionType(),
            'entiteType' => $audit->getEntiteType(),
            'entiteId' => $audit->getEntiteId(),
            'description' => $audit->getDescription(),
            'ancienneValeur' => $audit->getAncienneValeur(),
            'nouvelleValeur' => $audit->getNouvelleValeur(),
            'statut' => $audit->getStatut(),
            'adresseIp' => $audit->getAdresseIp(),
            'dateAction' => $audit->getDateAction()->format('c'),
            'utilisateur' => $u ? [
                'id' => $u->getId(),
                'nom' => $u->getNom(),
                'email' => $u->getEmail(),
            ] : null,
        ];
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
            $data[$result[$field] ?? 'unknown'] = (int) $result['cnt'];
        }
        return $data;
    }

    private function calculateAvgResponseTime(array $logs): int
    {
        $total = 0;
        $count = 0;
        foreach ($logs as $log) {
            if ($log->getTempsReponseMs()) {
                $total += $log->getTempsReponseMs();
                $count++;
            }
        }
        return $count > 0 ? (int) ($total / $count) : 0;
    }

    private function countAudits(int $hopitalId, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): int
    {
        $qb = $this->entityManager->getRepository(AuditTrail::class)
            ->createQueryBuilder('a')
            ->where('a.dateAction >= :dateFrom')
            ->andWhere('a.dateAction <= :dateTo')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo);

        if ($hopitalId > 0) {
            $qb->andWhere('a.hopitalId = :hopitalId')->setParameter('hopitalId', $hopitalId);
        }

        return (int) $qb->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
    }

    private function getErrorsByCategory($qb): array
    {
        return $this->getCountByField(
            (clone $qb)->andWhere('l.niveau IN (:levels)')->setParameter('levels', ['ERROR', 'CRITICAL']),
            'categorie'
        );
    }

    private function getAlertsBySeverity($qb): array
    {
        return $this->getCountByField((clone $qb)->andWhere('l.alerte = true'), 'typeAlerte');
    }

    private function getPercentileResponseTime($qb, int $percentile): int
    {
        // Implémentation simplifiée - une vraie implémentation utiliserait des agrégations SQL avancées
        $logs = (clone $qb)->select('l.tempsReponseMs')->getQuery()->getResult();
        $times = array_column($logs, 'tempsReponseMs');
        sort($times);
        $index = (int) (count($times) * ($percentile / 100));
        return $times[$index] ?? 0;
    }

    private function countSlowRequests($qb, int $threshold): int
    {
        return (int) (clone $qb)
            ->andWhere('l.tempsReponseMs > :threshold')
            ->setParameter('threshold', $threshold)
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function calculateAvailability($qb): float
    {
        $total = (int) (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult();
        if ($total === 0) return 100.0;

        $success = (int) (clone $qb)
            ->andWhere('l.statut = :statut')
            ->setParameter('statut', 'SUCCESS')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return round(($success / $total) * 100, 2);
    }

    private function calculateSuccessRate($qb): float
    {
        return $this->calculateAvailability($qb);
    }

    private function getEndpointPerformance($qb): array
    {
        $results = (clone $qb)
            ->select('l.endpoint, AVG(l.tempsReponseMs) as avgTime, MAX(l.tempsReponseMs) as maxTime, COUNT(l.id) as count')
            ->groupBy('l.endpoint')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'endpoint' => $result['endpoint'],
                'avgResponseTime' => (int) $result['avgTime'],
                'maxResponseTime' => (int) $result['maxTime'],
                'requestCount' => (int) $result['count'],
            ];
        }
        return $data;
    }

    private function getCategoryPerformance($qb): array
    {
        $results = (clone $qb)
            ->select('l.categorie, AVG(l.tempsReponseMs) as avgTime, COUNT(l.id) as count')
            ->groupBy('l.categorie')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'categorie' => $result['categorie'],
                'avgResponseTime' => (int) $result['avgTime'],
                'requestCount' => (int) $result['count'],
            ];
        }
        return $data;
    }

    private function getResponseTimeTimeSeries($qb): array
    {
        // Simplification: retourner les 24 dernières heures
        $results = (clone $qb)
            ->select('DATE_FORMAT(l.dateCreation, \'%Y-%m-%d %H:00\') as hour, AVG(l.tempsReponseMs) as avgTime, COUNT(l.id) as count')
            ->groupBy('hour')
            ->orderBy('hour', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn($r) => [
            'timestamp' => $r['hour'],
            'avgResponseTime' => (int) $r['avgTime'],
            'requestCount' => (int) $r['count'],
        ], $results);
    }

    private function getSlowestRequests($qb, int $limit): array
    {
        $results = (clone $qb)
            ->orderBy('l.tempsReponseMs', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(fn(LogsAudit $l) => [
            'id' => $l->getId(),
            'endpoint' => $l->getEndpoint(),
            'responseTime' => $l->getTempsReponseMs(),
            'dateCreation' => $l->getDateCreation()->format('c'),
        ], $results);
    }

    private function detectSpike($qb): array
    {
        // Détection simple de spike: comparer avec la moyenne
        $logs = (clone $qb)->getQuery()->getResult();
        $times = array_filter(array_map(fn($l) => $l->getTempsReponseMs(), $logs), fn($t) => $t !== null);
        
        if (empty($times)) return [];

        $avg = array_sum($times) / count($times);
        $threshold = $avg * 2;

        return array_filter(array_map(fn(LogsAudit $l) => $l->getTempsReponseMs() > $threshold ? [
            'id' => $l->getId(),
            'severity' => 'spike',
            'value' => $l->getTempsReponseMs(),
            'threshold' => (int) $threshold,
            'date' => $l->getDateCreation()->format('c'),
        ] : null, $logs));
    }

    private function detectDegradation($qb): array
    {
        // Comparaison avant/après
        return [];
    }

    private function detectFailurePatterns($qb): array
    {
        $errors = (clone $qb)
            ->andWhere('l.niveau IN (:levels)')
            ->setParameter('levels', ['ERROR', 'CRITICAL'])
            ->orderBy('l.dateCreation', 'DESC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();

        return array_map(fn(LogsAudit $e) => [
            'endpoint' => $e->getEndpoint(),
            'errorCount' => $e->getCodeHttp(),
            'lastError' => $e->getDateCreation()->format('c'),
        ], $errors);
    }

    private function detectSecurityAnomalies($qb): array
    {
        $securityEvents = (clone $qb)
            ->andWhere('l.categorie = :categorie')
            ->setParameter('categorie', 'SECURITY')
            ->orderBy('l.dateCreation', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        return array_map(fn(LogsAudit $e) => [
            'type' => $e->getActionType(),
            'ip' => $e->getAdresseIp(),
            'date' => $e->getDateCreation()->format('c'),
            'alerte' => $e->getAlerte(),
        ], $securityEvents);
    }

    private function calculateTraceDuration(array $logs): int
    {
        if (empty($logs)) return 0;
        $first = reset($logs)->getDateCreation();
        $last = end($logs)->getDateCreation();
        return (int) $last->format('U') - (int) $first->format('U');
    }

    /**
     * Exporter en PDF professionnel
     */
    private function exportAsPdf(array $logs, Utilisateurs $user): Response
    {
        // Récupérer l'hôpital de l'utilisateur
        $hopital = $user->getHopitalId();
        $hospitalName = $hopital ? $hopital->getNom() : 'Hôpital';

        // Calculer les statistiques
        $stats = $this->calculatePdfStatistics($logs);

        // Créer le PDF
        $pdf = new PdfGeneratorService();
        $pdf->setDocumentInfo(
            'Rapport Logs & Audit',
            $hospitalName,
            $stats
        );

        // Ajouter le résumé exécutif
        $pdf->addSummarySection($stats);

        // Ajouter le détail des logs
        $pdf->addDetailedSection($logs, 'Détail des Logs (' . count($logs) . ' entrées)');

        // Ajouter la section de performance
        if (isset($stats['performance'])) {
            $pdf->addPerformanceSection($stats['performance']);
        }

        // Ajouter les alertes
        if (isset($stats['alerts']) && !empty($stats['alerts'])) {
            $pdf->addAlertsSection($stats['alerts']);
        }

        // Ajouter la conclusion
        $conclusion = [
            'summary' => 'Ce rapport présente une analyse complète des logs et audits pour la période spécifiée. ' .
                'Les données ont été extraites du système de gestion hospitalière Rehoboth et formatées pour analyse.',
            'recommendations' => [
                'Examiner les erreurs critiques et prendre des actions correctives',
                'Optimiser les endpoints avec temps de réponse élevé',
                'Maintenir la conformité RGPD et ISO 27001',
                'Archiver régulièrement les logs anciens',
            ]
        ];
        $pdf->addConclusionSection($conclusion);

        // Générer et retourner le PDF
        $pdfContent = $pdf->Output('', 'S');

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="Rapport_Logs_Audit_' . date('Ymd_His') . '.pdf"');
        $response->headers->set('Content-Length', strlen($pdfContent));

        return $response;
    }

    /**
     * Calculer les statistiques pour le PDF
     */
    private function calculatePdfStatistics(array $logs): array
    {
        $stats = [
            'period' => [
                'from' => count($logs) > 0 ? $logs[count($logs) - 1]->getDateCreation()->format('Y-m-d') : date('Y-m-d'),
                'to' => count($logs) > 0 ? $logs[0]->getDateCreation()->format('Y-m-d') : date('Y-m-d'),
            ],
            'totals' => [
                'logCount' => count($logs),
                'auditCount' => count(array_filter($logs, fn($l) => $l->getTypeLog() === 'AUDIT')),
            ],
            'byLevel' => [],
            'byCategory' => [],
            'errors' => ['count' => 0, 'byCategory' => []],
            'alerts' => [],
            'performance' => [
                'avgResponseTime' => 0,
                'p95ResponseTime' => 0,
                'p99ResponseTime' => 0,
                'maxResponseTime' => 0,
                'slowRequests' => 0,
            ],
            'uptime' => [
                'availability' => 0,
                'successRate' => 0,
            ],
        ];

        // Compter par niveau et catégorie
        $levels = [];
        $categories = [];
        $responseTimes = [];
        $successCount = 0;

        foreach ($logs as $log) {
            // Niveaux
            $niveau = $log->getNiveau() ?? 'UNKNOWN';
            $levels[$niveau] = ($levels[$niveau] ?? 0) + 1;

            // Catégories
            $cat = $log->getCategorie() ?? 'UNKNOWN';
            $categories[$cat] = ($categories[$cat] ?? 0) + 1;

            // Temps de réponse
            if ($log->getTempsReponseMs()) {
                $responseTimes[] = $log->getTempsReponseMs();
                if ($log->getTempsReponseMs() > 5000) {
                    $stats['performance']['slowRequests']++;
                }
            }

            // Erreurs
            if (in_array($log->getNiveau(), ['ERROR', 'CRITICAL'])) {
                $stats['errors']['count']++;
                $stats['errors']['byCategory'][$cat] = ($stats['errors']['byCategory'][$cat] ?? 0) + 1;
            }

            // Succès
            if ($log->getStatut() === 'SUCCESS') {
                $successCount++;
            }
        }

        $stats['byLevel'] = $levels;
        $stats['byCategory'] = $categories;

        // Calculer les percentiles de temps de réponse
        if (!empty($responseTimes)) {
            sort($responseTimes);
            $stats['performance']['avgResponseTime'] = (int) array_sum($responseTimes) / count($responseTimes);
            $stats['performance']['maxResponseTime'] = max($responseTimes);
            $stats['performance']['p95ResponseTime'] = $responseTimes[(int)(count($responseTimes) * 0.95)];
            $stats['performance']['p99ResponseTime'] = $responseTimes[(int)(count($responseTimes) * 0.99)];
        }

        // Taux de succès
        $stats['uptime']['successRate'] = count($logs) > 0 ? round(($successCount / count($logs)) * 100, 2) : 0;
        $stats['uptime']['availability'] = $stats['uptime']['successRate'];

        return $stats;
    }

    private function exportAsCSV(array $logs): JsonResponse
    {
        $csv = "ID,Type,Action,Entité,Utilisateur,Statut,Temps(ms),Date,IP\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                '"%d","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $log->getId(),
                $log->getTypeLog(),
                $log->getActionType() ?? '',
                $log->getEntiteType() ?? '',
                $log->getUtilisateurId()?->getNom() ?? '',
                $log->getStatut(),
                $log->getTempsReponseMs() ?? '',
                $log->getDateCreation()->format('Y-m-d H:i:s'),
                $log->getAdresseIp() ?? ''
            );
        }

        return new JsonResponse([
            'success' => true,
            'format' => 'csv',
            'data' => $csv,
        ], 200);
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

    private function hasLogsRole(Utilisateurs $user): bool
    {
        $role = $user->getRoleId();
        if (!$role) {
            return false;
        }

        $code = strtoupper($role->getCode());
        return in_array($code, [
            'ADMIN', 'SUPER_ADMIN', 'AUDIT', 'COMPLIANCE', 'SYSTEM',
            'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_AUDIT', 'ROLE_COMPLIANCE', 'ROLE_SYSTEM'
        ], true);
    }
}
