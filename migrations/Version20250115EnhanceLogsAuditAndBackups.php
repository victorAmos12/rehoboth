<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour enrichir les tables logs_audit et sauvegardes
 * avec les champs professionnels pour logs techniques, audit trail et backups
 * 
 * Conforme aux standards OWASP, NIST, ISO 27001 et HIPAA
 */
final class Version20250115EnhanceLogsAuditAndBackups extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enhance logs_audit and sauvegardes tables with professional logging, audit trail and backup fields';
    }

    public function up(Schema $schema): void
    {
        // ===== LOGS_AUDIT TABLE MODIFICATIONS =====
        
        // Ajouter les colonnes pour logs techniques
        $this->addSql('ALTER TABLE logs_audit ADD type_log VARCHAR(20) DEFAULT \'TECHNIQUE\' NOT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD niveau VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD categorie VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD action_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD entite_type VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD message LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD contexte JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD stack_trace LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD endpoint VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD methode_http VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD temps_reponse_ms INT DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD code_http INT DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD statut VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD message_erreur LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE logs_audit ADD signature VARCHAR(255) DEFAULT NULL');
        
        // Ajouter les index pour les nouvelles colonnes
        $this->addSql('CREATE INDEX idx_type_log ON logs_audit (type_log)');
        $this->addSql('CREATE INDEX idx_niveau ON logs_audit (niveau)');
        $this->addSql('CREATE INDEX idx_action_type ON logs_audit (action_type)');
        
        // ===== SAUVEGARDES TABLE MODIFICATIONS =====
        
        // Ajouter les colonnes pour backups professionnels
        $this->addSql('ALTER TABLE sauvegardes ADD backup_id VARCHAR(100) UNIQUE DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD type_backup VARCHAR(50) DEFAULT \'COMPLETE\' NOT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD date_debut DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE sauvegardes ADD date_fin DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD localisation_secondaire LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD checksum_sha256 VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD cle_chiffrement VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD compression VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD nombre_fichiers INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD nombre_tables INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD date_expiration DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD message_erreur LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sauvegardes ADD notes LONGTEXT DEFAULT NULL');
        
        // Ajouter les index pour les nouvelles colonnes
        $this->addSql('CREATE INDEX idx_date_debut ON sauvegardes (date_debut)');
        $this->addSql('CREATE INDEX idx_type_backup ON sauvegardes (type_backup)');
        $this->addSql('CREATE INDEX idx_statut ON sauvegardes (statut)');
    }

    public function down(Schema $schema): void
    {
        // ===== LOGS_AUDIT TABLE ROLLBACK =====
        
        $this->addSql('DROP INDEX idx_type_log ON logs_audit');
        $this->addSql('DROP INDEX idx_niveau ON logs_audit');
        $this->addSql('DROP INDEX idx_action_type ON logs_audit');
        $this->addSql('DROP INDEX idx_date_creation ON logs_audit');
        
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN type_log');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN niveau');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN categorie');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN action_type');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN entite_type');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN description');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN message');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN contexte');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN stack_trace');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN endpoint');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN methode_http');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN temps_reponse_ms');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN code_http');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN statut');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN message_erreur');
        $this->addSql('ALTER TABLE logs_audit DROP COLUMN signature');
        
        // ===== SAUVEGARDES TABLE ROLLBACK =====
        
        $this->addSql('DROP INDEX idx_date_debut ON sauvegardes');
        $this->addSql('DROP INDEX idx_type_backup ON sauvegardes');
        $this->addSql('DROP INDEX idx_statut ON sauvegardes');
        
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN backup_id');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN type_backup');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN date_debut');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN date_fin');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN localisation_secondaire');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN checksum_sha256');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN cle_chiffrement');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN compression');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN nombre_fichiers');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN nombre_tables');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN date_expiration');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN message_erreur');
        $this->addSql('ALTER TABLE sauvegardes DROP COLUMN notes');
    }
}
