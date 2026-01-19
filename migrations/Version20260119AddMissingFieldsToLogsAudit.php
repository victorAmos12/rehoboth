<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter les champs manquants à logs_audit
 * - alerte (boolean)
 * - typeAlerte (string)
 * - traceId (string)
 * - requestId (string)
 */
final class Version20260119AddMissingFieldsToLogsAudit extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing fields to logs_audit table: alerte, typeAlerte, traceId, requestId';
    }

    public function up(Schema $schema): void
    {
        // Ajouter les colonnes manquantes
        $this->addSql('ALTER TABLE logs_audit ADD COLUMN alerte TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD COLUMN type_alerte VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD COLUMN trace_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD COLUMN request_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Supprimer les colonnes
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN IF EXISTS alerte');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN IF EXISTS type_alerte');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN IF EXISTS trace_id');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN IF EXISTS request_id');
    }
}
