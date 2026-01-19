<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table audit_trail (audit immuable)
 * 
 * Table de traçabilité IMMUABLE pour conformité RGPD, ISO 27001, OWASP
 * Enregistre QUI a fait QUOI, QUAND, OÙ, COMMENT et POURQUOI
 */
final class Version20260119CreateAuditTrailTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create audit_trail table for immutable audit logging';
    }

    public function up(Schema $schema): void
    {
        // Créer la table audit_trail
        $this->addSql('CREATE TABLE audit_trail (
            id INT AUTO_INCREMENT NOT NULL,
            utilisateur_id INT DEFAULT NULL,
            hopital_id INT DEFAULT NULL,
            action_type VARCHAR(50) NOT NULL DEFAULT "UPDATE",
            entite_type VARCHAR(100) NOT NULL DEFAULT "",
            entite_id INT NOT NULL DEFAULT 0,
            description LONGTEXT DEFAULT NULL,
            ancienne_valeur JSON DEFAULT NULL,
            nouvelle_valeur JSON DEFAULT NULL,
            statut VARCHAR(20) NOT NULL DEFAULT "SUCCESS",
            message_erreur LONGTEXT DEFAULT NULL,
            adresse_ip VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            signature VARCHAR(64) DEFAULT NULL,
            date_action DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            date_creation DATETIME NOT NULL COMMENT "(DC2Type:datetime_immutable)",
            PRIMARY KEY(id),
            INDEX idx_audit_date (date_action),
            INDEX idx_audit_utilisateur (utilisateur_id),
            INDEX idx_audit_hopital (hopital_id),
            INDEX idx_audit_entite (entite_type, entite_id),
            INDEX idx_audit_action (action_type),
            CONSTRAINT FK_AUDIT_UTILISATEUR FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id) ON DELETE SET NULL,
            CONSTRAINT FK_AUDIT_HOPITAL FOREIGN KEY (hopital_id) REFERENCES hopitaux (id) ON DELETE RESTRICT
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la table si elle existe
        $this->addSql('DROP TABLE IF EXISTS audit_trail');
    }
}
