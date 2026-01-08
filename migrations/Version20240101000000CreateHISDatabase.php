<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration complète pour la base de données du Système de Gestion Hospitalière (HIS)
 * Couvre tous les modules : configuration, clinique, pharmacie, labo, imagerie, finance, RH, etc.
 * 
 * VERSION AMÉLIORÉE AVEC :
 * - Paramètres de configuration enrichis (logo, icône, taux de paiement, etc.)
 * - Profils utilisateurs détaillés
 * - Tables supplémentaires pour les modes de paiement, devises, etc.
 */
final class Version20240101000000CreateHISDatabase extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création complète de la base de données HIS avec tous les modules - Version améliorée';
    }

    public function up(Schema $schema): void
    {
        // ============================================================================
        // SECTION 0 : TABLES DE RÉFÉRENCE GLOBALES
        // ============================================================================

        // Table des devises
        $this->addSql('CREATE TABLE devises (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(3) NOT NULL UNIQUE,
            nom VARCHAR(100) NOT NULL,
            symbole VARCHAR(10),
            taux_change DECIMAL(10,4) DEFAULT 1.0000,
            devise_reference BOOLEAN DEFAULT false,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des modes de paiement
        $this->addSql('CREATE TABLE modes_paiement (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            type_paiement VARCHAR(50) COMMENT "Espèces, Chèque, Carte, Virement, Assurance, Autre",
            frais_transaction DECIMAL(5,2) COMMENT "En pourcentage",
            delai_encaissement INT COMMENT "En jours",
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des taux de TVA
        $this->addSql('CREATE TABLE taux_tva (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            nom VARCHAR(100) NOT NULL,
            pourcentage DECIMAL(5,2) NOT NULL,
            date_debut DATE NOT NULL,
            date_fin DATE,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 1 : CONFIGURATION GÉNÉRALE & SÉCURITÉ
        // ============================================================================

        // Table des hôpitaux/sites
        $this->addSql('CREATE TABLE hopitaux (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            nom VARCHAR(255) NOT NULL,
            adresse VARCHAR(255),
            ville VARCHAR(100),
            code_postal VARCHAR(10),
            telephone VARCHAR(20),
            email VARCHAR(255),
            directeur_id INT,
            type_hopital VARCHAR(50) COMMENT "Public, Privé, Clinique",
            nombre_lits INT,
            logo_url VARCHAR(500),
            icone_url VARCHAR(500),
            couleur_primaire VARCHAR(7),
            couleur_secondaire VARCHAR(7),
            site_web VARCHAR(255),
            numero_siret VARCHAR(50),
            numero_tva VARCHAR(50),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_actif (actif)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des services/départements
        $this->addSql('CREATE TABLE services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            code VARCHAR(50) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            description TEXT,
            type_service VARCHAR(100) COMMENT "Urgences, Chirurgie, Médecine, Pédiatrie, etc.",
            chef_service_id INT,
            nombre_lits INT,
            localisation VARCHAR(100),
            telephone VARCHAR(20),
            email VARCHAR(255),
            logo_service VARCHAR(500),
            couleur_service VARCHAR(7),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_code_hopital (hopital_id, code),
            INDEX idx_hopital (hopital_id),
            INDEX idx_type (type_service),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des spécialités médicales
        $this->addSql('CREATE TABLE specialites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            nom VARCHAR(255) NOT NULL,
            description TEXT,
            code_snomed VARCHAR(50),
            icone VARCHAR(500),
            couleur VARCHAR(7),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des profils utilisateurs
        $this->addSql('CREATE TABLE profils_utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            type_profil VARCHAR(50) COMMENT "Médecin, Infirmier, Admin, Pharmacien, Labo, Radiologue, RH, Finance, etc.",
            icone VARCHAR(500),
            couleur VARCHAR(7),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des rôles utilisateurs
        $this->addSql('CREATE TABLE roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(100) NOT NULL UNIQUE,
            nom VARCHAR(255) NOT NULL,
            description TEXT,
            niveau_acces INT COMMENT "0=Utilisateur, 1=Superviseur, 2=Admin, 3=Super-Admin",
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des permissions
        $this->addSql('CREATE TABLE permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(100) NOT NULL UNIQUE,
            nom VARCHAR(255) NOT NULL,
            description TEXT,
            module VARCHAR(100),
            action VARCHAR(100),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_module (module)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table de liaison rôles-permissions
        $this->addSql('CREATE TABLE role_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_role_permission (role_id, permission_id),
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des utilisateurs (AMÉLIORÉE)
        $this->addSql('CREATE TABLE utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            profil_id INT NOT NULL,
            nom VARCHAR(255) NOT NULL,
            prenom VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            telephone VARCHAR(20),
            login VARCHAR(100) NOT NULL UNIQUE,
            mot_de_passe VARCHAR(255) NOT NULL,
            role_id INT NOT NULL,
            specialite_id INT,
            numero_licence VARCHAR(100),
            numero_ordre VARCHAR(100),
            date_embauche DATE,
            photo_profil VARCHAR(500),
            signature_numerique VARCHAR(500),
            bio TEXT,
            adresse VARCHAR(255),
            ville VARCHAR(100),
            code_postal VARCHAR(10),
            date_naissance DATE,
            sexe CHAR(1),
            nationalite VARCHAR(100),
            numero_identite VARCHAR(50),
            type_identite VARCHAR(50),
            telephone_urgence VARCHAR(20),
            contact_urgence_nom VARCHAR(255),
            actif BOOLEAN DEFAULT true,
            compte_verrouille BOOLEAN DEFAULT false,
            nombre_tentatives_connexion INT DEFAULT 0,
            date_dernier_changement_mdp DATETIME,
            mdp_temporaire BOOLEAN DEFAULT false,
            authentification_2fa BOOLEAN DEFAULT false,
            derniere_connexion DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_hopital (hopital_id),
            INDEX idx_email (email),
            INDEX idx_login (login),
            INDEX idx_role (role_id),
            INDEX idx_profil (profil_id),
            INDEX idx_actif (actif),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (role_id) REFERENCES roles(id),
            FOREIGN KEY (profil_id) REFERENCES profils_utilisateurs(id),
            FOREIGN KEY (specialite_id) REFERENCES specialites(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des affectations utilisateurs aux services
        $this->addSql('CREATE TABLE affectations_utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            service_id INT NOT NULL,
            date_debut DATE NOT NULL,
            date_fin DATE,
            pourcentage_temps DECIMAL(5,2) COMMENT "Pourcentage de temps affecté",
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_service (utilisateur_id, service_id),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des logs d'audit
        $this->addSql('CREATE TABLE logs_audit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT,
            hopital_id INT,
            module VARCHAR(100),
            action VARCHAR(100),
            entite VARCHAR(100),
            entite_id INT,
            ancienne_valeur LONGTEXT,
            nouvelle_valeur LONGTEXT,
            adresse_ip VARCHAR(45),
            user_agent TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_utilisateur (utilisateur_id),
            INDEX idx_hopital (hopital_id),
            INDEX idx_module (module),
            INDEX idx_date (date_creation),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des paramètres de configuration (AMÉLIORÉE)
        $this->addSql('CREATE TABLE parametres_configuration (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT,
            cle VARCHAR(255) NOT NULL,
            valeur LONGTEXT,
            type VARCHAR(50) COMMENT "string, int, boolean, json, decimal",
            description TEXT,
            categorie VARCHAR(100) COMMENT "Général, Affichage, Paiement, Email, SMS, Sécurité, etc.",
            logo_url VARCHAR(500),
            icone_url VARCHAR(500),
            couleur_primaire VARCHAR(7),
            couleur_secondaire VARCHAR(7),
            taux_paiement_defaut DECIMAL(5,2),
            taux_tva_defaut DECIMAL(5,2),
            devise_defaut_id INT,
            mode_paiement_defaut_id INT,
            email_expediteur VARCHAR(255),
            email_support VARCHAR(255),
            telephone_support VARCHAR(20),
            url_logo_email VARCHAR(500),
            signature_email TEXT,
            actif BOOLEAN DEFAULT true,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_cle (hopital_id, cle),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (devise_defaut_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (mode_paiement_defaut_id) REFERENCES modes_paiement(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des conventions d\'assurance
        $this->addSql('CREATE TABLE conventions_assurance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            code VARCHAR(50) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            type_couverture VARCHAR(100) COMMENT "Assurance, Mutuelle, Privé",
            pourcentage_couverture DECIMAL(5,2),
            contact_personne VARCHAR(255),
            telephone VARCHAR(20),
            email VARCHAR(255),
            adresse TEXT,
            logo_url VARCHAR(500),
            numero_convention VARCHAR(100),
            taux_commission DECIMAL(5,2),
            actif BOOLEAN DEFAULT true,
            date_debut DATE,
            date_fin DATE,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 2 : GESTION DES PATIENTS
        // ============================================================================

        // Table des patients
        $this->addSql('CREATE TABLE patients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            numero_dossier VARCHAR(50) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            prenom VARCHAR(255) NOT NULL,
            date_naissance DATE NOT NULL,
            sexe CHAR(1) COMMENT "M ou F",
            numero_identite VARCHAR(50),
            type_identite VARCHAR(50) COMMENT "CNI, Passeport, Permis",
            adresse VARCHAR(255),
            ville VARCHAR(100),
            code_postal VARCHAR(10),
            telephone VARCHAR(20),
            email VARCHAR(255),
            contact_urgence_nom VARCHAR(255),
            contact_urgence_telephone VARCHAR(20),
            contact_urgence_lien VARCHAR(100),
            groupe_sanguin VARCHAR(5),
            allergies TEXT,
            antecedents_medicaux TEXT,
            antecedents_chirurgicaux TEXT,
            medicaments_actuels TEXT,
            statut_civil VARCHAR(50) COMMENT "Célibataire, Marié, Divorcé, Veuf",
            profession VARCHAR(100),
            nationalite VARCHAR(100),
            langue_preference VARCHAR(50),
            photo_patient VARCHAR(500),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_dossier (hopital_id, numero_dossier),
            INDEX idx_hopital (hopital_id),
            INDEX idx_nom_prenom (nom, prenom),
            INDEX idx_numero_identite (numero_identite),
            INDEX idx_date_naissance (date_naissance),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des assurances patients
        $this->addSql('CREATE TABLE assurances_patients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            convention_id INT NOT NULL,
            numero_police VARCHAR(100),
            date_debut DATE,
            date_fin DATE,
            beneficiaire_nom VARCHAR(255),
            beneficiaire_lien VARCHAR(100),
            taux_couverture DECIMAL(5,2),
            montant_franchise DECIMAL(10,2),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (convention_id) REFERENCES conventions_assurance(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des dossiers médicaux électroniques (DME)
        $this->addSql('CREATE TABLE dossiers_medicaux (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_dme VARCHAR(50) NOT NULL,
            date_ouverture DATE NOT NULL,
            date_fermeture DATE,
            medecin_referent_id INT,
            statut VARCHAR(50) COMMENT "Actif, Archivé, Suspendu",
            notes_generales LONGTEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_dme (hopital_id, numero_dme),
            INDEX idx_patient (patient_id),
            INDEX idx_hopital (hopital_id),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (medecin_referent_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 3 : GESTION DES RENDEZ-VOUS
        // ============================================================================

        // Table des créneaux de consultation
        $this->addSql('CREATE TABLE creneaux_consultation (
            id INT AUTO_INCREMENT PRIMARY KEY,
            medecin_id INT NOT NULL,
            service_id INT NOT NULL,
            date_consultation DATE NOT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME NOT NULL,
            nombre_places INT DEFAULT 1,
            places_disponibles INT DEFAULT 1,
            type_consultation VARCHAR(50) COMMENT "Consultation, Suivi, Urgence",
            lieu VARCHAR(255),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_medecin (medecin_id),
            INDEX idx_service (service_id),
            INDEX idx_date (date_consultation),
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des rendez-vous
        $this->addSql('CREATE TABLE rendez_vous (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            creneau_id INT NOT NULL,
            medecin_id INT NOT NULL,
            service_id INT NOT NULL,
            hopital_id INT NOT NULL,
            date_rendez_vous DATE NOT NULL,
            heure_rendez_vous TIME NOT NULL,
            motif_consultation TEXT,
            statut VARCHAR(50) COMMENT "Programmé, Confirmé, Réalisé, Annulé, No-show",
            type_consultation VARCHAR(50),
            notes_pre_consultation TEXT,
            date_confirmation DATETIME,
            date_realisation DATETIME,
            date_annulation DATETIME,
            raison_annulation TEXT,
            rappel_sms_envoye BOOLEAN DEFAULT false,
            rappel_email_envoye BOOLEAN DEFAULT false,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_patient (patient_id),
            INDEX idx_medecin (medecin_id),
            INDEX idx_date (date_rendez_vous),
            INDEX idx_statut (statut),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (creneau_id) REFERENCES creneaux_consultation(id),
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (service_id) REFERENCES services(id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 4 : GESTION DES ADMISSIONS & LITS
        // ============================================================================

        // Table des lits
        $this->addSql('CREATE TABLE lits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_lit VARCHAR(50) NOT NULL,
            type_lit VARCHAR(50) COMMENT "Standard, Semi-privé, Privé, Soins intensifs",
            etage INT,
            chambre VARCHAR(50),
            statut VARCHAR(50) COMMENT "Disponible, Occupé, Maintenance, Fermé",
            date_derniere_maintenance DATE,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_lit (hopital_id, numero_lit),
            INDEX idx_service (service_id),
            INDEX idx_hopital (hopital_id),
            INDEX idx_statut (statut),
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des tarifs par type de lit
        $this->addSql('CREATE TABLE tarifs_lits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            type_lit VARCHAR(50) NOT NULL,
            montant_journalier DECIMAL(10,2) NOT NULL,
            devise_id INT,
            taux_tva_id INT,
            date_debut DATE NOT NULL,
            date_fin DATE,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_type_date (hopital_id, type_lit, date_debut),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (taux_tva_id) REFERENCES taux_tva(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des admissions
        $this->addSql('CREATE TABLE admissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            service_id INT NOT NULL,
            lit_id INT,
            medecin_id INT NOT NULL,
            numero_admission VARCHAR(50) NOT NULL,
            date_admission DATETIME NOT NULL,
            date_sortie DATETIME,
            type_admission VARCHAR(50) COMMENT "Programmée, Urgence, Transfert",
            motif_admission TEXT,
            diagnostic_principal VARCHAR(255),
            diagnostic_secondaire TEXT,
            medecin_sortie_id INT,
            raison_sortie VARCHAR(100) COMMENT "Guérison, Décès, Transfert, Départ volontaire",
            notes_sortie TEXT,
            statut VARCHAR(50) COMMENT "Admis, Hospitalisé, Sortie, Décédé",
            duree_sejour INT COMMENT "En jours",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_admission (hopital_id, numero_admission),
            INDEX idx_patient (patient_id),
            INDEX idx_hopital (hopital_id),
            INDEX idx_service (service_id),
            INDEX idx_date_admission (date_admission),
            INDEX idx_statut (statut),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id),
            FOREIGN KEY (lit_id) REFERENCES lits(id) ON DELETE SET NULL,
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (medecin_sortie_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des transferts de patients
        $this->addSql('CREATE TABLE transferts_patients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admission_id INT NOT NULL,
            service_origine_id INT NOT NULL,
            service_destination_id INT NOT NULL,
            lit_origine_id INT,
            lit_destination_id INT,
            date_transfert DATETIME NOT NULL,
            motif_transfert TEXT,
            utilisateur_id INT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE CASCADE,
            FOREIGN KEY (service_origine_id) REFERENCES services(id),
            FOREIGN KEY (service_destination_id) REFERENCES services(id),
            FOREIGN KEY (lit_origine_id) REFERENCES lits(id) ON DELETE SET NULL,
            FOREIGN KEY (lit_destination_id) REFERENCES lits(id) ON DELETE SET NULL,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 5 : CONSULTATIONS & NOTES CLINIQUES
        // ============================================================================

        // Table des consultations
        $this->addSql('CREATE TABLE consultations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            medecin_id INT NOT NULL,
            service_id INT NOT NULL,
            hopital_id INT NOT NULL,
            rendez_vous_id INT,
            admission_id INT,
            date_consultation DATETIME NOT NULL,
            motif_consultation TEXT,
            diagnostic_principal VARCHAR(255),
            diagnostic_secondaire TEXT,
            plan_traitement TEXT,
            observations_cliniques TEXT,
            examen_physique TEXT,
            tension_arterielle VARCHAR(20),
            frequence_cardiaque INT,
            temperature DECIMAL(4,1),
            poids DECIMAL(6,2),
            taille DECIMAL(5,2),
            imc DECIMAL(5,2),
            statut VARCHAR(50) COMMENT "Complétée, Brouillon, Annulée",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_patient (patient_id),
            INDEX idx_medecin (medecin_id),
            INDEX idx_date (date_consultation),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (service_id) REFERENCES services(id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE SET NULL,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des notes infirmières
        $this->addSql('CREATE TABLE notes_infirmieres (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admission_id INT NOT NULL,
            infirmier_id INT NOT NULL,
            date_note DATETIME NOT NULL,
            type_note VARCHAR(50) COMMENT "Observation, Ronde, Incident, Alerte",
            contenu TEXT NOT NULL,
            signes_vitaux_tension VARCHAR(20),
            signes_vitaux_frequence_cardiaque INT,
            signes_vitaux_temperature DECIMAL(4,1),
            signes_vitaux_frequence_respiratoire INT,
            signes_vitaux_saturation_o2 DECIMAL(5,2),
            observations_generales TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_admission (admission_id),
            INDEX idx_infirmier (infirmier_id),
            INDEX idx_date (date_note),
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE CASCADE,
            FOREIGN KEY (infirmier_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 6 : PRESCRIPTIONS & MÉDICAMENTS
        // ============================================================================

        // Table des médicaments
        $this->addSql('CREATE TABLE medicaments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code_medicament VARCHAR(50) NOT NULL UNIQUE,
            nom_commercial VARCHAR(255) NOT NULL,
            nom_generique VARCHAR(255),
            forme_pharmaceutique VARCHAR(100) COMMENT "Comprimé, Injection, Sirop, etc.",
            dosage VARCHAR(100),
            unite_dosage VARCHAR(50),
            fabricant VARCHAR(255),
            code_atc VARCHAR(50),
            code_cip VARCHAR(50),
            prix_unitaire DECIMAL(10,2),
            devise_id INT,
            taux_tva_id INT,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code_medicament),
            INDEX idx_nom (nom_commercial),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (taux_tva_id) REFERENCES taux_tva(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des contre-indications & interactions
        $this->addSql('CREATE TABLE interactions_medicamenteuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            medicament_1_id INT NOT NULL,
            medicament_2_id INT NOT NULL,
            niveau_severite VARCHAR(50) COMMENT "Légère, Modérée, Grave, Contre-indication",
            description TEXT,
            recommandation TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_interaction (medicament_1_id, medicament_2_id),
            FOREIGN KEY (medicament_1_id) REFERENCES medicaments(id) ON DELETE CASCADE,
            FOREIGN KEY (medicament_2_id) REFERENCES medicaments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des prescriptions
        $this->addSql('CREATE TABLE prescriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            consultation_id INT,
            admission_id INT,
            patient_id INT NOT NULL,
            medecin_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_prescription VARCHAR(50) NOT NULL,
            date_prescription DATETIME NOT NULL,
            date_debut_traitement DATE,
            date_fin_traitement DATE,
            statut VARCHAR(50) COMMENT "Brouillon, Validée, Exécutée, Annulée",
            notes_prescription TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_prescription (hopital_id, numero_prescription),
            INDEX idx_patient (patient_id),
            INDEX idx_medecin (medecin_id),
            INDEX idx_date (date_prescription),
            INDEX idx_statut (statut),
            FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des lignes de prescription
        $this->addSql('CREATE TABLE lignes_prescriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prescription_id INT NOT NULL,
            medicament_id INT NOT NULL,
            quantite DECIMAL(10,2) NOT NULL,
            unite_quantite VARCHAR(50),
            dosage VARCHAR(100),
            frequence_administration VARCHAR(100) COMMENT "1x/jour, 2x/jour, etc.",
            voie_administration VARCHAR(100) COMMENT "Orale, IV, IM, SC, etc.",
            duree_traitement INT COMMENT "En jours",
            instructions_speciales TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_prescription (prescription_id),
            INDEX idx_medicament (medicament_id),
            FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
            FOREIGN KEY (medicament_id) REFERENCES medicaments(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des administrations de médicaments
        $this->addSql('CREATE TABLE administrations_medicaments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ligne_prescription_id INT NOT NULL,
            admission_id INT NOT NULL,
            infirmier_id INT NOT NULL,
            date_administration DATETIME NOT NULL,
            quantite_administree DECIMAL(10,2),
            unite_quantite VARCHAR(50),
            voie_administration VARCHAR(100),
            site_injection VARCHAR(100),
            observations TEXT,
            effet_secondaire_observe BOOLEAN DEFAULT false,
            description_effet_secondaire TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ligne_prescription (ligne_prescription_id),
            INDEX idx_admission (admission_id),
            INDEX idx_infirmier (infirmier_id),
            INDEX idx_date (date_administration),
            FOREIGN KEY (ligne_prescription_id) REFERENCES lignes_prescriptions(id) ON DELETE CASCADE,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE CASCADE,
            FOREIGN KEY (infirmier_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 7 : PHARMACIE & STOCK
        // ============================================================================

        // Table des fournisseurs
        $this->addSql('CREATE TABLE fournisseurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            code_fournisseur VARCHAR(50) NOT NULL,
            nom_fournisseur VARCHAR(255) NOT NULL,
            type_fournisseur VARCHAR(100) COMMENT "Pharmacie, Équipement, Consommables, etc.",
            adresse VARCHAR(255),
            ville VARCHAR(100),
            code_postal VARCHAR(10),
            telephone VARCHAR(20),
            email VARCHAR(255),
            contact_personne VARCHAR(255),
            numero_siret VARCHAR(50),
            numero_tva VARCHAR(50),
            conditions_paiement VARCHAR(100),
            delai_livraison INT COMMENT "En jours",
            logo_fournisseur VARCHAR(500),
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code_fournisseur),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des stocks pharmacie
        $this->addSql('CREATE TABLE stocks_pharmacie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            medicament_id INT NOT NULL,
            quantite_stock INT NOT NULL,
            quantite_minimale INT,
            quantite_maximale INT,
            lot_numero VARCHAR(100),
            date_expiration DATE,
            prix_achat_unitaire DECIMAL(10,2),
            date_reception DATE,
            fournisseur_id INT,
            localisation_stockage VARCHAR(255),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_medicament_lot (hopital_id, medicament_id, lot_numero),
            INDEX idx_hopital (hopital_id),
            INDEX idx_medicament (medicament_id),
            INDEX idx_date_expiration (date_expiration),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (medicament_id) REFERENCES medicaments(id),
            FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des mouvements de stock
        $this->addSql('CREATE TABLE mouvements_stock (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stock_id INT NOT NULL,
            type_mouvement VARCHAR(50) COMMENT "Entrée, Sortie, Ajustement, Perte",
            quantite INT NOT NULL,
            motif TEXT,
            utilisateur_id INT,
            reference_document VARCHAR(100),
            date_mouvement DATETIME NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_stock (stock_id),
            INDEX idx_date (date_mouvement),
            FOREIGN KEY (stock_id) REFERENCES stocks_pharmacie(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des distributions pharmacie
        $this->addSql('CREATE TABLE distributions_pharmacie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            service_id INT NOT NULL,
            numero_bon VARCHAR(50) NOT NULL,
            date_distribution DATETIME NOT NULL,
            pharmacien_id INT NOT NULL,
            statut VARCHAR(50) COMMENT "Brouillon, Validée, Distribuée, Reçue",
            notes TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_bon (hopital_id, numero_bon),
            INDEX idx_hopital (hopital_id),
            INDEX idx_service (service_id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id),
            FOREIGN KEY (pharmacien_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des lignes de distribution
        $this->addSql('CREATE TABLE lignes_distributions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            distribution_id INT NOT NULL,
            medicament_id INT NOT NULL,
            quantite INT NOT NULL,
            lot_numero VARCHAR(100),
            date_expiration DATE,
            prix_unitaire DECIMAL(10,2),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (distribution_id) REFERENCES distributions_pharmacie(id) ON DELETE CASCADE,
            FOREIGN KEY (medicament_id) REFERENCES medicaments(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 8 : LABORATOIRE
        // ============================================================================

        // Table des types d\'examens laboratoire
        $this->addSql('CREATE TABLE types_examens_labo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code_examen VARCHAR(50) NOT NULL UNIQUE,
            nom_examen VARCHAR(255) NOT NULL,
            description TEXT,
            code_loinc VARCHAR(50),
            code_snomed VARCHAR(50),
            type_specimen VARCHAR(100) COMMENT "Sang, Urine, Selles, etc.",
            volume_specimen INT COMMENT "En mL",
            tube_prelevement VARCHAR(100),
            conditions_conservation TEXT,
            delai_resultat INT COMMENT "En heures",
            prix_examen DECIMAL(10,2),
            devise_id INT,
            taux_tva_id INT,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code_examen),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (taux_tva_id) REFERENCES taux_tva(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des panels d\'examens
        $this->addSql('CREATE TABLE panels_examens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code_panel VARCHAR(50) NOT NULL UNIQUE,
            nom_panel VARCHAR(255) NOT NULL,
            description TEXT,
            prix_panel DECIMAL(10,2),
            devise_id INT,
            taux_tva_id INT,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (taux_tva_id) REFERENCES taux_tva(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table de liaison panels-examens
        $this->addSql('CREATE TABLE panel_examens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            panel_id INT NOT NULL,
            examen_id INT NOT NULL,
            ordre INT,
            UNIQUE KEY unique_panel_examen (panel_id, examen_id),
            FOREIGN KEY (panel_id) REFERENCES panels_examens(id) ON DELETE CASCADE,
            FOREIGN KEY (examen_id) REFERENCES types_examens_labo(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des ordonnances laboratoire
        $this->addSql('CREATE TABLE ordonnances_labo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            medecin_id INT NOT NULL,
            hopital_id INT NOT NULL,
            consultation_id INT,
            admission_id INT,
            numero_ordonnance VARCHAR(50) NOT NULL,
            date_ordonnance DATETIME NOT NULL,
            motif_examen TEXT,
            urgence BOOLEAN DEFAULT false,
            statut VARCHAR(50) COMMENT "Brouillon, Validée, Reçue au labo, En cours, Complétée, Annulée",
            notes_ordonnance TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_ordonnance (hopital_id, numero_ordonnance),
            INDEX idx_patient (patient_id),
            INDEX idx_medecin (medecin_id),
            INDEX idx_date (date_ordonnance),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des lignes d\'ordonnance labo
        $this->addSql('CREATE TABLE lignes_ordonnances_labo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ordonnance_id INT NOT NULL,
            examen_id INT NOT NULL,
            panel_id INT,
            quantite INT DEFAULT 1,
            priorite VARCHAR(50) COMMENT "Routine, Urgent",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ordonnance_id) REFERENCES ordonnances_labo(id) ON DELETE CASCADE,
            FOREIGN KEY (examen_id) REFERENCES types_examens_labo(id),
            FOREIGN KEY (panel_id) REFERENCES panels_examens(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des prélèvements
        $this->addSql('CREATE TABLE prelevements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ordonnance_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_prelevement VARCHAR(50) NOT NULL,
            date_prelevement DATETIME NOT NULL,
            infirmier_id INT NOT NULL,
            type_specimen VARCHAR(100),
            volume_specimen INT,
            tube_prelevement VARCHAR(100),
            numero_tube VARCHAR(50),
            conditions_conservation TEXT,
            observations_prelevement TEXT,
            statut VARCHAR(50) COMMENT "Prélevé, En transit, Reçu au labo, Rejeté",
            date_reception_labo DATETIME,
            raison_rejet TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_prelevement (hopital_id, numero_prelevement),
            INDEX idx_ordonnance (ordonnance_id),
            INDEX idx_patient (patient_id),
            INDEX idx_date (date_prelevement),
            FOREIGN KEY (ordonnance_id) REFERENCES ordonnances_labo(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (infirmier_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des résultats laboratoire
        $this->addSql('CREATE TABLE resultats_labo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prelevement_id INT NOT NULL,
            examen_id INT NOT NULL,
            valeur_resultat VARCHAR(255),
            unite_resultat VARCHAR(50),
            valeur_reference_min DECIMAL(10,2),
            valeur_reference_max DECIMAL(10,2),
            statut_resultat VARCHAR(50) COMMENT "Normal, Anormal, Critique",
            interpretation TEXT,
            technicien_id INT,
            date_analyse DATETIME,
            validateur_id INT,
            date_validation DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_prelevement (prelevement_id),
            INDEX idx_examen (examen_id),
            FOREIGN KEY (prelevement_id) REFERENCES prelevements(id) ON DELETE CASCADE,
            FOREIGN KEY (examen_id) REFERENCES types_examens_labo(id),
            FOREIGN KEY (technicien_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
            FOREIGN KEY (validateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 9 : IMAGERIE & RADIOLOGIE
        // ============================================================================

        // Table des types d\'imagerie
        $this->addSql('CREATE TABLE types_imagerie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code_imagerie VARCHAR(50) NOT NULL UNIQUE,
            nom_imagerie VARCHAR(255) NOT NULL,
            description TEXT,
            type_modalite VARCHAR(100) COMMENT "Radiographie, Scanner, IRM, Échographie, etc.",
            code_snomed VARCHAR(50),
            prix_examen DECIMAL(10,2),
            devise_id INT,
            taux_tva_id INT,
            duree_examen INT COMMENT "En minutes",
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (taux_tva_id) REFERENCES taux_tva(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des ordonnances imagerie
        $this->addSql('CREATE TABLE ordonnances_imagerie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            medecin_id INT NOT NULL,
            hopital_id INT NOT NULL,
            consultation_id INT,
            admission_id INT,
            numero_ordonnance VARCHAR(50) NOT NULL,
            date_ordonnance DATETIME NOT NULL,
            motif_examen TEXT,
            urgence BOOLEAN DEFAULT false,
            zone_anatomique VARCHAR(255),
            antecedents_pertinents TEXT,
            statut VARCHAR(50) COMMENT "Brouillon, Validée, Programmée, Réalisée, Rapportée, Annulée",
            notes_ordonnance TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_ordonnance (hopital_id, numero_ordonnance),
            INDEX idx_patient (patient_id),
            INDEX idx_medecin (medecin_id),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des lignes d\'ordonnance imagerie
        $this->addSql('CREATE TABLE lignes_ordonnances_imagerie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ordonnance_id INT NOT NULL,
            imagerie_id INT NOT NULL,
            quantite INT DEFAULT 1,
            priorite VARCHAR(50) COMMENT "Routine, Urgent",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ordonnance_id) REFERENCES ordonnances_imagerie(id) ON DELETE CASCADE,
            FOREIGN KEY (imagerie_id) REFERENCES types_imagerie(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des examens imagerie réalisés
        $this->addSql('CREATE TABLE examens_imagerie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ordonnance_id INT NOT NULL,
            imagerie_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_examen VARCHAR(50) NOT NULL,
            date_examen DATETIME NOT NULL,
            technicien_id INT NOT NULL,
            modalite VARCHAR(100),
            zone_anatomique VARCHAR(255),
            observations_technique TEXT,
            statut VARCHAR(50) COMMENT "Programmé, En cours, Complété, Rejeté",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_examen (hopital_id, numero_examen),
            INDEX idx_ordonnance (ordonnance_id),
            INDEX idx_patient (patient_id),
            FOREIGN KEY (ordonnance_id) REFERENCES ordonnances_imagerie(id) ON DELETE CASCADE,
            FOREIGN KEY (imagerie_id) REFERENCES types_imagerie(id),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (technicien_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des rapports radiologiques
        $this->addSql('CREATE TABLE rapports_radiologiques (
            id INT AUTO_INCREMENT PRIMARY KEY,
            examen_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_rapport VARCHAR(50) NOT NULL,
            date_rapport DATETIME NOT NULL,
            radiologue_id INT NOT NULL,
            titre_rapport VARCHAR(255),
            contenu_rapport LONGTEXT,
            conclusion TEXT,
            recommandations TEXT,
            statut VARCHAR(50) COMMENT "Brouillon, Validé, Signé, Archivé",
            date_signature DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_rapport (hopital_id, numero_rapport),
            INDEX idx_examen (examen_id),
            INDEX idx_patient (patient_id),
            FOREIGN KEY (examen_id) REFERENCES examens_imagerie(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (radiologue_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des fichiers DICOM
        $this->addSql('CREATE TABLE fichiers_dicom (
            id INT AUTO_INCREMENT PRIMARY KEY,
            examen_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_serie VARCHAR(100),
            numero_instance VARCHAR(100),
            chemin_fichier VARCHAR(500),
            nom_fichier VARCHAR(255),
            taille_fichier INT COMMENT "En octets",
            format_fichier VARCHAR(50),
            date_creation_dicom DATETIME,
            date_archivage DATETIME,
            localisation_pacs VARCHAR(255),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_examen (examen_id),
            INDEX idx_hopital (hopital_id),
            FOREIGN KEY (examen_id) REFERENCES examens_imagerie(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 10 : INTERVENTIONS CHIRURGICALES
        // ============================================================================

        // Table des types d\'interventions
        $this->addSql('CREATE TABLE types_interventions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code_intervention VARCHAR(50) NOT NULL UNIQUE,
            nom_intervention VARCHAR(255) NOT NULL,
            description TEXT,
            code_ccam VARCHAR(50),
            duree_moyenne INT COMMENT "En minutes",
            niveau_complexite VARCHAR(50) COMMENT "Simple, Modérée, Complexe",
            prix_intervention DECIMAL(10,2),
            devise_id INT,
            taux_tva_id INT,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (taux_tva_id) REFERENCES taux_tva(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des demandes d\'intervention
        $this->addSql('CREATE TABLE demandes_interventions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            medecin_id INT NOT NULL,
            hopital_id INT NOT NULL,
            consultation_id INT,
            admission_id INT,
            numero_demande VARCHAR(50) NOT NULL,
            date_demande DATETIME NOT NULL,
            intervention_id INT NOT NULL,
            urgence BOOLEAN DEFAULT false,
            diagnostic_preoperatoire TEXT,
            antecedents_chirurgicaux TEXT,
            medicaments_actuels TEXT,
            allergies TEXT,
            statut VARCHAR(50) COMMENT "Brouillon, Validée, Programmée, Réalisée, Annul��e",
            date_intervention_prevue DATE,
            notes_demande TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_demande (hopital_id, numero_demande),
            INDEX idx_patient (patient_id),
            INDEX idx_medecin (medecin_id),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL,
            FOREIGN KEY (intervention_id) REFERENCES types_interventions(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');


        // Table des salles d\'opération
        $this->addSql('CREATE TABLE salles_operations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            service_id INT NOT NULL,
            numero_salle VARCHAR(50) NOT NULL,
            type_salle VARCHAR(100) COMMENT "Chirurgie générale, Orthopédie, Cardiologie, etc.",
            capacite INT,
            equipements TEXT,
            statut VARCHAR(50) COMMENT "Disponible, Occupée, Maintenance, Fermée",
            date_derniere_maintenance DATE,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_salle (hopital_id, numero_salle),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');


        // Table du planning opératoire
        $this->addSql('CREATE TABLE planning_operatoire (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            salle_operation_id INT NOT NULL,
            demande_intervention_id INT NOT NULL,
            patient_id INT NOT NULL,
            chirurgien_id INT NOT NULL,
            date_operation DATE NOT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME,
            duree_reelle INT COMMENT "En minutes",
            statut VARCHAR(50) COMMENT "Programmée, En cours, Complétée, Annulée, Reportée",
            raison_annulation TEXT,
            date_annulation DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_hopital (hopital_id),
            INDEX idx_salle (salle_operation_id),
            INDEX idx_date (date_operation),
            INDEX idx_statut (statut),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (salle_operation_id) REFERENCES salles_operations(id),
            FOREIGN KEY (demande_intervention_id) REFERENCES demandes_interventions(id),
            FOREIGN KEY (patient_id) REFERENCES patients(id),
            FOREIGN KEY (chirurgien_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

       
        

        // Table des équipes chirurgicales
        $this->addSql('CREATE TABLE equipes_chirurgicales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            planning_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            role_equipe VARCHAR(100) COMMENT "Chirurgien, Assistant, Anesthésiste, Infirmier, etc.",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_planning_user (planning_id, utilisateur_id),
            FOREIGN KEY (planning_id) REFERENCES planning_operatoire(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des rapports opératoires
        $this->addSql('CREATE TABLE rapports_operatoires (
            id INT AUTO_INCREMENT PRIMARY KEY,
            planning_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_rapport VARCHAR(50) NOT NULL,
            date_rapport DATETIME NOT NULL,
            chirurgien_id INT NOT NULL,
            titre_intervention VARCHAR(255),
            description_intervention LONGTEXT,
            complications TEXT,
            produits_utilises TEXT,
            specimens_preleves TEXT,
            recommandations_postop TEXT,
            statut VARCHAR(50) COMMENT "Brouillon, Validé, Signé, Archivé",
            date_signature DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_rapport (hopital_id, numero_rapport),
            INDEX idx_planning (planning_id),
            INDEX idx_patient (patient_id),
            FOREIGN KEY (planning_id) REFERENCES planning_operatoire(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (chirurgien_id) REFERENCES utilisateurs(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 11 : URGENCES & TRIAGE
        // ============================================================================

        // Table des niveaux de triage
        $this->addSql('CREATE TABLE niveaux_triage (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            nom VARCHAR(100) NOT NULL,
            description TEXT,
            couleur VARCHAR(20),
            icone VARCHAR(500),
            priorite INT,
            delai_prise_en_charge INT COMMENT "En minutes",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des triages
        $this->addSql('CREATE TABLE triages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            service_urgences_id INT NOT NULL,
            numero_triage VARCHAR(50) NOT NULL,
            date_triage DATETIME NOT NULL,
            infirmier_triage_id INT NOT NULL,
            niveau_triage_id INT NOT NULL,
            motif_consultation TEXT,
            antecedents_pertinents TEXT,
            allergies TEXT,
            medicaments_actuels TEXT,
            tension_arterielle VARCHAR(20),
            frequence_cardiaque INT,
            temperature DECIMAL(4,1),
            frequence_respiratoire INT,
            saturation_o2 DECIMAL(5,2),
            poids DECIMAL(6,2),
            observations_triage TEXT,
            statut VARCHAR(50) COMMENT "Triagé, En attente, Pris en charge, Transféré",
            date_prise_en_charge DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_triage (hopital_id, numero_triage),
            INDEX idx_patient (patient_id),
            INDEX idx_hopital (hopital_id),
            INDEX idx_date (date_triage),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (service_urgences_id) REFERENCES services(id),
            FOREIGN KEY (infirmier_triage_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (niveau_triage_id) REFERENCES niveaux_triage(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 12 : COMPTABILITÉ & FACTURATION
        // ============================================================================



        // Table des bons de commande
        $this->addSql('CREATE TABLE bons_commande (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            fournisseur_id INT NOT NULL,
            numero_bon VARCHAR(50) NOT NULL,
            date_commande DATE NOT NULL,
            utilisateur_id INT NOT NULL,
            date_livraison_prevue DATE,
            date_livraison_reelle DATE,
            montant_total DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Brouillon, Validée, Commandée, Livrée, Facturée, Annulée",
            notes_commande TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_bon (hopital_id, numero_bon),
            INDEX idx_hopital (hopital_id),
            INDEX idx_fournisseur (fournisseur_id),
            INDEX idx_date (date_commande),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des lignes de bon de commande
        $this->addSql('CREATE TABLE lignes_bons_commande (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bon_commande_id INT NOT NULL,
            medicament_id INT,
            description_article VARCHAR(255),
            quantite INT NOT NULL,
            prix_unitaire DECIMAL(10,2),
            montant_ligne DECIMAL(12,2),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (bon_commande_id) REFERENCES bons_commande(id) ON DELETE CASCADE,
            FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des actes médicaux (tarification)
        $this->addSql('CREATE TABLE actes_medicaux (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            code_acte VARCHAR(50) NOT NULL,
            nom_acte VARCHAR(255) NOT NULL,
            description TEXT,
            code_ccam VARCHAR(50),
            code_cim VARCHAR(50),
            prix_acte DECIMAL(10,2),
            devise_id INT,
            taux_tva_id INT,
            date_debut DATE,
            date_fin DATE,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code_acte),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (taux_tva_id) REFERENCES taux_tva(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des factures
        $this->addSql('CREATE TABLE factures (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            patient_id INT NOT NULL,
            admission_id INT,
            numero_facture VARCHAR(50) NOT NULL,
            date_facture DATE NOT NULL,
            date_echeance DATE,
            montant_total DECIMAL(12,2),
            montant_assurance DECIMAL(12,2),
            montant_patient DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Brouillon, Validée, Envoyée, Payée, Partiellement payée, Impayée",
            type_facture VARCHAR(50) COMMENT "Consultation, Hospitalisation, Acte, Autre",
            notes_facture TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_facture (hopital_id, numero_facture),
            INDEX idx_hopital (hopital_id),
            INDEX idx_patient (patient_id),
            INDEX idx_date (date_facture),
            INDEX idx_statut (statut),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des lignes de facture
        $this->addSql('CREATE TABLE lignes_factures (
            id INT AUTO_INCREMENT PRIMARY KEY,
            facture_id INT NOT NULL,
            acte_id INT,
            description_ligne VARCHAR(255),
            quantite INT DEFAULT 1,
            prix_unitaire DECIMAL(10,2),
            montant_ligne DECIMAL(12,2),
            taux_tva DECIMAL(5,2),
            montant_tva DECIMAL(12,2),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
            FOREIGN KEY (acte_id) REFERENCES actes_medicaux(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des paiements (AMÉLIORÉE)
        $this->addSql('CREATE TABLE paiements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            facture_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_paiement VARCHAR(50) NOT NULL,
            date_paiement DATE NOT NULL,
            montant_paiement DECIMAL(12,2),
            devise_id INT,
            mode_paiement_id INT,
            reference_paiement VARCHAR(100),
            taux_paiement DECIMAL(5,2),
            frais_transaction DECIMAL(10,2),
            statut VARCHAR(50) COMMENT "Enregistré, Validé, Annulé",
            notes_paiement TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_paiement (hopital_id, numero_paiement),
            INDEX idx_facture (facture_id),
            INDEX idx_patient (patient_id),
            INDEX idx_date (date_paiement),
            FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL,
            FOREIGN KEY (mode_paiement_id) REFERENCES modes_paiement(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des réclamations assurance
        $this->addSql('CREATE TABLE reclamations_assurance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            facture_id INT NOT NULL,
            convention_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_reclamation VARCHAR(50) NOT NULL,
            date_reclamation DATE NOT NULL,
            montant_reclame DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Soumise, En cours, Acceptée, Rejetée, Partiellement acceptée",
            motif_rejet TEXT,
            date_reponse DATE,
            montant_accepte DECIMAL(12,2),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_reclamation (hopital_id, numero_reclamation),
            FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE,
            FOREIGN KEY (convention_id) REFERENCES conventions_assurance(id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 13 : RESSOURCES HUMAINES & PAIE
        // ============================================================================

        // Table des contrats
        $this->addSql('CREATE TABLE contrats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_contrat VARCHAR(50) NOT NULL,
            type_contrat VARCHAR(50) COMMENT "CDI, CDD, Stage, Consultant",
            date_debut DATE NOT NULL,
            date_fin DATE,
            poste VARCHAR(255),
            salaire_base DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Actif, Suspendu, Terminé",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_contrat (hopital_id, numero_contrat),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des congés
        $this->addSql('CREATE TABLE conges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            hopital_id INT NOT NULL,
            type_conge VARCHAR(50) COMMENT "Annuel, Maladie, Maternité, Paternité, Autre",
            date_debut DATE NOT NULL,
            date_fin DATE NOT NULL,
            nombre_jours INT,
            statut VARCHAR(50) COMMENT "Demandé, Approuvé, Rejeté, En cours, Terminé",
            motif TEXT,
            approbateur_id INT,
            date_approbation DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_utilisateur (utilisateur_id),
            INDEX idx_hopital (hopital_id),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (approbateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des formations
        $this->addSql('CREATE TABLE formations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            code_formation VARCHAR(50) NOT NULL,
            nom_formation VARCHAR(255) NOT NULL,
            description TEXT,
            type_formation VARCHAR(100) COMMENT "Interne, Externe, E-learning",
            date_debut DATE,
            date_fin DATE,
            formateur VARCHAR(255),
            nombre_heures INT,
            lieu_formation VARCHAR(255),
            cout_formation DECIMAL(10,2),
            devise_id INT,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code_formation),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table de participation aux formations
        $this->addSql('CREATE TABLE participations_formations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            formation_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            date_participation DATE,
            statut VARCHAR(50) COMMENT "Inscrit, Participé, Absent, Complété",
            note_evaluation DECIMAL(5,2),
            certificat_obtenu BOOLEAN DEFAULT false,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_formation_user (formation_id, utilisateur_id),
            FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des certifications
        $this->addSql('CREATE TABLE certifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            hopital_id INT NOT NULL,
            nom_certification VARCHAR(255) NOT NULL,
            organisme_certification VARCHAR(255),
            date_obtention DATE NOT NULL,
            date_expiration DATE,
            numero_certification VARCHAR(100),
            statut VARCHAR(50) COMMENT "Valide, Expiré, Suspendu",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des bulletins de paie
        $this->addSql('CREATE TABLE bulletins_paie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_bulletin VARCHAR(50) NOT NULL,
            periode_debut DATE NOT NULL,
            periode_fin DATE NOT NULL,
            salaire_brut DECIMAL(12,2),
            cotisations_sociales DECIMAL(12,2),
            impot_revenu DECIMAL(12,2),
            autres_deductions DECIMAL(12,2),
            salaire_net DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Brouillon, Validé, Payé",
            date_paiement DATE,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_bulletin (hopital_id, numero_bulletin),
            INDEX idx_utilisateur (utilisateur_id),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 14 : MAINTENANCE & INFRASTRUCTURE
        // ============================================================================

        // Table des équipements
        $this->addSql('CREATE TABLE equipements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            service_id INT,
            code_equipement VARCHAR(50) NOT NULL,
            nom_equipement VARCHAR(255) NOT NULL,
            type_equipement VARCHAR(100) COMMENT "Biomédical, Informatique, Mobilier, etc.",
            marque VARCHAR(100),
            modele VARCHAR(100),
            numero_serie VARCHAR(100),
            date_acquisition DATE,
            date_mise_en_service DATE,
            prix_acquisition DECIMAL(12,2),
            devise_id INT,
            duree_vie_utile INT COMMENT "En années",
            statut VARCHAR(50) COMMENT "Actif, Maintenance, Hors service, Retiré",
            localisation VARCHAR(255),
            fournisseur_id INT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code_equipement),
            INDEX idx_hopital (hopital_id),
            INDEX idx_service (service_id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
            FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL,
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des contrats de maintenance
        $this->addSql('CREATE TABLE contrats_maintenance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            equipement_id INT NOT NULL,
            hopital_id INT NOT NULL,
            fournisseur_id INT NOT NULL,
            numero_contrat VARCHAR(50) NOT NULL,
            type_maintenance VARCHAR(50) COMMENT "Préventive, Corrective, Mixte",
            date_debut DATE NOT NULL,
            date_fin DATE,
            frequence_maintenance VARCHAR(100) COMMENT "Mensuelle, Trimestrielle, Annuelle",
            cout_maintenance DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Actif, Expiré, Suspendu",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_contrat (hopital_id, numero_contrat),
            FOREIGN KEY (equipement_id) REFERENCES equipements(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des interventions de maintenance
        $this->addSql('CREATE TABLE interventions_maintenance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            equipement_id INT NOT NULL,
            hopital_id INT NOT NULL,
            numero_intervention VARCHAR(50) NOT NULL,
            date_intervention DATE NOT NULL,
            type_intervention VARCHAR(50) COMMENT "Préventive, Corrective, Inspection",
            technicien_id INT NOT NULL,
            description_intervention TEXT,
            pieces_remplacees TEXT,
            duree_intervention INT COMMENT "En heures",
            cout_intervention DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Programmée, En cours, Complétée, Annulée",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_intervention (hopital_id, numero_intervention),
            INDEX idx_equipement (equipement_id),
            INDEX idx_date (date_intervention),
            FOREIGN KEY (equipement_id) REFERENCES equipements(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (technicien_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 15 : QUALITÉ, CONFORMITÉ & RECHERCHE
        // ============================================================================

        // Table des indicateurs qualité
        $this->addSql('CREATE TABLE indicateurs_qualite (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            service_id INT,
            code_indicateur VARCHAR(50) NOT NULL,
            nom_indicateur VARCHAR(255) NOT NULL,
            description TEXT,
            type_indicateur VARCHAR(100) COMMENT "Infection, Mortalité, Réadmission, Satisfaction, etc.",
            unite_mesure VARCHAR(50),
            valeur_cible DECIMAL(10,2),
            frequence_mesure VARCHAR(50) COMMENT "Quotidienne, Hebdomadaire, Mensuelle",
            date_debut DATE,
            date_fin DATE,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code_indicateur),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des mesures d\'indicateurs
        $this->addSql('CREATE TABLE mesures_indicateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            indicateur_id INT NOT NULL,
            hopital_id INT NOT NULL,
            valeur_mesuree DECIMAL(10,2),
            date_mesure DATE NOT NULL,
            notes_mesure TEXT,
            utilisateur_id INT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_indicateur (indicateur_id),
            INDEX idx_date (date_mesure),
            FOREIGN KEY (indicateur_id) REFERENCES indicateurs_qualite(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des plaintes & incidents
        $this->addSql('CREATE TABLE plaintes_incidents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            patient_id INT,
            utilisateur_id INT,
            numero_plainte VARCHAR(50) NOT NULL,
            date_plainte DATETIME NOT NULL,
            type_plainte VARCHAR(100) COMMENT "Clinique, Administrative, Qualité, Sécurité",
            description_plainte TEXT,
            gravite VARCHAR(50) COMMENT "Mineure, Modérée, Grave, Critique",
            statut VARCHAR(50) COMMENT "Ouverte, En investigation, Résolue, Fermée",
            responsable_investigation_id INT,
            date_resolution DATETIME,
            actions_correctives TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_plainte (hopital_id, numero_plainte),
            INDEX idx_hopital (hopital_id),
            INDEX idx_date (date_plainte),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
            FOREIGN KEY (responsable_investigation_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des projets de recherche
        $this->addSql('CREATE TABLE projets_recherche (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            code_projet VARCHAR(50) NOT NULL,
            titre_projet VARCHAR(255) NOT NULL,
            description_projet LONGTEXT,
            chercheur_principal_id INT NOT NULL,
            date_debut DATE NOT NULL,
            date_fin DATE,
            budget_projet DECIMAL(12,2),
            devise_id INT,
            statut VARCHAR(50) COMMENT "Planifié, En cours, Suspendu, Complété, Annulé",
            comite_ethique_approuve BOOLEAN DEFAULT false,
            date_approbation_ethique DATE,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code_projet),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (chercheur_principal_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (devise_id) REFERENCES devises(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des consentements de recherche
        $this->addSql('CREATE TABLE consentements_recherche (
            id INT AUTO_INCREMENT PRIMARY KEY,
            projet_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            date_consentement DATE NOT NULL,
            consentement_donne BOOLEAN DEFAULT false,
            signature_patient VARCHAR(255),
            signature_temoin VARCHAR(255),
            date_retrait_consentement DATE,
            raison_retrait TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_projet_patient (projet_id, patient_id),
            FOREIGN KEY (projet_id) REFERENCES projets_recherche(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 16 : ARCHIVAGE & DOCUMENTS
        // ============================================================================

        // Table des documents
        $this->addSql('CREATE TABLE documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            patient_id INT,
            utilisateur_id INT,
            type_document VARCHAR(100) COMMENT "Ordonnance, Rapport, Certificat, Feuille de sortie, etc.",
            nom_document VARCHAR(255) NOT NULL,
            description TEXT,
            chemin_fichier VARCHAR(500),
            nom_fichier VARCHAR(255),
            type_fichier VARCHAR(50),
            taille_fichier INT COMMENT "En octets",
            date_document DATE,
            date_expiration DATE,
            statut VARCHAR(50) COMMENT "Brouillon, Validé, Signé, Archivé",
            confidentialite VARCHAR(50) COMMENT "Public, Confidentiel, Très confidentiel",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_hopital (hopital_id),
            INDEX idx_patient (patient_id),
            INDEX idx_type (type_document),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des archives
        $this->addSql('CREATE TABLE archives (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            patient_id INT NOT NULL,
            numero_archive VARCHAR(50) NOT NULL,
            date_archivage DATETIME NOT NULL,
            type_archivage VARCHAR(50) COMMENT "Dossier complet, Dossier partiel, Documents",
            raison_archivage VARCHAR(100) COMMENT "Fin de traitement, Décès, Transfert, Inactivité",
            localisation_archive VARCHAR(255),
            support_archive VARCHAR(50) COMMENT "Physique, Numérique, Hybride",
            duree_conservation INT COMMENT "En années",
            date_destruction_prevue DATE,
            statut VARCHAR(50) COMMENT "Archivé, En attente destruction, Détruit",
            utilisateur_archivage_id INT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_archive (hopital_id, numero_archive),
            INDEX idx_hopital (hopital_id),
            INDEX idx_patient (patient_id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_archivage_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des sauvegardes
        $this->addSql('CREATE TABLE sauvegardes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            numero_sauvegarde VARCHAR(50) NOT NULL,
            date_sauvegarde DATETIME NOT NULL,
            type_sauvegarde VARCHAR(50) COMMENT "Complète, Incrémentale, Différentielle",
            taille_sauvegarde INT COMMENT "En Mo",
            localisation_sauvegarde VARCHAR(255),
            statut VARCHAR(50) COMMENT "En cours, Complétée, Échouée",
            duree_sauvegarde INT COMMENT "En secondes",
            utilisateur_id INT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_sauvegarde (hopital_id, numero_sauvegarde),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 17 : INTÉGRATIONS & INTEROPÉRABILITÉ
        // ============================================================================

        // Table des intégrations externes
        $this->addSql('CREATE TABLE integrations_externes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            code_integration VARCHAR(50) NOT NULL,
            nom_integration VARCHAR(255) NOT NULL,
            type_integration VARCHAR(100) COMMENT "HL7, FHIR, API REST, SFTP, etc.",
            url_endpoint VARCHAR(500),
            authentification_type VARCHAR(50) COMMENT "API Key, OAuth2, Basic Auth",
            statut VARCHAR(50) COMMENT "Active, Inactive, Erreur",
            derniere_synchronisation DATETIME,
            frequence_synchronisation VARCHAR(100),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_code (hopital_id, code_integration),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des logs d\'intégration
        $this->addSql('CREATE TABLE logs_integrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            integration_id INT NOT NULL,
            hopital_id INT NOT NULL,
            date_log DATETIME NOT NULL,
            type_log VARCHAR(50) COMMENT "Info, Avertissement, Erreur",
            message_log TEXT,
            donnees_envoyees LONGTEXT,
            donnees_recues LONGTEXT,
            statut_reponse INT COMMENT "Code HTTP",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_integration (integration_id),
            INDEX idx_date (date_log),
            FOREIGN KEY (integration_id) REFERENCES integrations_externes(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 18 : PORTAIL PATIENT
        // ============================================================================

        // Table des comptes portail patient
        $this->addSql('CREATE TABLE comptes_portail_patient (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            email_portail VARCHAR(255) NOT NULL UNIQUE,
            mot_de_passe_portail VARCHAR(255),
            date_creation_compte DATETIME NOT NULL,
            date_derniere_connexion DATETIME,
            actif BOOLEAN DEFAULT true,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_patient_hopital (patient_id, hopital_id),
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des messages sécurisés
        $this->addSql('CREATE TABLE messages_securises (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            patient_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            sujet VARCHAR(255),
            contenu_message TEXT NOT NULL,
            type_message VARCHAR(50) COMMENT "Consultation, Résultat, Rappel, Autre",
            date_envoi DATETIME NOT NULL,
            date_lecture DATETIME,
            statut VARCHAR(50) COMMENT "Envoyé, Lu, Répondu, Archivé",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_patient (patient_id),
            INDEX idx_hopital (hopital_id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 19 : FEUILLES DE SORTIE & SUIVI POST-HOSPITALISATION
        // ============================================================================

        // Table des feuilles de sortie
        $this->addSql('CREATE TABLE feuilles_sortie (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admission_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            medecin_id INT NOT NULL,
            numero_feuille VARCHAR(50) NOT NULL,
            date_sortie DATE NOT NULL,
            diagnostic_principal VARCHAR(255),
            diagnostic_secondaire TEXT,
            procedures_effectuees TEXT,
            medicaments_sortie TEXT,
            recommandations_sortie TEXT,
            suivi_recommande TEXT,
            date_prochain_rendez_vous DATE,
            medecin_suivi_id INT,
            restrictions_activites TEXT,
            regime_alimentaire TEXT,
            soins_plaies TEXT,
            signes_alerte TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_hopital_feuille (hopital_id, numero_feuille),
            INDEX idx_admission (admission_id),
            INDEX idx_patient (patient_id),
            FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (medecin_id) REFERENCES utilisateurs(id),
            FOREIGN KEY (medecin_suivi_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table du suivi post-hospitalisation
        $this->addSql('CREATE TABLE suivis_post_hospitalisation (
            id INT AUTO_INCREMENT PRIMARY KEY,
            feuille_sortie_id INT NOT NULL,
            patient_id INT NOT NULL,
            hopital_id INT NOT NULL,
            date_suivi DATE NOT NULL,
            type_suivi VARCHAR(50) COMMENT "Téléphone, Visite, Consultation",
            observations_suivi TEXT,
            complications_observees TEXT,
            adherence_traitement VARCHAR(50) COMMENT "Bonne, Partielle, Mauvaise",
            readmission_necessaire BOOLEAN DEFAULT false,
            raison_readmission TEXT,
            utilisateur_suivi_id INT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_feuille (feuille_sortie_id),
            INDEX idx_patient (patient_id),
            FOREIGN KEY (feuille_sortie_id) REFERENCES feuilles_sortie(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id),
            FOREIGN KEY (utilisateur_suivi_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 20 : NOTIFICATIONS & ALERTES
        // ============================================================================

        // Table des notifications
        $this->addSql('CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            type_notification VARCHAR(100) COMMENT "Alerte, Rappel, Information, Urgence",
            titre_notification VARCHAR(255),
            contenu_notification TEXT,
            priorite VARCHAR(50) COMMENT "Basse, Normale, Haute, Critique",
            date_notification DATETIME NOT NULL,
            date_lecture DATETIME,
            statut VARCHAR(50) COMMENT "Non lue, Lue, Archivée",
            lien_action VARCHAR(500),
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_utilisateur (utilisateur_id),
            INDEX idx_hopital (hopital_id),
            INDEX idx_date (date_notification),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 21 : SESSIONS & TOKENS
        // ============================================================================

        // Table des sessions utilisateurs
        $this->addSql('CREATE TABLE sessions_utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            utilisateur_id INT NOT NULL,
            hopital_id INT NOT NULL,
            token_session VARCHAR(500) NOT NULL UNIQUE,
            adresse_ip VARCHAR(45),
            user_agent TEXT,
            date_connexion DATETIME NOT NULL,
            date_derniere_activite DATETIME,
            date_deconnexion DATETIME,
            statut VARCHAR(50) COMMENT "Active, Expirée, Fermée",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_utilisateur (utilisateur_id),
            INDEX idx_token (token_session),
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ============================================================================
        // SECTION 22 : TABLEAUX DE BORD & RAPPORTS
        // ============================================================================

        // Table des rapports personnalisés
        $this->addSql('CREATE TABLE rapports_personnalises (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hopital_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            nom_rapport VARCHAR(255) NOT NULL,
            description_rapport TEXT,
            type_rapport VARCHAR(100) COMMENT "Activité, Financier, Qualité, RH, etc.",
            configuration_rapport LONGTEXT COMMENT "JSON avec paramètres du rapport",
            frequence_generation VARCHAR(50) COMMENT "Manuelle, Quotidienne, Hebdomadaire, Mensuelle",
            date_derniere_generation DATETIME,
            statut VARCHAR(50) COMMENT "Actif, Inactif",
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_hopital (hopital_id),
            INDEX idx_utilisateur (utilisateur_id),
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Table des exports de rapports
        $this->addSql('CREATE TABLE exports_rapports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rapport_id INT NOT NULL,
            hopital_id INT NOT NULL,
            utilisateur_id INT NOT NULL,
            format_export VARCHAR(50) COMMENT "PDF, CSV, Excel, JSON",
            chemin_fichier VARCHAR(500),
            nom_fichier VARCHAR(255),
            taille_fichier INT COMMENT "En octets",
            date_export DATETIME NOT NULL,
            statut VARCHAR(50) COMMENT "Généré, Téléchargé, Expiré",
            date_expiration DATETIME,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_rapport (rapport_id),
            INDEX idx_hopital (hopital_id),
            FOREIGN KEY (rapport_id) REFERENCES rapports_personnalises(id) ON DELETE CASCADE,
            FOREIGN KEY (hopital_id) REFERENCES hopitaux(id) ON DELETE CASCADE,
            FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        // Suppression des tables dans l\'ordre inverse des dépendances
        $this->addSql('DROP TABLE IF EXISTS exports_rapports');
        $this->addSql('DROP TABLE IF EXISTS rapports_personnalises');
        $this->addSql('DROP TABLE IF EXISTS sessions_utilisateurs');
        $this->addSql('DROP TABLE IF EXISTS notifications');
        $this->addSql('DROP TABLE IF EXISTS suivis_post_hospitalisation');
        $this->addSql('DROP TABLE IF EXISTS feuilles_sortie');
        $this->addSql('DROP TABLE IF EXISTS messages_securises');
        $this->addSql('DROP TABLE IF EXISTS comptes_portail_patient');
        $this->addSql('DROP TABLE IF EXISTS logs_integrations');
        $this->addSql('DROP TABLE IF EXISTS integrations_externes');
        $this->addSql('DROP TABLE IF EXISTS sauvegardes');
        $this->addSql('DROP TABLE IF EXISTS archives');
        $this->addSql('DROP TABLE IF EXISTS documents');
        $this->addSql('DROP TABLE IF EXISTS consentements_recherche');
        $this->addSql('DROP TABLE IF EXISTS projets_recherche');
        $this->addSql('DROP TABLE IF EXISTS plaintes_incidents');
        $this->addSql('DROP TABLE IF EXISTS mesures_indicateurs');
        $this->addSql('DROP TABLE IF EXISTS indicateurs_qualite');
        $this->addSql('DROP TABLE IF EXISTS interventions_maintenance');
        $this->addSql('DROP TABLE IF EXISTS contrats_maintenance');
        $this->addSql('DROP TABLE IF EXISTS equipements');
        $this->addSql('DROP TABLE IF EXISTS bulletins_paie');
        $this->addSql('DROP TABLE IF EXISTS certifications');
        $this->addSql('DROP TABLE IF EXISTS participations_formations');
        $this->addSql('DROP TABLE IF EXISTS formations');
        $this->addSql('DROP TABLE IF EXISTS conges');
        $this->addSql('DROP TABLE IF EXISTS contrats');
        $this->addSql('DROP TABLE IF EXISTS reclamations_assurance');
        $this->addSql('DROP TABLE IF EXISTS paiements');
        $this->addSql('DROP TABLE IF EXISTS lignes_factures');
        $this->addSql('DROP TABLE IF EXISTS factures');
        $this->addSql('DROP TABLE IF EXISTS actes_medicaux');
        $this->addSql('DROP TABLE IF EXISTS lignes_bons_commande');
        $this->addSql('DROP TABLE IF EXISTS bons_commande');
        $this->addSql('DROP TABLE IF EXISTS fournisseurs');
        $this->addSql('DROP TABLE IF EXISTS triages');
        $this->addSql('DROP TABLE IF EXISTS niveaux_triage');
        $this->addSql('DROP TABLE IF EXISTS rapports_operatoires');
        $this->addSql('DROP TABLE IF EXISTS equipes_chirurgicales');
        $this->addSql('DROP TABLE IF EXISTS planning_operatoire');
        $this->addSql('DROP TABLE IF EXISTS salles_operations');
        $this->addSql('DROP TABLE IF EXISTS demandes_interventions');
        $this->addSql('DROP TABLE IF EXISTS types_interventions');
        $this->addSql('DROP TABLE IF EXISTS fichiers_dicom');
        $this->addSql('DROP TABLE IF EXISTS rapports_radiologiques');
        $this->addSql('DROP TABLE IF EXISTS examens_imagerie');
        $this->addSql('DROP TABLE IF EXISTS lignes_ordonnances_imagerie');
        $this->addSql('DROP TABLE IF EXISTS ordonnances_imagerie');
        $this->addSql('DROP TABLE IF EXISTS types_imagerie');
        $this->addSql('DROP TABLE IF EXISTS resultats_labo');
        $this->addSql('DROP TABLE IF EXISTS prelevements');
        $this->addSql('DROP TABLE IF EXISTS lignes_ordonnances_labo');
        $this->addSql('DROP TABLE IF EXISTS ordonnances_labo');
        $this->addSql('DROP TABLE IF EXISTS panel_examens');
        $this->addSql('DROP TABLE IF EXISTS panels_examens');
        $this->addSql('DROP TABLE IF EXISTS types_examens_labo');
        $this->addSql('DROP TABLE IF EXISTS lignes_distributions');
        $this->addSql('DROP TABLE IF EXISTS distributions_pharmacie');
        $this->addSql('DROP TABLE IF EXISTS mouvements_stock');
        $this->addSql('DROP TABLE IF EXISTS stocks_pharmacie');
        $this->addSql('DROP TABLE IF EXISTS administrations_medicaments');
        $this->addSql('DROP TABLE IF EXISTS lignes_prescriptions');
        $this->addSql('DROP TABLE IF EXISTS prescriptions');
        $this->addSql('DROP TABLE IF EXISTS interactions_medicamenteuses');
        $this->addSql('DROP TABLE IF EXISTS medicaments');
        $this->addSql('DROP TABLE IF EXISTS notes_infirmieres');
        $this->addSql('DROP TABLE IF EXISTS consultations');
        $this->addSql('DROP TABLE IF EXISTS transferts_patients');
        $this->addSql('DROP TABLE IF EXISTS admissions');
        $this->addSql('DROP TABLE IF EXISTS tarifs_lits');
        $this->addSql('DROP TABLE IF EXISTS lits');
        $this->addSql('DROP TABLE IF EXISTS rendez_vous');
        $this->addSql('DROP TABLE IF EXISTS creneaux_consultation');
        $this->addSql('DROP TABLE IF EXISTS assurances_patients');
        $this->addSql('DROP TABLE IF EXISTS dossiers_medicaux');
        $this->addSql('DROP TABLE IF EXISTS patients');
        $this->addSql('DROP TABLE IF EXISTS conventions_assurance');
        $this->addSql('DROP TABLE IF EXISTS parametres_configuration');
        $this->addSql('DROP TABLE IF EXISTS logs_audit');
        $this->addSql('DROP TABLE IF EXISTS affectations_utilisateurs');
        $this->addSql('DROP TABLE IF EXISTS utilisateurs');
        $this->addSql('DROP TABLE IF EXISTS profils_utilisateurs');
        $this->addSql('DROP TABLE IF EXISTS role_permissions');
        $this->addSql('DROP TABLE IF EXISTS permissions');
        $this->addSql('DROP TABLE IF EXISTS roles');
        $this->addSql('DROP TABLE IF EXISTS specialites');
        $this->addSql('DROP TABLE IF EXISTS services');
        $this->addSql('DROP TABLE IF EXISTS hopitaux');
        $this->addSql('DROP TABLE IF EXISTS taux_tva');
        $this->addSql('DROP TABLE IF EXISTS modes_paiement');
        $this->addSql('DROP TABLE IF EXISTS devises');
    }
}
