<?php

namespace App\Service;

use App\Entity\Administration\AuditTrail;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;

/**
 * Service d'audit immuable
 * 
 * Traçabilité légale: QUI a fait QUOI, QUAND, OÙ
 * 🚫 AUCUN DELETE
 * 👉 L'audit est IMMUABLE
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
}
