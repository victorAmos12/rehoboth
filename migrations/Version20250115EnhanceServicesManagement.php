<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour améliorer la gestion des services hospitaliers
 * Ajoute les pôles d'activité et les champs de gestion complète des services
 */
final class Version20250115EnhanceServicesManagement extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add poles_activite table and enhance services table with management fields';
    }

    public function up(Schema $schema): void
    {
        // Créer la table poles_activite
        $this->addSql('CREATE TABLE poles_activite (
            id INT AUTO_INCREMENT NOT NULL,
            hopital_id INT NOT NULL,
            responsable_id INT DEFAULT NULL,
            code VARCHAR(100) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            type_pole VARCHAR(100) DEFAULT NULL,
            budget_annuel DECIMAL(12, 2) DEFAULT NULL,
            actif TINYINT(1) DEFAULT 1,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_hopital (hopital_id),
            INDEX idx_responsable (responsable_id),
            CONSTRAINT FK_POLES_HOPITAL FOREIGN KEY (hopital_id) REFERENCES hopitaux (id),
            CONSTRAINT FK_POLES_RESPONSABLE FOREIGN KEY (responsable_id) REFERENCES utilisateurs (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Ajouter les colonnes à la table services
        $this->addSql('ALTER TABLE services ADD pole_id INT DEFAULT NULL AFTER hopital_id');
        $this->addSql('ALTER TABLE services ADD budget_annuel DECIMAL(12, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE services ADD nombre_personnel INT DEFAULT NULL');
        $this->addSql('ALTER TABLE services ADD horaires_ouverture LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE services ADD niveau_accreditation VARCHAR(100) DEFAULT NULL');
        
        // Ajouter la contrainte de clé étrangère
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_SERVICES_POLE FOREIGN KEY (pole_id) REFERENCES poles_activite (id)');
        
        // Ajouter l'index
        $this->addSql('CREATE INDEX idx_pole ON services (pole_id)');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la contrainte de clé étrangère
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_SERVICES_POLE');
        
        // Supprimer l'index
        $this->addSql('DROP INDEX idx_pole ON services');
        
        // Supprimer les colonnes
        $this->addSql('ALTER TABLE services DROP COLUMN pole_id');
        $this->addSql('ALTER TABLE services DROP COLUMN budget_annuel');
        $this->addSql('ALTER TABLE services DROP COLUMN nombre_personnel');
        $this->addSql('ALTER TABLE services DROP COLUMN horaires_ouverture');
        $this->addSql('ALTER TABLE services DROP COLUMN niveau_accreditation');

        // Supprimer la table poles_activite
        $this->addSql('DROP TABLE poles_activite');
    }
}
