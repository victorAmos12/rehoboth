<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter les informations médicales supplémentaires au patient
 * - Antécédents familiaux (père, mère, enfants, épouse, etc.)
 * - Historique des vaccinations
 * - Autres informations critiques du dossier médical
 */
final class Version20260103AddPatientMedicalInfo extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les colonnes pour les antécédents familiaux, vaccinations et autres infos médicales critiques';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('patients');

        // Antécédents familiaux
        if (!$table->hasColumn('antecedents_familiaux_pere')) {
            $table->addColumn('antecedents_familiaux_pere', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Antécédents médicaux du père'
            ]);
        }

        if (!$table->hasColumn('antecedents_familiaux_mere')) {
            $table->addColumn('antecedents_familiaux_mere', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Antécédents médicaux de la mère'
            ]);
        }

        if (!$table->hasColumn('antecedents_familiaux_enfants')) {
            $table->addColumn('antecedents_familiaux_enfants', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Antécédents médicaux des enfants'
            ]);
        }

        if (!$table->hasColumn('antecedents_familiaux_epouse')) {
            $table->addColumn('antecedents_familiaux_epouse', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Antécédents médicaux du conjoint/conjointe'
            ]);
        }

        if (!$table->hasColumn('antecedents_familiaux_autres')) {
            $table->addColumn('antecedents_familiaux_autres', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Autres antécédents familiaux pertinents'
            ]);
        }

        // Vaccinations
        if (!$table->hasColumn('historique_vaccinations')) {
            $table->addColumn('historique_vaccinations', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Historique complet des vaccinations (JSON format)'
            ]);
        }

        if (!$table->hasColumn('date_derniere_vaccination')) {
            $table->addColumn('date_derniere_vaccination', Types::DATE_MUTABLE, [
                'notnull' => false,
                'comment' => 'Date de la dernière vaccination'
            ]);
        }

        // Autres informations critiques
        if (!$table->hasColumn('habitudes_vie')) {
            $table->addColumn('habitudes_vie', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Tabagisme, alcool, drogues, etc.'
            ]);
        }

        if (!$table->hasColumn('facteurs_risque')) {
            $table->addColumn('facteurs_risque', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Facteurs de risque identifiés'
            ]);
        }

        if (!$table->hasColumn('observations_generales')) {
            $table->addColumn('observations_generales', Types::TEXT, [
                'notnull' => false,
                'comment' => 'Observations générales du dossier médical'
            ]);
        }

        if (!$table->hasColumn('date_derniere_mise_a_jour_dossier')) {
            $table->addColumn('date_derniere_mise_a_jour_dossier', Types::DATETIME_MUTABLE, [
                'notnull' => false,
                'comment' => 'Date de la dernière mise à jour du dossier médical'
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('patients');

        // Supprimer les colonnes ajoutées
        $columns = [
            'antecedents_familiaux_pere',
            'antecedents_familiaux_mere',
            'antecedents_familiaux_enfants',
            'antecedents_familiaux_epouse',
            'antecedents_familiaux_autres',
            'historique_vaccinations',
            'date_derniere_vaccination',
            'habitudes_vie',
            'facteurs_risque',
            'observations_generales',
            'date_derniere_mise_a_jour_dossier'
        ];

        foreach ($columns as $column) {
            if ($table->hasColumn($column)) {
                $table->dropColumn($column);
            }
        }
    }
}
