<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260103CreateMenusTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create menus and menu_roles tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE menus (
            id INT AUTO_INCREMENT NOT NULL,
            code VARCHAR(100) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            icone VARCHAR(255) DEFAULT NULL,
            route VARCHAR(255) DEFAULT NULL,
            module VARCHAR(100) DEFAULT NULL,
            parent_id INT DEFAULT NULL,
            ordre INT DEFAULT NULL,
            actif TINYINT(1) NOT NULL DEFAULT 1,
            date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE menu_roles (
            menu_id INT NOT NULL,
            role_id INT NOT NULL,
            PRIMARY KEY (menu_id, role_id),
            FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Insert menus for different roles
        $this->insertMenus();
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS menu_roles');
        $this->addSql('DROP TABLE IF EXISTS menus');
    }

    private function insertMenus(): void
    {
        // All menus with parent-child hierarchy
        $menus = [
            // 1. DASHBOARD
            ['code' => 'dashboard', 'nom' => 'Tableau de bord', 'icone' => 'dashboard', 'route' => '/dashboard', 'module' => 'dashboard', 'ordre' => 1],
            
            // 2. PATIENTS & DOSSIERS MEDICAUX
            ['code' => 'patients', 'nom' => 'Patients', 'icone' => 'people', 'route' => '/patients', 'module' => 'patients', 'ordre' => 2],
            ['code' => 'patients.list', 'nom' => 'Liste des patients', 'icone' => 'list', 'route' => '/patients/list', 'module' => 'patients', 'parent_id' => 'patients', 'ordre' => 1],
            ['code' => 'patients.create', 'nom' => 'Nouveau patient', 'icone' => 'add', 'route' => '/patients/create', 'module' => 'patients', 'parent_id' => 'patients', 'ordre' => 2],
            ['code' => 'patients.export', 'nom' => 'Exporter', 'icone' => 'download', 'route' => '/patients/export', 'module' => 'patients', 'parent_id' => 'patients', 'ordre' => 3],
            
            ['code' => 'dossiers_medicaux', 'nom' => 'Dossiers Médicaux', 'icone' => 'folder_open', 'route' => '/dossiers-medicaux', 'module' => 'dossiers_medicaux', 'ordre' => 3],
            ['code' => 'dossiers_medicaux.list', 'nom' => 'Liste des dossiers', 'icone' => 'list', 'route' => '/dossiers-medicaux/list', 'module' => 'dossiers_medicaux', 'parent_id' => 'dossiers_medicaux', 'ordre' => 1],
            ['code' => 'dossiers_medicaux.create', 'nom' => 'Nouveau dossier', 'icone' => 'add', 'route' => '/dossiers-medicaux/create', 'module' => 'dossiers_medicaux', 'parent_id' => 'dossiers_medicaux', 'ordre' => 2],
            
            // 3. ADMISSIONS & TRANSFERTS & SORTIES
            ['code' => 'admissions', 'nom' => 'Admissions', 'icone' => 'assignment', 'route' => '/admissions', 'module' => 'admissions', 'ordre' => 4],
            ['code' => 'admissions.list', 'nom' => 'Liste des admissions', 'icone' => 'list', 'route' => '/admissions/list', 'module' => 'admissions', 'parent_id' => 'admissions', 'ordre' => 1],
            ['code' => 'admissions.create', 'nom' => 'Nouvelle admission', 'icone' => 'add', 'route' => '/admissions/create', 'module' => 'admissions', 'parent_id' => 'admissions', 'ordre' => 2],
            ['code' => 'transferts', 'nom' => 'Transferts', 'icone' => 'compare_arrows', 'route' => '/transferts', 'module' => 'transferts', 'parent_id' => 'admissions', 'ordre' => 3],
            ['code' => 'sorties', 'nom' => 'Sorties', 'icone' => 'exit_to_app', 'route' => '/sorties', 'module' => 'sorties', 'parent_id' => 'admissions', 'ordre' => 4],
            
            // 4. CONSULTATIONS & RENDEZ-VOUS
            ['code' => 'consultations', 'nom' => 'Consultations', 'icone' => 'medical_services', 'route' => '/consultations', 'module' => 'consultations', 'ordre' => 5],
            ['code' => 'consultations.list', 'nom' => 'Liste des consultations', 'icone' => 'list', 'route' => '/consultations/list', 'module' => 'consultations', 'parent_id' => 'consultations', 'ordre' => 1],
            ['code' => 'consultations.create', 'nom' => 'Nouvelle consultation', 'icone' => 'add', 'route' => '/consultations/create', 'module' => 'consultations', 'parent_id' => 'consultations', 'ordre' => 2],
            
            ['code' => 'rendez_vous', 'nom' => 'Rendez-vous', 'icone' => 'event', 'route' => '/rendez-vous', 'module' => 'rendez_vous', 'ordre' => 6],
            ['code' => 'rendez_vous.calendar', 'nom' => 'Calendrier', 'icone' => 'calendar_today', 'route' => '/rendez-vous/calendar', 'module' => 'rendez_vous', 'parent_id' => 'rendez_vous', 'ordre' => 1],
            ['code' => 'rendez_vous.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/rendez-vous/list', 'module' => 'rendez_vous', 'parent_id' => 'rendez_vous', 'ordre' => 2],
            ['code' => 'rendez_vous.create', 'nom' => 'Nouveau rendez-vous', 'icone' => 'add', 'route' => '/rendez-vous/create', 'module' => 'rendez_vous', 'parent_id' => 'rendez_vous', 'ordre' => 3],
            ['code' => 'creneaux', 'nom' => 'Créneaux', 'icone' => 'schedule', 'route' => '/creneaux', 'module' => 'creneaux', 'parent_id' => 'rendez_vous', 'ordre' => 4],
            
            // 5. PRESCRIPTIONS & MEDICAMENTS
            ['code' => 'prescriptions', 'nom' => 'Prescriptions', 'icone' => 'prescription', 'route' => '/prescriptions', 'module' => 'prescriptions', 'ordre' => 7],
            ['code' => 'prescriptions.list', 'nom' => 'Liste des prescriptions', 'icone' => 'list', 'route' => '/prescriptions/list', 'module' => 'prescriptions', 'parent_id' => 'prescriptions', 'ordre' => 1],
            ['code' => 'prescriptions.create', 'nom' => 'Nouvelle prescription', 'icone' => 'add', 'route' => '/prescriptions/create', 'module' => 'prescriptions', 'parent_id' => 'prescriptions', 'ordre' => 2],
            ['code' => 'prescriptions.valider', 'nom' => 'Valider', 'icone' => 'check_circle', 'route' => '/prescriptions/valider', 'module' => 'prescriptions', 'parent_id' => 'prescriptions', 'ordre' => 3],
            
            ['code' => 'medicaments', 'nom' => 'Médicaments', 'icone' => 'medication', 'route' => '/medicaments', 'module' => 'medicaments', 'parent_id' => 'prescriptions', 'ordre' => 4],
            ['code' => 'administrations', 'nom' => 'Administrations', 'icone' => 'local_hospital', 'route' => '/administrations', 'module' => 'administrations', 'parent_id' => 'prescriptions', 'ordre' => 5],
            
            // 6. PHARMACIE
            ['code' => 'pharmacie', 'nom' => 'Pharmacie', 'icone' => 'local_pharmacy', 'route' => '/pharmacie', 'module' => 'pharmacie', 'ordre' => 8],
            ['code' => 'stocks_pharmacie', 'nom' => 'Stocks', 'icone' => 'inventory', 'route' => '/pharmacie/stocks', 'module' => 'stocks_pharmacie', 'parent_id' => 'pharmacie', 'ordre' => 1],
            ['code' => 'stocks_pharmacie.list', 'nom' => 'Consulter stocks', 'icone' => 'list', 'route' => '/pharmacie/stocks/list', 'module' => 'stocks_pharmacie', 'parent_id' => 'stocks_pharmacie', 'ordre' => 1],
            ['code' => 'stocks_pharmacie.create', 'nom' => 'Ajouter stock', 'icone' => 'add', 'route' => '/pharmacie/stocks/create', 'module' => 'stocks_pharmacie', 'parent_id' => 'stocks_pharmacie', 'ordre' => 2],
            ['code' => 'distributions_pharmacie', 'nom' => 'Distributions', 'icone' => 'local_shipping', 'route' => '/pharmacie/distributions', 'module' => 'distributions_pharmacie', 'parent_id' => 'pharmacie', 'ordre' => 2],
            ['code' => 'distributions_pharmacie.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/pharmacie/distributions/list', 'module' => 'distributions_pharmacie', 'parent_id' => 'distributions_pharmacie', 'ordre' => 1],
            ['code' => 'distributions_pharmacie.create', 'nom' => 'Nouvelle distribution', 'icone' => 'add', 'route' => '/pharmacie/distributions/create', 'module' => 'distributions_pharmacie', 'parent_id' => 'distributions_pharmacie', 'ordre' => 2],
            
            // 7. LABORATOIRE
            ['code' => 'laboratoire', 'nom' => 'Laboratoire', 'icone' => 'science', 'route' => '/laboratoire', 'module' => 'laboratoire', 'ordre' => 9],
            ['code' => 'ordonnances_labo', 'nom' => 'Ordonnances', 'icone' => 'description', 'route' => '/laboratoire/ordonnances', 'module' => 'ordonnances_labo', 'parent_id' => 'laboratoire', 'ordre' => 1],
            ['code' => 'ordonnances_labo.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/laboratoire/ordonnances/list', 'module' => 'ordonnances_labo', 'parent_id' => 'ordonnances_labo', 'ordre' => 1],
            ['code' => 'ordonnances_labo.create', 'nom' => 'Nouvelle ordonnance', 'icone' => 'add', 'route' => '/laboratoire/ordonnances/create', 'module' => 'ordonnances_labo', 'parent_id' => 'ordonnances_labo', 'ordre' => 2],
            ['code' => 'prelevements', 'nom' => 'Prélèvements', 'icone' => 'bloodtype', 'route' => '/laboratoire/prelevements', 'module' => 'prelevements', 'parent_id' => 'laboratoire', 'ordre' => 2],
            ['code' => 'prelevements.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/laboratoire/prelevements/list', 'module' => 'prelevements', 'parent_id' => 'prelevements', 'ordre' => 1],
            ['code' => 'prelevements.create', 'nom' => 'Nouveau prélèvement', 'icone' => 'add', 'route' => '/laboratoire/prelevements/create', 'module' => 'prelevements', 'parent_id' => 'prelevements', 'ordre' => 2],
            ['code' => 'resultats_labo', 'nom' => 'Résultats', 'icone' => 'assessment', 'route' => '/laboratoire/resultats', 'module' => 'resultats_labo', 'parent_id' => 'laboratoire', 'ordre' => 3],
            ['code' => 'resultats_labo.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/laboratoire/resultats/list', 'module' => 'resultats_labo', 'parent_id' => 'resultats_labo', 'ordre' => 1],
            ['code' => 'resultats_labo.create', 'nom' => 'Nouveau résultat', 'icone' => 'add', 'route' => '/laboratoire/resultats/create', 'module' => 'resultats_labo', 'parent_id' => 'resultats_labo', 'ordre' => 2],
            
            // 8. IMAGERIE
            ['code' => 'imagerie', 'nom' => 'Imagerie', 'icone' => 'image', 'route' => '/imagerie', 'module' => 'imagerie', 'ordre' => 10],
            ['code' => 'ordonnances_imagerie', 'nom' => 'Ordonnances', 'icone' => 'description', 'route' => '/imagerie/ordonnances', 'module' => 'ordonnances_imagerie', 'parent_id' => 'imagerie', 'ordre' => 1],
            ['code' => 'ordonnances_imagerie.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/imagerie/ordonnances/list', 'module' => 'ordonnances_imagerie', 'parent_id' => 'ordonnances_imagerie', 'ordre' => 1],
            ['code' => 'ordonnances_imagerie.create', 'nom' => 'Nouvelle ordonnance', 'icone' => 'add', 'route' => '/imagerie/ordonnances/create', 'module' => 'ordonnances_imagerie', 'parent_id' => 'ordonnances_imagerie', 'ordre' => 2],
            ['code' => 'examens_imagerie', 'nom' => 'Examens', 'icone' => 'image_search', 'route' => '/imagerie/examens', 'module' => 'examens_imagerie', 'parent_id' => 'imagerie', 'ordre' => 2],
            ['code' => 'examens_imagerie.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/imagerie/examens/list', 'module' => 'examens_imagerie', 'parent_id' => 'examens_imagerie', 'ordre' => 1],
            ['code' => 'examens_imagerie.create', 'nom' => 'Nouvel examen', 'icone' => 'add', 'route' => '/imagerie/examens/create', 'module' => 'examens_imagerie', 'parent_id' => 'examens_imagerie', 'ordre' => 2],
            ['code' => 'rapports_radiologiques', 'nom' => 'Rapports', 'icone' => 'description', 'route' => '/imagerie/rapports', 'module' => 'rapports_radiologiques', 'parent_id' => 'imagerie', 'ordre' => 3],
            ['code' => 'rapports_radiologiques.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/imagerie/rapports/list', 'module' => 'rapports_radiologiques', 'parent_id' => 'rapports_radiologiques', 'ordre' => 1],
            ['code' => 'rapports_radiologiques.create', 'nom' => 'Nouveau rapport', 'icone' => 'add', 'route' => '/imagerie/rapports/create', 'module' => 'rapports_radiologiques', 'parent_id' => 'rapports_radiologiques', 'ordre' => 2],
            
            // 9. CHIRURGIE
            ['code' => 'chirurgie', 'nom' => 'Chirurgie', 'icone' => 'local_hospital', 'route' => '/chirurgie', 'module' => 'chirurgie', 'ordre' => 11],
            ['code' => 'demandes_interventions', 'nom' => 'Demandes', 'icone' => 'request_page', 'route' => '/chirurgie/demandes', 'module' => 'demandes_interventions', 'parent_id' => 'chirurgie', 'ordre' => 1],
            ['code' => 'demandes_interventions.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/chirurgie/demandes/list', 'module' => 'demandes_interventions', 'parent_id' => 'demandes_interventions', 'ordre' => 1],
            ['code' => 'demandes_interventions.create', 'nom' => 'Nouvelle demande', 'icone' => 'add', 'route' => '/chirurgie/demandes/create', 'module' => 'demandes_interventions', 'parent_id' => 'demandes_interventions', 'ordre' => 2],
            ['code' => 'planning_operatoire', 'nom' => 'Planning', 'icone' => 'calendar_month', 'route' => '/chirurgie/planning', 'module' => 'planning_operatoire', 'parent_id' => 'chirurgie', 'ordre' => 2],
            ['code' => 'planning_operatoire.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/chirurgie/planning/list', 'module' => 'planning_operatoire', 'parent_id' => 'planning_operatoire', 'ordre' => 1],
            ['code' => 'planning_operatoire.create', 'nom' => 'Nouveau planning', 'icone' => 'add', 'route' => '/chirurgie/planning/create', 'module' => 'planning_operatoire', 'parent_id' => 'planning_operatoire', 'ordre' => 2],
            ['code' => 'rapports_operatoires', 'nom' => 'Rapports', 'icone' => 'description', 'route' => '/chirurgie/rapports', 'module' => 'rapports_operatoires', 'parent_id' => 'chirurgie', 'ordre' => 3],
            ['code' => 'rapports_operatoires.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/chirurgie/rapports/list', 'module' => 'rapports_operatoires', 'parent_id' => 'rapports_operatoires', 'ordre' => 1],
            ['code' => 'rapports_operatoires.create', 'nom' => 'Nouveau rapport', 'icone' => 'add', 'route' => '/chirurgie/rapports/create', 'module' => 'rapports_operatoires', 'parent_id' => 'rapports_operatoires', 'ordre' => 2],
            
            // 10. URGENCES & TRIAGE
            ['code' => 'urgences', 'nom' => 'Urgences', 'icone' => 'emergency', 'route' => '/urgences', 'module' => 'urgences', 'ordre' => 12],
            ['code' => 'triages', 'nom' => 'Triages', 'icone' => 'priority_high', 'route' => '/urgences/triages', 'module' => 'triages', 'parent_id' => 'urgences', 'ordre' => 1],
            ['code' => 'triages.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/urgences/triages/list', 'module' => 'triages', 'parent_id' => 'triages', 'ordre' => 1],
            ['code' => 'triages.create', 'nom' => 'Nouveau triage', 'icone' => 'add', 'route' => '/urgences/triages/create', 'module' => 'triages', 'parent_id' => 'triages', 'ordre' => 2],
            
            // 11. FACTURATION
            ['code' => 'facturation', 'nom' => 'Facturation', 'icone' => 'receipt', 'route' => '/facturation', 'module' => 'facturation', 'ordre' => 13],
            ['code' => 'factures', 'nom' => 'Factures', 'icone' => 'description', 'route' => '/facturation/factures', 'module' => 'factures', 'parent_id' => 'facturation', 'ordre' => 1],
            ['code' => 'factures.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/facturation/factures/list', 'module' => 'factures', 'parent_id' => 'factures', 'ordre' => 1],
            ['code' => 'factures.create', 'nom' => 'Nouvelle facture', 'icone' => 'add', 'route' => '/facturation/factures/create', 'module' => 'factures', 'parent_id' => 'factures', 'ordre' => 2],
            ['code' => 'paiements', 'nom' => 'Paiements', 'icone' => 'payment', 'route' => '/facturation/paiements', 'module' => 'paiements', 'parent_id' => 'facturation', 'ordre' => 2],
            ['code' => 'paiements.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/facturation/paiements/list', 'module' => 'paiements', 'parent_id' => 'paiements', 'ordre' => 1],
            ['code' => 'paiements.create', 'nom' => 'Nouveau paiement', 'icone' => 'add', 'route' => '/facturation/paiements/create', 'module' => 'paiements', 'parent_id' => 'paiements', 'ordre' => 2],
            ['code' => 'reclamations_assurance', 'nom' => 'Réclamations', 'icone' => 'warning', 'route' => '/facturation/reclamations', 'module' => 'reclamations_assurance', 'parent_id' => 'facturation', 'ordre' => 3],
            
            // 12. RESSOURCES HUMAINES
            ['code' => 'rh', 'nom' => 'Ressources Humaines', 'icone' => 'group', 'route' => '/rh', 'module' => 'rh', 'ordre' => 14],
            ['code' => 'utilisateurs', 'nom' => 'Utilisateurs', 'icone' => 'person', 'route' => '/rh/utilisateurs', 'module' => 'utilisateurs', 'parent_id' => 'rh', 'ordre' => 1],
            ['code' => 'utilisateurs.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/rh/utilisateurs/list', 'module' => 'utilisateurs', 'parent_id' => 'utilisateurs', 'ordre' => 1],
            ['code' => 'utilisateurs.create', 'nom' => 'Nouvel utilisateur', 'icone' => 'add', 'route' => '/rh/utilisateurs/create', 'module' => 'utilisateurs', 'parent_id' => 'utilisateurs', 'ordre' => 2],
            ['code' => 'formations', 'nom' => 'Formations', 'icone' => 'school', 'route' => '/rh/formations', 'module' => 'formations', 'parent_id' => 'rh', 'ordre' => 2],
            ['code' => 'formations.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/rh/formations/list', 'module' => 'formations', 'parent_id' => 'formations', 'ordre' => 1],
            ['code' => 'formations.create', 'nom' => 'Nouvelle formation', 'icone' => 'add', 'route' => '/rh/formations/create', 'module' => 'formations', 'parent_id' => 'formations', 'ordre' => 2],
            ['code' => 'conges', 'nom' => 'Congés', 'icone' => 'beach_access', 'route' => '/rh/conges', 'module' => 'conges', 'parent_id' => 'rh', 'ordre' => 3],
            ['code' => 'conges.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/rh/conges/list', 'module' => 'conges', 'parent_id' => 'conges', 'ordre' => 1],
            ['code' => 'conges.create', 'nom' => 'Demander un congé', 'icone' => 'add', 'route' => '/rh/conges/create', 'module' => 'conges', 'parent_id' => 'conges', 'ordre' => 2],
            ['code' => 'paie', 'nom' => 'Paie', 'icone' => 'attach_money', 'route' => '/rh/paie', 'module' => 'paie', 'parent_id' => 'rh', 'ordre' => 4],
            ['code' => 'paie.list', 'nom' => 'Bulletins', 'icone' => 'list', 'route' => '/rh/paie/list', 'module' => 'paie', 'parent_id' => 'paie', 'ordre' => 1],
            ['code' => 'paie.create', 'nom' => 'Nouveau bulletin', 'icone' => 'add', 'route' => '/rh/paie/create', 'module' => 'paie', 'parent_id' => 'paie', 'ordre' => 2],
            
            // 13. ADMINISTRATION
            ['code' => 'administration', 'nom' => 'Administration', 'icone' => 'admin_panel_settings', 'route' => '/administration', 'module' => 'administration', 'ordre' => 15],
            ['code' => 'hopitaux', 'nom' => 'Hôpitaux', 'icone' => 'business', 'route' => '/administration/hopitaux', 'module' => 'hopitaux', 'parent_id' => 'administration', 'ordre' => 1],
            ['code' => 'hopitaux.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/administration/hopitaux/list', 'module' => 'hopitaux', 'parent_id' => 'hopitaux', 'ordre' => 1],
            ['code' => 'hopitaux.create', 'nom' => 'Nouvel hôpital', 'icone' => 'add', 'route' => '/administration/hopitaux/create', 'module' => 'hopitaux', 'parent_id' => 'hopitaux', 'ordre' => 2],
            ['code' => 'services', 'nom' => 'Services', 'icone' => 'domain', 'route' => '/administration/services', 'module' => 'services', 'parent_id' => 'administration', 'ordre' => 2],
            ['code' => 'services.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/administration/services/list', 'module' => 'services', 'parent_id' => 'services', 'ordre' => 1],
            ['code' => 'services.create', 'nom' => 'Nouveau service', 'icone' => 'add', 'route' => '/administration/services/create', 'module' => 'services', 'parent_id' => 'services', 'ordre' => 2],
            ['code' => 'lits', 'nom' => 'Lits', 'icone' => 'bed', 'route' => '/administration/lits', 'module' => 'lits', 'parent_id' => 'administration', 'ordre' => 3],
            ['code' => 'lits.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/administration/lits/list', 'module' => 'lits', 'parent_id' => 'lits', 'ordre' => 1],
            ['code' => 'lits.create', 'nom' => 'Nouveau lit', 'icone' => 'add', 'route' => '/administration/lits/create', 'module' => 'lits', 'parent_id' => 'lits', 'ordre' => 2],
            ['code' => 'equipements', 'nom' => 'Équipements', 'icone' => 'devices', 'route' => '/administration/equipements', 'module' => 'equipements', 'parent_id' => 'administration', 'ordre' => 4],
            ['code' => 'equipements.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/administration/equipements/list', 'module' => 'equipements', 'parent_id' => 'equipements', 'ordre' => 1],
            ['code' => 'equipements.create', 'nom' => 'Nouvel équipement', 'icone' => 'add', 'route' => '/administration/equipements/create', 'module' => 'equipements', 'parent_id' => 'equipements', 'ordre' => 2],
            ['code' => 'maintenance', 'nom' => 'Maintenance', 'icone' => 'build', 'route' => '/administration/maintenance', 'module' => 'maintenance', 'parent_id' => 'administration', 'ordre' => 5],
            ['code' => 'maintenance.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/administration/maintenance/list', 'module' => 'maintenance', 'parent_id' => 'maintenance', 'ordre' => 1],
            ['code' => 'maintenance.create', 'nom' => 'Nouvelle intervention', 'icone' => 'add', 'route' => '/administration/maintenance/create', 'module' => 'maintenance', 'parent_id' => 'maintenance', 'ordre' => 2],
            ['code' => 'fournisseurs', 'nom' => 'Fournisseurs', 'icone' => 'local_shipping', 'route' => '/administration/fournisseurs', 'module' => 'fournisseurs', 'parent_id' => 'administration', 'ordre' => 6],
            ['code' => 'fournisseurs.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/administration/fournisseurs/list', 'module' => 'fournisseurs', 'parent_id' => 'fournisseurs', 'ordre' => 1],
            ['code' => 'fournisseurs.create', 'nom' => 'Nouveau fournisseur', 'icone' => 'add', 'route' => '/administration/fournisseurs/create', 'module' => 'fournisseurs', 'parent_id' => 'fournisseurs', 'ordre' => 2],
            ['code' => 'bons_commande', 'nom' => 'Bons de commande', 'icone' => 'shopping_cart', 'route' => '/administration/bons-commande', 'module' => 'bons_commande', 'parent_id' => 'administration', 'ordre' => 7],
            ['code' => 'bons_commande.list', 'nom' => 'Liste', 'icone' => 'list', 'route' => '/administration/bons-commande/list', 'module' => 'bons_commande', 'parent_id' => 'bons_commande', 'ordre' => 1],
            ['code' => 'bons_commande.create', 'nom' => 'Nouveau bon', 'icone' => 'add', 'route' => '/administration/bons-commande/create', 'module' => 'bons_commande', 'parent_id' => 'bons_commande', 'ordre' => 2],
            
            // 14. RAPPORTS & QUALITÉ
            ['code' => 'rapports', 'nom' => 'Rapports', 'icone' => 'bar_chart', 'route' => '/rapports', 'module' => 'rapports', 'ordre' => 16],
            ['code' => 'rapports.list', 'nom' => 'Mes rapports', 'icone' => 'list', 'route' => '/rapports/list', 'module' => 'rapports', 'parent_id' => 'rapports', 'ordre' => 1],
            ['code' => 'rapports.create', 'nom' => 'Créer un rapport', 'icone' => 'add', 'route' => '/rapports/create', 'module' => 'rapports', 'parent_id' => 'rapports', 'ordre' => 2],
            ['code' => 'indicateurs', 'nom' => 'Indicateurs', 'icone' => 'trending_up', 'route' => '/rapports/indicateurs', 'module' => 'indicateurs', 'parent_id' => 'rapports', 'ordre' => 3],
            ['code' => 'plaintes', 'nom' => 'Plaintes & Incidents', 'icone' => 'warning', 'route' => '/rapports/plaintes', 'module' => 'plaintes', 'parent_id' => 'rapports', 'ordre' => 4],
            
            // 15. PARAMÈTRES & SÉCURITÉ
            ['code' => 'parametres', 'nom' => 'Paramètres', 'icone' => 'settings', 'route' => '/parametres', 'module' => 'parametres', 'ordre' => 17],
            ['code' => 'roles', 'nom' => 'Rôles & Permissions', 'icone' => 'security', 'route' => '/parametres/roles', 'module' => 'roles', 'parent_id' => 'parametres', 'ordre' => 1],
            ['code' => 'roles.list', 'nom' => 'Liste des rôles', 'icone' => 'list', 'route' => '/parametres/roles/list', 'module' => 'roles', 'parent_id' => 'roles', 'ordre' => 1],
            ['code' => 'roles.create', 'nom' => 'Nouveau rôle', 'icone' => 'add', 'route' => '/parametres/roles/create', 'module' => 'roles', 'parent_id' => 'roles', 'ordre' => 2],
            ['code' => 'logs', 'nom' => 'Logs & Audit', 'icone' => 'history', 'route' => '/parametres/logs', 'module' => 'logs', 'parent_id' => 'parametres', 'ordre' => 2],
            ['code' => 'logs.audit', 'nom' => 'Audit', 'icone' => 'list', 'route' => '/parametres/logs/audit', 'module' => 'logs', 'parent_id' => 'logs', 'ordre' => 1],
            ['code' => 'sauvegardes', 'nom' => 'Sauvegardes', 'icone' => 'backup', 'route' => '/parametres/sauvegardes', 'module' => 'sauvegardes', 'parent_id' => 'parametres', 'ordre' => 3],
            ['code' => 'archives', 'nom' => 'Archives', 'icone' => 'archive', 'route' => '/parametres/archives', 'module' => 'archives', 'parent_id' => 'parametres', 'ordre' => 4],
        ];

        foreach ($menus as $index => $menu) {
            $parentId = isset($menu['parent_id']) ? "'{$menu['parent_id']}'" : 'NULL';
            $ordre = $menu['ordre'] ?? ($index + 1);
            $description = isset($menu['description']) ? "'{$menu['description']}'" : 'NULL';
            
            $this->addSql("INSERT INTO menus (code, nom, icone, route, module, parent_id, ordre, actif, date_creation) 
                VALUES ('{$menu['code']}', '{$menu['nom']}', '{$menu['icone']}', '{$menu['route']}', '{$menu['module']}', {$parentId}, {$ordre}, 1, NOW())");
        }

        // Assign menus to roles
        $this->assignMenusToRoles();
    }

    private function assignMenusToRoles(): void
    {
        // Admin has access to all menus
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r WHERE r.code = 'ROLE_ADMIN'");

        // Directeur
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_DIRECTEUR' 
            AND m.code IN ('dashboard', 'patients', 'admissions', 'consultations', 'facturation', 'rh', 'rapports', 'parametres')");

        // Médecin
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_MEDECIN' 
            AND m.code IN ('dashboard', 'patients', 'dossiers_medicaux', 'admissions', 'consultations', 'rendez_vous', 'prescriptions', 'laboratoire', 'imagerie', 'chirurgie')");

        // Infirmier
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_INFIRMIER' 
            AND m.code IN ('dashboard', 'patients', 'admissions', 'consultations', 'prescriptions')");

        // Pharmacien
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_PHARMACIEN' 
            AND m.code IN ('dashboard', 'pharmacie', 'prescriptions')");

        // Laborantin
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_LABORANTIN' 
            AND m.code IN ('dashboard', 'laboratoire')");

        // Radiologue
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_RADIOLOGUE' 
            AND m.code IN ('dashboard', 'imagerie')");

        // Comptable
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_COMPTABLE' 
            AND m.code IN ('dashboard', 'facturation', 'rapports')");

        // RH
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_RH' 
            AND m.code IN ('dashboard', 'rh')");

        // Maintenance
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_MAINTENANCE' 
            AND m.code IN ('dashboard', 'administration')");

        // Réceptionniste
        $this->addSql("INSERT INTO menu_roles (menu_id, role_id) 
            SELECT m.id, r.id FROM menus m, roles r 
            WHERE r.code = 'ROLE_RECEPTIONNISTE' 
            AND m.code IN ('dashboard', 'patients', 'rendez_vous')");
    }
}
