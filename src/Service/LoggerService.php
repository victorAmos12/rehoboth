<?php

namespace App\Service;

use App\Entity\Administration\LogsTechniques;
use App\Entity\Administration\Hopitaux;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;

/**
 * Service de journalisation technique
 * 
 * Gère les logs applicatifs, HTTP, performance, sécurité
 * ❌ Jamais de données sensibles
 * ✅ Rotation automatique
 */
class LoggerService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Enregistre un log technique
     */
    public function log(
        string $niveau,
        string $categorie,
        string $message,
        ?array $contexte = null,
        ?string $stackTrace = null,
        ?Hopitaux $hopital = null,
        ?Request $request = null,
    ): LogsTechniques {
        $log = new LogsTechniques();
        $log->setNiveau($niveau);
        $log->setCategorie($categorie);
        $log->setMessage($message);
        $log->setContexte($contexte);
        $log->setStackTrace($stackTrace);
        $log->setHopitalId($hopital);

        if ($request) {
            $log->setAdresseIp($request->getClientIp());
            $log->setUserAgent($request->headers->get('User-Agent'));
            $log->setEndpoint($request->getPathInfo());
            $log->setMethodeHttp($request->getMethod());
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    /**
     * Log d'erreur
     */
    public function error(
        string $message,
        ?array $contexte = null,
        ?string $stackTrace = null,
        ?Hopitaux $hopital = null,
        ?Request $request = null,
    ): LogsTechniques {
        return $this->log('ERROR', 'APPLICATION', $message, $contexte, $stackTrace, $hopital, $request);
    }

    /**
     * Log d'avertissement
     */
    public function warning(
        string $message,
        ?array $contexte = null,
        ?Hopitaux $hopital = null,
        ?Request $request = null,
    ): LogsTechniques {
        return $this->log('WARNING', 'APPLICATION', $message, $contexte, null, $hopital, $request);
    }

    /**
     * Log d'information
     */
    public function info(
        string $message,
        ?array $contexte = null,
        ?Hopitaux $hopital = null,
        ?Request $request = null,
    ): LogsTechniques {
        return $this->log('INFO', 'APPLICATION', $message, $contexte, null, $hopital, $request);
    }

    /**
     * Log de performance HTTP
     */
    public function logHttpPerformance(
        Request $request,
        int $statusCode,
        int $tempsMs,
        ?Hopitaux $hopital = null,
    ): LogsTechniques {
        $log = new LogsTechniques();
        $log->setNiveau($tempsMs > 1000 ? 'WARNING' : 'INFO');
        $log->setCategorie('HTTP');
        $log->setMessage("HTTP {$request->getMethod()} {$request->getPathInfo()} - {$statusCode}");
        $log->setEndpoint($request->getPathInfo());
        $log->setMethodeHttp($request->getMethod());
        $log->setCodeHttp($statusCode);
        $log->setTempsReponseMs($tempsMs);
        $log->setAdresseIp($request->getClientIp());
        $log->setUserAgent($request->headers->get('User-Agent'));
        $log->setHopitalId($hopital);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    /**
     * Log de requête BD lente
     */
    public function logSlowQuery(
        string $query,
        int $tempsMs,
        ?array $params = null,
        ?Hopitaux $hopital = null,
    ): LogsTechniques {
        $log = new LogsTechniques();
        $log->setNiveau('WARNING');
        $log->setCategorie('DATABASE');
        $log->setMessage("Requête lente détectée ({$tempsMs}ms)");
        $log->setContexte([
            'query' => substr($query, 0, 500),
            'tempsMs' => $tempsMs,
            'params' => $params,
        ]);
        $log->setTempsReponseMs($tempsMs);
        $log->setHopitalId($hopital);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    /**
     * Log de tentative de connexion échouée
     */
    public function logFailedLogin(
        string $username,
        string $raison,
        ?Request $request = null,
        ?Hopitaux $hopital = null,
    ): LogsTechniques {
        $log = new LogsTechniques();
        $log->setNiveau('WARNING');
        $log->setCategorie('SECURITY');
        $log->setMessage("Tentative de connexion échouée: {$raison}");
        $log->setContexte(['username' => $username]);
        if ($request) {
            $log->setAdresseIp($request->getClientIp());
            $log->setUserAgent($request->headers->get('User-Agent'));
        }
        $log->setHopitalId($hopital);

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    /**
     * Nettoie les logs anciens (rotation)
     * À appeler via cron job
     */
    public function cleanOldLogs(int $joursRetention = 90): int
    {
        $dateLimit = new DateTimeImmutable("-{$joursRetention} days", new \DateTimeZone('UTC'));

        $qb = $this->entityManager->createQueryBuilder()
            ->delete(LogsTechniques::class, 'l')
            ->where('l.dateCreation < :dateLimit')
            ->setParameter('dateLimit', $dateLimit);

        return $qb->getQuery()->execute();
    }
}
