<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250120AddServiceCardFields extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add service card fields to utilisateurs table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateurs ADD adresse_physique VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateurs ADD date_livraison DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateurs ADD validite VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateurs DROP COLUMN adresse_physique');
        $this->addSql('ALTER TABLE utilisateurs DROP COLUMN date_livraison');
        $this->addSql('ALTER TABLE utilisateurs DROP COLUMN validite');
    }
}
