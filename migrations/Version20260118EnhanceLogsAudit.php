<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour améliorer la table logs_audit
 * - Ajouter des indices pour les performances
 * - Optimiser les requêtes
 */
final class Version20260118EnhanceLogsAudit extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enhance logs_audit table with better indexing';
    }

    public function up(Schema $schema): void
    {
        // Ajouter des indices pour les performances (sans IF NOT EXISTS qui n'existe pas en MySQL)
        $this->addSql('CREATE INDEX idx_type_log_date ON logs_audit(type_log, date_creation)');
        $this->addSql('CREATE INDEX idx_niveau_date ON logs_audit(niveau, date_creation)');
        $this->addSql('CREATE INDEX idx_categorie_date ON logs_audit(categorie, date_creation)');
        $this->addSql('CREATE INDEX idx_action_type_date ON logs_audit(action_type, date_creation)');
        $this->addSql('CREATE INDEX idx_statut_date ON logs_audit(statut, date_creation)');
        $this->addSql('CREATE INDEX idx_entite_type_id ON logs_audit(entite_type, entite_id)');
        $this->addSql('CREATE INDEX idx_utilisateur_date ON logs_audit(utilisateur_id, date_creation)');
        $this->addSql('CREATE INDEX idx_hopital_date ON logs_audit(hopital_id, date_creation)');
        $this->addSql('CREATE INDEX idx_code_http ON logs_audit(code_http)');
        $this->addSql('CREATE INDEX idx_endpoint ON logs_audit(endpoint(100))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_type_log_date ON logs_audit');
        $this->addSql('DROP INDEX idx_niveau_date ON logs_audit');
        $this->addSql('DROP INDEX idx_categorie_date ON logs_audit');
        $this->addSql('DROP INDEX idx_action_type_date ON logs_audit');
        $this->addSql('DROP INDEX idx_statut_date ON logs_audit');
        $this->addSql('DROP INDEX idx_entite_type_id ON logs_audit');
        $this->addSql('DROP INDEX idx_utilisateur_date ON logs_audit');
        $this->addSql('DROP INDEX idx_hopital_date ON logs_audit');
        $this->addSql('DROP INDEX idx_code_http ON logs_audit');
        $this->addSql('DROP INDEX idx_endpoint ON logs_audit');
    }
}
