<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer les tables types_services et types_poles
 * Ces tables définissent les catégories de services et de pôles d'activité
 */
final class Version20250115CreateTypesServicesAndTypesPoles extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create types_services and types_poles tables for service and pole categorization';
    }

    public function up(Schema $schema): void
    {
        // Créer la table types_services
        $this->addSql('CREATE TABLE types_services (
            id INT AUTO_INCREMENT NOT NULL,
            code VARCHAR(100) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            categorie VARCHAR(50) DEFAULT NULL,
            actif TINYINT(1) DEFAULT 1,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_code (code),
            INDEX idx_code (code)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Créer la table types_poles
        $this->addSql('CREATE TABLE types_poles (
            id INT AUTO_INCREMENT NOT NULL,
            code VARCHAR(100) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            actif TINYINT(1) DEFAULT 1,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_code (code),
            INDEX idx_code (code)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Ajouter les colonnes de relation à la table services si elles n'existent pas
        $this->addSql('ALTER TABLE services ADD COLUMN type_service_id INT DEFAULT NULL AFTER type_service');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_SERVICES_TYPE_SERVICE FOREIGN KEY (type_service_id) REFERENCES types_services (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_type_service_id ON services (type_service_id)');

        // Ajouter les colonnes de relation à la table poles_activite si elles n'existent pas
        $this->addSql('ALTER TABLE poles_activite ADD COLUMN type_pole_id INT DEFAULT NULL AFTER type_pole');
        $this->addSql('ALTER TABLE poles_activite ADD CONSTRAINT FK_POLES_TYPE_POLE FOREIGN KEY (type_pole_id) REFERENCES types_poles (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_type_pole_id ON poles_activite (type_pole_id)');
    }

    public function down(Schema $schema): void
    {
        // Supprimer les contraintes de clé étrangère
        $this->addSql('ALTER TABLE services DROP FOREIGN KEY FK_SERVICES_TYPE_SERVICE');
        $this->addSql('ALTER TABLE poles_activite DROP FOREIGN KEY FK_POLES_TYPE_POLE');

        // Supprimer les index
        $this->addSql('DROP INDEX idx_type_service_id ON services');
        $this->addSql('DROP INDEX idx_type_pole_id ON poles_activite');

        // Supprimer les colonnes
        $this->addSql('ALTER TABLE services DROP COLUMN type_service_id');
        $this->addSql('ALTER TABLE poles_activite DROP COLUMN type_pole_id');

        // Supprimer les tables
        $this->addSql('DROP TABLE types_services');
        $this->addSql('DROP TABLE types_poles');
    }
}
