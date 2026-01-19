<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table backup_schedules
 * Gestion des sauvegardes programmées/planifiées
 */
final class Version20250120CreateBackupSchedules extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create backup_schedules table for automated backup scheduling';
    }

    public function up(Schema $schema): void
    {
        // Créer la table backup_schedules
        $this->addSql('CREATE TABLE backup_schedules (
            id INT AUTO_INCREMENT NOT NULL,
            hopital_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            schedule_id VARCHAR(100) NOT NULL UNIQUE,
            type_backup VARCHAR(50) NOT NULL DEFAULT "COMPLETE",
            frequency VARCHAR(20) NOT NULL DEFAULT "DAILY",
            time VARCHAR(5) NOT NULL DEFAULT "02:00",
            day_of_week INT DEFAULT NULL,
            day_of_month INT DEFAULT NULL,
            localisation_backup LONGTEXT NOT NULL,
            localisation_secondaire LONGTEXT DEFAULT NULL,
            retention_days INT NOT NULL DEFAULT 30,
            actif TINYINT(1) NOT NULL DEFAULT 1,
            prochaine_execution DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)",
            derniere_execution DATETIME DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)",
            dernier_statut VARCHAR(20) DEFAULT NULL,
            message_erreur LONGTEXT DEFAULT NULL,
            executions_reussies INT NOT NULL DEFAULT 0,
            executions_echouees INT NOT NULL DEFAULT 0,
            notes LONGTEXT DEFAULT NULL,
            date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT "(DC2Type:datetime_immutable)",
            date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT "(DC2Type:datetime_immutable)",
            PRIMARY KEY(id),
            UNIQUE KEY UNIQ_schedule_id (schedule_id),
            KEY idx_hopital_id (hopital_id),
            KEY idx_utilisateur_id (utilisateur_id),
            KEY idx_actif (actif),
            KEY idx_prochaine_execution (prochaine_execution),
            CONSTRAINT FK_backup_schedules_hopital FOREIGN KEY (hopital_id) REFERENCES hopitaux (id),
            CONSTRAINT FK_backup_schedules_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS backup_schedules');
    }
}
