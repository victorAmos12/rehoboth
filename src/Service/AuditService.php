<?php

namespace App\Service;

use App\Entity\Administration\AuditTrail;
use App\Entity\Administration\LogsAudit;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;

/**
 * Service unifié pour Audit Trail + Logs Techniques
 * 
 * 🔹 AUDIT TRAIL (Immuable)
 * ├─ Traçabilité légale: QUI a fait QUOI, QUAND, OÙ
 * ├─ Actions: CREATE, READ, UPDATE, DELETE, EXPORT, IMPORT, LOGIN, LOGOUT
 * ├─ Conformité: RGPD, ISO 27001, OWASP
 * └─ 🚫 AUCUN DELETE
 * 
 * 🔹 LOGS TECHNIQUES (Modernes avec télémétrie)
 * ├─ Niveaux: DEBUG, INFO, WARNING, ERROR, CRITICAL
 * ├─ Catégories: APPLICATION, HTTP, DATABASE, SECURITY, PERFORMANCE, SYSTEM
 * ├─ Télémétrie: Temps réponse, code HTTP, contexte d'exécution
 * └─ Utilisés pour débogage, surveillance, alertes
 * 
 * @see https://www.iso.org/standard/27001
 * @see https://owasp.org/www-project-application-security-verification-standard/
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html
 */
