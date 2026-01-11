<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table chambres et ajouter la relation avec lits
 */
final class Version20250115CreateChambresTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create chambres table and add chambre_id foreign key to lits table';
    }

    public function up(Schema $schema): void
    {
        // Créer la table chambres
        $this->addSql('CREATE TABLE chambres (
            id INT AUTO_INCREMENT NOT NULL,
            service_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_chambre VARCHAR(50) NOT NULL,
            etage INT DEFAULT NULL,
            nombre_lits INT DEFAULT NULL,
            type_chambre VARCHAR(50) DEFAULT NULL,
            statut VARCHAR(50) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            localisation VARCHAR(255) DEFAULT NULL,
            climatisee TINYINT(1) DEFAULT NULL,
            sanitaires_prives TINYINT(1) DEFAULT NULL,
            television TINYINT(1) DEFAULT NULL,
            telephone TINYINT(1) DEFAULT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_hopital (hopital_id),
            INDEX idx_service (service_id),
            INDEX idx_statut (statut),
            CONSTRAINT FK_CHAMBRES_SERVICE FOREIGN KEY (service_id) REFERENCES services (id),
            CONSTRAINT FK_CHAMBRES_HOPITAL FOREIGN KEY (hopital_id) REFERENCES hopitaux (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Ajouter la colonne chambre_id à la table lits
        $this->addSql('ALTER TABLE lits ADD chambre_id INT NOT NULL AFTER service_id');
        
        // Ajouter la contrainte de clé étrangère
        $this->addSql('ALTER TABLE lits ADD CONSTRAINT FK_LITS_CHAMBRE FOREIGN KEY (chambre_id) REFERENCES chambres (id)');
        
        // Ajouter l'index
        $this->addSql('CREATE INDEX idx_chambre ON lits (chambre_id)');

        // Supprimer la colonne chambre de la table lits si elle existe
        $this->addSql('ALTER TABLE lits DROP COLUMN chambre');
    }

    public function down(Schema $schema): void
    {
        // Supprimer la contrainte de clé étrangère
        $this->addSql('ALTER TABLE lits DROP FOREIGN KEY FK_LITS_CHAMBRE');
        
        // Supprimer l'index
        $this->addSql('DROP INDEX idx_chambre ON lits');
        
        // Ajouter la colonne chambre
        $this->addSql('ALTER TABLE lits ADD chambre VARCHAR(50) DEFAULT NULL AFTER etage');
        
        // Supprimer la colonne chambre_id
        $this->addSql('ALTER TABLE lits DROP COLUMN chambre_id');

        // Supprimer la table chambres
        $this->addSql('DROP TABLE chambres');
    }
}
