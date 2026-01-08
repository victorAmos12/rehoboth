<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add 2FA columns to utilisateurs table
 */
final class Version20260108130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add 2FA columns (secret_2fa and pin_2fa) to utilisateurs table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateurs ADD COLUMN secret_2fa VARCHAR(255) NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateurs ADD COLUMN pin_2fa VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateurs DROP COLUMN secret_2fa');
        $this->addSql('ALTER TABLE utilisateurs DROP COLUMN pin_2fa');
    }
}