class AuditService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Enregistre une action d'audit
     */
    public function log(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        string $actionType,
        string $entiteType,
        int $entiteId,
        string $description,
        ?array $ancienneValeur = null,
        ?array $nouvelleValeur = null,
        ?Request $request = null,
        string $statut = 'SUCCESS',
        ?string $messageErreur = null,
    ): AuditTrail {
        $audit = new AuditTrail();
        $audit->setUtilisateurId($utilisateur);
        $audit->setHopitalId($hopital);
        $audit->setActionType($actionType);
        $audit->setEntiteType($entiteType);
        $audit->setEntiteId($entiteId);
        $audit->setDescription($description);
        $audit->setAncienneValeur($ancienneValeur);
        $audit->setNouvelleValeur($nouvelleValeur);
        $audit->setStatut($statut);
        $audit->setMessageErreur($messageErreur);

        if ($request) {
            $audit->setAdresseIp($request->getClientIp());
            $audit->setUserAgent($request->headers->get('User-Agent'));
        }

        // Signature HMAC pour intégrité (optionnel)
        $audit->setSignature($this->generateSignature($audit));

        $this->entityManager->persist($audit);
        $this->entityManager->flush();

        return $audit;
    }

    /**
     * Audit de création
     */
    public function logCreate(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        string $entiteType,
        int $entiteId,
        array $nouvelleValeur,
        ?Request $request = null,
    ): AuditTrail {
        return $this->log(
            $utilisateur,
            $hopital,
            'CREATE',
            $entiteType,
            $entiteId,
            "Création de {$entiteType} #{$entiteId}",
            null,
            $nouvelleValeur,
            $request,
        );
    }

    /**
     * Audit de modification
     */
    public function logUpdate(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        string $entiteType,
        int $entiteId,
        array $ancienneValeur,
        array $nouvelleValeur,
        ?Request $request = null,
    ): AuditTrail {
        return $this->log(
            $utilisateur,
            $hopital,
            'UPDATE',
            $entiteType,
            $entiteId,
            "Modification de {$entiteType} #{$entiteId}",
            $ancienneValeur,
            $nouvelleValeur,
            $request,
        );
    }

    /**
     * Audit de suppression
     */
    public function logDelete(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        string $entiteType,
        int $entiteId,
        array $ancienneValeur,
        ?Request $request = null,
    ): AuditTrail {
        return $this->log(
            $utilisateur,
            $hopital,
            'DELETE',
            $entiteType,
            $entiteId,
            "Suppression de {$entiteType} #{$entiteId}",
            $ancienneValeur,
            null,
            $request,
        );
    }

    /**
     * Audit de lecture/export
     */
    public function logRead(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        string $entiteType,
        int $entiteId,
        string $action = 'READ',
        ?Request $request = null,
    ): AuditTrail {
        return $this->log(
            $utilisateur,
            $hopital,
            $action,
            $entiteType,
            $entiteId,
            "{$action} de {$entiteType} #{$entiteId}",
            null,
            null,
            $request,
        );
    }

    /**
     * Audit de connexion
     */
    public function logLogin(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        bool $success = true,
        ?string $raison = null,
        ?Request $request = null,
    ): AuditTrail {
        return $this->log(
            $utilisateur,
            $hopital,
            'LOGIN',
            'Utilisateur',
            $utilisateur->getId(),
            $success ? 'Connexion réussie' : "Connexion échouée: {$raison}",
            null,
            null,
            $request,
            $success ? 'SUCCESS' : 'FAILURE',
            $success ? null : $raison,
        );
    }

    /**
     * Audit de déconnexion
     */
    public function logLogout(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        ?Request $request = null,
    ): AuditTrail {
        return $this->log(
            $utilisateur,
            $hopital,
            'LOGOUT',
            'Utilisateur',
            $utilisateur->getId(),
            'Déconnexion',
            null,
            null,
            $request,
        );
    }

    /**
     * Audit d'export
     */
    public function logExport(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        string $entiteType,
        int $entiteId,
        string $format = 'PDF',
        ?Request $request = null,
    ): AuditTrail {
        return $this->log(
            $utilisateur,
            $hopital,
            'EXPORT',
            $entiteType,
            $entiteId,
            "Export {$format} de {$entiteType} #{$entiteId}",
            null,
            null,
            $request,
        );
    }

    /**
     * Génère une signature HMAC pour intégrité
     */
    private function generateSignature(AuditTrail $audit): string
    {
        $data = implode('|', [
            $audit->getUtilisateurId()->getId(),
            $audit->getHopitalId()->getId(),
            $audit->getActionType(),
            $audit->getEntiteType(),
            $audit->getEntiteId(),
            $audit->getDateAction()->format('c'),
        ]);

        // Utiliser une clé secrète depuis .env
        $secret = $_ENV['AUDIT_SIGNATURE_KEY'] ?? 'default-secret-key';

        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Vérifie l'intégrité d'un audit
     */
    public function verifySignature(AuditTrail $audit): bool
    {
        $expectedSignature = $this->generateSignature($audit);
        return hash_equals($expectedSignature, $audit->getSignature() ?? '');
    }

    /**
     * Récupère l'historique d'audit pour une entité
     */
    public function getEntityHistory(string $entiteType, int $entiteId, int $limit = 50): array
    {
        return $this->entityManager->getRepository(AuditTrail::class)
            ->createQueryBuilder('a')
            ->where('a.entiteType = :entiteType')
            ->andWhere('a.entiteId = :entiteId')
            ->setParameter('entiteType', $entiteType)
            ->setParameter('entiteId', $entiteId)
            ->orderBy('a.dateAction', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère l'historique d'audit pour un utilisateur
     */
    public function getUserHistory(Utilisateurs $utilisateur, int $limit = 50): array
    {
        return $this->entityManager->getRepository(AuditTrail::class)
            ->createQueryBuilder('a')
            ->where('a.utilisateurId = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('a.dateAction', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Construit une requête filtrée pour les logs
     */
    public function buildFilteredQuery(
        ?string $typeLog = null,
        ?string $actionType = null,
        ?string $niveau = null,
        ?string $categorie = null,
        ?string $entiteType = null,
        ?int $entiteId = null,
        ?int $utilisateurId = null,
        ?int $hopitalId = null,
        ?string $statut = null,
        ?string $endpoint = null,
        ?int $codeHttp = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $searchText = null,
    ): QueryBuilder {
        $qb = $this->entityManager->getRepository(LogsAudit::class)
            ->createQueryBuilder('l')
            ->leftJoin('l.utilisateurId', 'u')
            ->leftJoin('l.hopitalId', 'h')
            ->addSelect('u', 'h');

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
        if ($entiteId !== null) {
            $qb->andWhere('l.entiteId = :entiteId')->setParameter('entiteId', $entiteId);
        }
        if ($utilisateurId !== null) {
            $qb->andWhere('l.utilisateurId = :utilisateurId')->setParameter('utilisateurId', $utilisateurId);
        }
        if ($hopitalId !== null) {
            $qb->andWhere('l.hopitalId = :hopitalId')->setParameter('hopitalId', $hopitalId);
        }
        if ($statut) {
            $qb->andWhere('l.statut = :statut')->setParameter('statut', $statut);
        }
        if ($endpoint) {
            $qb->andWhere('l.endpoint = :endpoint')->setParameter('endpoint', $endpoint);
        }
        if ($codeHttp !== null) {
            $qb->andWhere('l.codeHttp = :codeHttp')->setParameter('codeHttp', $codeHttp);
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
        if ($searchText) {
            $qb->andWhere('l.message LIKE :search OR l.description LIKE :search OR l.endpoint LIKE :search')
               ->setParameter('search', '%' . $searchText . '%');
        }

        return $qb->orderBy('l.dateCreation', 'DESC');
    }

    /**
     * Récupère les statistiques globales
     */
    public function getStatistics(?int $hopitalId = null, ?string $typeLog = null, ?string $dateFrom = null): array
    {
        $dateFromObj = $dateFrom 
            ? (\DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) ?: new \DateTimeImmutable($dateFrom))
            : new \DateTimeImmutable('-7 days');
        $dateToObj = new \DateTimeImmutable();

        $qb = $this->entityManager->getRepository(LogsAudit::class)
            ->createQueryBuilder('l')
            ->where('l.dateCreation >= :dateFrom')
            ->andWhere('l.dateCreation <= :dateTo')
            ->setParameter('dateFrom', $dateFromObj)
            ->setParameter('dateTo', $dateToObj);

        if ($hopitalId) {
            $qb->andWhere('l.hopitalId = :hopitalId')->setParameter('hopitalId', $hopitalId);
        }
        if ($typeLog) {
            $qb->andWhere('l.typeLog = :typeLog')->setParameter('typeLog', $typeLog);
        }

        $total = (int) (clone $qb)->select('COUNT(l.id)')->getQuery()->getSingleScalarResult();

        return [
            'period' => ['from' => $dateFromObj->format('Y-m-d'), 'to' => $dateToObj->format('Y-m-d')],
            'total' => $total,
            'avgResponseTime' => (int) ((clone $qb)->select('AVG(l.tempsReponseMs)')->getQuery()->getSingleScalarResult() ?? 0),
        ];
    }

    /**
     * Log technique
     */
    public function logTechnique(
        Hopitaux $hopital,
        string $niveau = 'INFO',
        string $categorie = 'APPLICATION',
        ?string $message = null,
        ?array $contexte = null,
        ?string $stackTrace = null,
        ?string $endpoint = null,
        ?string $methodeHttp = null,
        ?int $codeHttp = null,
        ?int $tempsReponseMs = null,
        ?string $adresseIp = null,
        ?string $userAgent = null,
        ?Utilisateurs $user = null,
    ): LogsAudit {
        $log = new LogsAudit();
        $log->setTypeLog('TECHNIQUE');
        $log->setNiveau($niveau);
        $log->setCategorie($categorie);
        $log->setMessage($message);
        $log->setContexte($contexte ? json_encode($contexte) : null);
        $log->setEndpoint($endpoint);
        $log->setMethodeHttp($methodeHttp);
        $log->setCodeHttp($codeHttp);
        $log->setTempsReponseMs($tempsReponseMs);
        $log->setAdresseIp($adresseIp);
        $log->setHopitalId($hopital);
        if ($user) {
            $log->setUtilisateurId($user);
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }
}
