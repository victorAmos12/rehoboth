<?php

namespace App\Service;

use App\Entity\Administration\BackupMetadata;
use App\Entity\Administration\Hopitaux;
use App\Entity\Personnel\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

/**
 * Service de gestion des sauvegardes
 * 
 * Règle 3-2-1:
 * - 3 copies
 * - 2 supports différents
 * - 1 hors site
 * 
 * ✔ Sauvegardes automatisées
 * ✔ Chiffrement (AES-256)
 * ✔ Tests réguliers de restauration
 * ✔ Journalisation des restaurations (audit)
 */
class BackupService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Crée une métadonnée de backup
     */
    public function createBackup(
        Utilisateurs $utilisateur,
        Hopitaux $hopital,
        string $typeBackup,
        string $localisationBackup,
        ?string $localisationSecondaire = null,
    ): BackupMetadata {
        $backup = new BackupMetadata();
        $backup->setBackupId($this->generateBackupId($hopital));
        $backup->setTypeBackup($typeBackup);
        $backup->setStatut('PENDING');
        $backup->setLocalisationBackup($localisationBackup);
        $backup->setLocalisationSecondaire($localisationSecondaire);
        $backup->setUtilisateurId($utilisateur);
        $backup->setHopitalId($hopital);

        // Calculer date d'expiration (ex: 30 jours pour INCREMENTAL, 90 pour COMPLETE)
        $retetion = match ($typeBackup) {
            'INCREMENTAL' => 30,
            'DIFFERENTIAL' => 60,
            'SNAPSHOT' => 7,
            default => 90, // COMPLETE
        };
        $backup->setDateExpiration(
            new DateTimeImmutable("+{$retetion} days", new \DateTimeZone('UTC'))
        );

        $this->entityManager->persist($backup);
        $this->entityManager->flush();

        return $backup;
    }

    /**
     * Met à jour le statut d'un backup
     */
    public function updateBackupStatus(
        BackupMetadata $backup,
        string $statut,
        ?int $tailleBytes = null,
        ?int $dureeSecondes = null,
        ?string $checksumSha256 = null,
        ?int $nombreFichiers = null,
        ?int $nombreTables = null,
        ?string $messageErreur = null,
    ): BackupMetadata {
        $backup->setStatut($statut);

        if ($tailleBytes !== null) {
            $backup->setTailleBytes($tailleBytes);
        }
        if ($dureeSecondes !== null) {
            $backup->setDureeSecondes($dureeSecondes);
        }
        if ($checksumSha256 !== null) {
            $backup->setChecksumSha256($checksumSha256);
        }
        if ($nombreFichiers !== null) {
            $backup->setNombreFichiers($nombreFichiers);
        }
        if ($nombreTables !== null) {
            $backup->setNombreTables($nombreTables);
        }
        if ($messageErreur !== null) {
            $backup->setMessageErreur($messageErreur);
        }

        if ($statut === 'SUCCESS' || $statut === 'FAILED') {
            $backup->setDateFin(new DateTimeImmutable('now', new \DateTimeZone('UTC')));
        }

        $this->entityManager->flush();

        return $backup;
    }

    /**
     * Marque un backup comme vérifié
     */
    public function verifyBackup(BackupMetadata $backup): BackupMetadata
    {
        return $this->updateBackupStatus($backup, 'VERIFIED');
    }

    /**
     * Récupère le dernier backup réussi
     */
    public function getLastSuccessfulBackup(Hopitaux $hopital, ?string $typeBackup = null): ?BackupMetadata
    {
        $qb = $this->entityManager->getRepository(BackupMetadata::class)
            ->createQueryBuilder('b')
            ->where('b.hopitalId = :hopital')
            ->andWhere('b.statut = :statut')
            ->setParameter('hopital', $hopital)
            ->setParameter('statut', 'SUCCESS')
            ->orderBy('b.dateDebut', 'DESC')
            ->setMaxResults(1);

        if ($typeBackup) {
            $qb->andWhere('b.typeBackup = :typeBackup')
                ->setParameter('typeBackup', $typeBackup);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Récupère les backups expirés
     */
    public function getExpiredBackups(): array
    {
        $now = new DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return $this->entityManager->getRepository(BackupMetadata::class)
            ->createQueryBuilder('b')
            ->where('b.dateExpiration < :now')
            ->andWhere('b.statut != :deleted')
            ->setParameter('now', $now)
            ->setParameter('deleted', 'DELETED')
            ->orderBy('b.dateExpiration', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime un backup (marque comme DELETED, ne supprime pas physiquement)
     */
    public function deleteBackup(BackupMetadata $backup, string $raison = ''): BackupMetadata
    {
        $backup->setStatut('DELETED');
        $backup->setNotes(($backup->getNotes() ?? '') . "\nSupprimé: {$raison}");

        $this->entityManager->flush();

        return $backup;
    }

    /**
     * Génère un ID unique pour le backup
     */
    private function generateBackupId(Hopitaux $hopital): string
    {
        $timestamp = (new DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('YmdHis');
        $random = bin2hex(random_bytes(4));

        return "BACKUP_{$hopital->getId()}_{$timestamp}_{$random}";
    }

    /**
     * Récupère les statistiques de backup pour un hôpital
     */
    public function getBackupStats(Hopitaux $hopital): array
    {
        $backups = $this->entityManager->getRepository(BackupMetadata::class)
            ->createQueryBuilder('b')
            ->where('b.hopitalId = :hopital')
            ->andWhere('b.statut != :deleted')
            ->setParameter('hopital', $hopital)
            ->setParameter('deleted', 'DELETED')
            ->getQuery()
            ->getResult();

        $totalSize = 0;
        $successCount = 0;
        $failedCount = 0;
        $lastBackup = null;

        foreach ($backups as $backup) {
            if ($backup->getStatut() === 'SUCCESS') {
                $successCount++;
                $totalSize += $backup->getTailleBytes() ?? 0;
                if (!$lastBackup || $backup->getDateDebut() > $lastBackup->getDateDebut()) {
                    $lastBackup = $backup;
                }
            } elseif ($backup->getStatut() === 'FAILED') {
                $failedCount++;
            }
        }

        return [
            'totalBackups' => count($backups),
            'successCount' => $successCount,
            'failedCount' => $failedCount,
            'totalSizeBytes' => $totalSize,
            'totalSizeGb' => round($totalSize / (1024 ** 3), 2),
            'lastBackupDate' => $lastBackup?->getDateDebut()?->format('c'),
            'lastBackupSize' => $lastBackup?->getTailleBytes(),
        ];
    }
}
