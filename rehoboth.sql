-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 03 jan. 2026 à 08:16
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `rehoboth-h`
--
DROP DATABASE IF EXISTS `rehoboth-h`;
CREATE DATABASE IF NOT EXISTS `rehoboth-h` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `rehoboth-h`;

-- --------------------------------------------------------

--
-- Structure de la table `actes_medicaux`
--

DROP TABLE IF EXISTS `actes_medicaux`;
CREATE TABLE IF NOT EXISTS `actes_medicaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `code_acte` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_acte` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `code_ccam` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_cim` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix_acte` decimal(10,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `taux_tva_id` int DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_acte`),
  KEY `devise_id` (`devise_id`),
  KEY `taux_tva_id` (`taux_tva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `administrations_medicaments`
--

DROP TABLE IF EXISTS `administrations_medicaments`;
CREATE TABLE IF NOT EXISTS `administrations_medicaments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ligne_prescription_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `infirmier_id` int NOT NULL,
  `date_administration` datetime NOT NULL,
  `quantite_administree` decimal(10,2) DEFAULT NULL,
  `unite_quantite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `voie_administration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_injection` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `effet_secondaire_observe` tinyint(1) DEFAULT '0',
  `description_effet_secondaire` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ligne_prescription` (`ligne_prescription_id`),
  KEY `idx_admission` (`admission_id`),
  KEY `idx_infirmier` (`infirmier_id`),
  KEY `idx_date` (`date_administration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `admissions`
--

DROP TABLE IF EXISTS `admissions`;
CREATE TABLE IF NOT EXISTS `admissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `service_id` int NOT NULL,
  `lit_id` int DEFAULT NULL,
  `medecin_id` int NOT NULL,
  `numero_admission` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_admission` datetime NOT NULL,
  `date_sortie` datetime DEFAULT NULL,
  `type_admission` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Programmée, Urgence, Transfert',
  `motif_admission` text COLLATE utf8mb4_unicode_ci,
  `diagnostic_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnostic_secondaire` text COLLATE utf8mb4_unicode_ci,
  `medecin_sortie_id` int DEFAULT NULL,
  `raison_sortie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Guérison, Décès, Transfert, Départ volontaire',
  `notes_sortie` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Admis, Hospitalisé, Sortie, Décédé',
  `duree_sejour` int DEFAULT NULL COMMENT 'En jours',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_admission` (`hopital_id`,`numero_admission`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_date_admission` (`date_admission`),
  KEY `idx_statut` (`statut`),
  KEY `lit_id` (`lit_id`),
  KEY `medecin_id` (`medecin_id`),
  KEY `medecin_sortie_id` (`medecin_sortie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `affectations_utilisateurs`
--

DROP TABLE IF EXISTS `affectations_utilisateurs`;
CREATE TABLE IF NOT EXISTS `affectations_utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `service_id` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `pourcentage_temps` decimal(5,2) DEFAULT NULL COMMENT 'Pourcentage de temps affecté',
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_service` (`utilisateur_id`,`service_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `archives`
--

DROP TABLE IF EXISTS `archives`;
CREATE TABLE IF NOT EXISTS `archives` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `numero_archive` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_archivage` datetime NOT NULL,
  `type_archivage` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dossier complet, Dossier partiel, Documents',
  `raison_archivage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Fin de traitement, Décès, Transfert, Inactivité',
  `localisation_archive` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `support_archive` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Physique, Numérique, Hybride',
  `duree_conservation` int DEFAULT NULL COMMENT 'En années',
  `date_destruction_prevue` date DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivé, En attente destruction, Détruit',
  `utilisateur_archivage_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_archive` (`hopital_id`,`numero_archive`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `utilisateur_archivage_id` (`utilisateur_archivage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `assurances_patients`
--

DROP TABLE IF EXISTS `assurances_patients`;
CREATE TABLE IF NOT EXISTS `assurances_patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `convention_id` int NOT NULL,
  `numero_police` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `beneficiaire_nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beneficiaire_lien` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taux_couverture` decimal(5,2) DEFAULT NULL,
  `montant_franchise` decimal(10,2) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `convention_id` (`convention_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bons_commande`
--

DROP TABLE IF EXISTS `bons_commande`;
CREATE TABLE IF NOT EXISTS `bons_commande` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `fournisseur_id` int NOT NULL,
  `numero_bon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_commande` date NOT NULL,
  `utilisateur_id` int NOT NULL,
  `date_livraison_prevue` date DEFAULT NULL,
  `date_livraison_reelle` date DEFAULT NULL,
  `montant_total` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validée, Commandée, Livrée, Facturée, Annulée',
  `notes_commande` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_bon` (`hopital_id`,`numero_bon`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_fournisseur` (`fournisseur_id`),
  KEY `idx_date` (`date_commande`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bulletins_paie`
--

DROP TABLE IF EXISTS `bulletins_paie`;
CREATE TABLE IF NOT EXISTS `bulletins_paie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_bulletin` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode_debut` date NOT NULL,
  `periode_fin` date NOT NULL,
  `salaire_brut` decimal(12,2) DEFAULT NULL,
  `cotisations_sociales` decimal(12,2) DEFAULT NULL,
  `impot_revenu` decimal(12,2) DEFAULT NULL,
  `autres_deductions` decimal(12,2) DEFAULT NULL,
  `salaire_net` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validé, Payé',
  `date_paiement` date DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_bulletin` (`hopital_id`,`numero_bulletin`),
  KEY `idx_utilisateur` (`utilisateur_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `certifications`
--

DROP TABLE IF EXISTS `certifications`;
CREATE TABLE IF NOT EXISTS `certifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `nom_certification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organisme_certification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_obtention` date NOT NULL,
  `date_expiration` date DEFAULT NULL,
  `numero_certification` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Valide, Expiré, Suspendu',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `hopital_id` (`hopital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comptes_portail_patient`
--

DROP TABLE IF EXISTS `comptes_portail_patient`;
CREATE TABLE IF NOT EXISTS `comptes_portail_patient` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `email_portail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe_portail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation_compte` datetime NOT NULL,
  `date_derniere_connexion` datetime DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_portail` (`email_portail`),
  UNIQUE KEY `unique_patient_hopital` (`patient_id`,`hopital_id`),
  KEY `hopital_id` (`hopital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conges`
--

DROP TABLE IF EXISTS `conges`;
CREATE TABLE IF NOT EXISTS `conges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `type_conge` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Annuel, Maladie, Maternité, Paternité, Autre',
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `nombre_jours` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Demandé, Approuvé, Rejeté, En cours, Terminé',
  `motif` text COLLATE utf8mb4_unicode_ci,
  `approbateur_id` int DEFAULT NULL,
  `date_approbation` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_utilisateur` (`utilisateur_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `approbateur_id` (`approbateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `consentements_recherche`
--

DROP TABLE IF EXISTS `consentements_recherche`;
CREATE TABLE IF NOT EXISTS `consentements_recherche` (
  `id` int NOT NULL AUTO_INCREMENT,
  `projet_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `date_consentement` date NOT NULL,
  `consentement_donne` tinyint(1) DEFAULT '0',
  `signature_patient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_temoin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_retrait_consentement` date DEFAULT NULL,
  `raison_retrait` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_projet_patient` (`projet_id`,`patient_id`),
  KEY `patient_id` (`patient_id`),
  KEY `hopital_id` (`hopital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `consultations`
--

DROP TABLE IF EXISTS `consultations`;
CREATE TABLE IF NOT EXISTS `consultations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `service_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `rendez_vous_id` int DEFAULT NULL,
  `admission_id` int DEFAULT NULL,
  `date_consultation` datetime NOT NULL,
  `motif_consultation` text COLLATE utf8mb4_unicode_ci,
  `diagnostic_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnostic_secondaire` text COLLATE utf8mb4_unicode_ci,
  `plan_traitement` text COLLATE utf8mb4_unicode_ci,
  `observations_cliniques` text COLLATE utf8mb4_unicode_ci,
  `examen_physique` text COLLATE utf8mb4_unicode_ci,
  `tension_arterielle` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequence_cardiaque` int DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `poids` decimal(6,2) DEFAULT NULL,
  `taille` decimal(5,2) DEFAULT NULL,
  `imc` decimal(5,2) DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Complétée, Brouillon, Annulée',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_medecin` (`medecin_id`),
  KEY `idx_date` (`date_consultation`),
  KEY `service_id` (`service_id`),
  KEY `hopital_id` (`hopital_id`),
  KEY `rendez_vous_id` (`rendez_vous_id`),
  KEY `admission_id` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contrats`
--

DROP TABLE IF EXISTS `contrats`;
CREATE TABLE IF NOT EXISTS `contrats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_contrat` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_contrat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CDI, CDD, Stage, Consultant',
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `poste` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_base` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Actif, Suspendu, Terminé',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_contrat` (`hopital_id`,`numero_contrat`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contrats_maintenance`
--

DROP TABLE IF EXISTS `contrats_maintenance`;
CREATE TABLE IF NOT EXISTS `contrats_maintenance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `equipement_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `fournisseur_id` int NOT NULL,
  `numero_contrat` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_maintenance` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Préventive, Corrective, Mixte',
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `frequence_maintenance` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mensuelle, Trimestrielle, Annuelle',
  `cout_maintenance` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Actif, Expiré, Suspendu',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_contrat` (`hopital_id`,`numero_contrat`),
  KEY `equipement_id` (`equipement_id`),
  KEY `fournisseur_id` (`fournisseur_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conventions_assurance`
--

DROP TABLE IF EXISTS `conventions_assurance`;
CREATE TABLE IF NOT EXISTS `conventions_assurance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_couverture` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Assurance, Mutuelle, Privé',
  `pourcentage_couverture` decimal(5,2) DEFAULT NULL,
  `contact_personne` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_convention` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taux_commission` decimal(5,2) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `creneaux_consultation`
--

DROP TABLE IF EXISTS `creneaux_consultation`;
CREATE TABLE IF NOT EXISTS `creneaux_consultation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medecin_id` int NOT NULL,
  `service_id` int NOT NULL,
  `date_consultation` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `nombre_places` int DEFAULT '1',
  `places_disponibles` int DEFAULT '1',
  `type_consultation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Consultation, Suivi, Urgence',
  `lieu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medecin` (`medecin_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_date` (`date_consultation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_interventions`
--

DROP TABLE IF EXISTS `demandes_interventions`;
CREATE TABLE IF NOT EXISTS `demandes_interventions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `consultation_id` int DEFAULT NULL,
  `admission_id` int DEFAULT NULL,
  `numero_demande` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_demande` datetime NOT NULL,
  `intervention_id` int NOT NULL,
  `urgence` tinyint(1) DEFAULT '0',
  `diagnostic_preoperatoire` text COLLATE utf8mb4_unicode_ci,
  `antecedents_chirurgicaux` text COLLATE utf8mb4_unicode_ci,
  `medicaments_actuels` text COLLATE utf8mb4_unicode_ci,
  `allergies` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validée, Programmée, Réalisée, Annul��e',
  `date_intervention_prevue` date DEFAULT NULL,
  `notes_demande` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_demande` (`hopital_id`,`numero_demande`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_medecin` (`medecin_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `admission_id` (`admission_id`),
  KEY `intervention_id` (`intervention_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `devises`
--

DROP TABLE IF EXISTS `devises`;
CREATE TABLE IF NOT EXISTS `devises` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbole` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taux_change` decimal(10,4) DEFAULT '1.0000',
  `devise_reference` tinyint(1) DEFAULT '0',
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `distributions_pharmacie`
--

DROP TABLE IF EXISTS `distributions_pharmacie`;
CREATE TABLE IF NOT EXISTS `distributions_pharmacie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `service_id` int NOT NULL,
  `numero_bon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_distribution` datetime NOT NULL,
  `pharmacien_id` int NOT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validée, Distribuée, Reçue',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_bon` (`hopital_id`,`numero_bon`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_service` (`service_id`),
  KEY `pharmacien_id` (`pharmacien_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20240101000000CreateHISDatabase', '2026-01-03 03:19:35', 5948);

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `utilisateur_id` int DEFAULT NULL,
  `type_document` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ordonnance, Rapport, Certificat, Feuille de sortie, etc.',
  `nom_document` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `chemin_fichier` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_fichier` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille_fichier` int DEFAULT NULL COMMENT 'En octets',
  `date_document` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validé, Signé, Archivé',
  `confidentialite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public, Confidentiel, Très confidentiel',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_type` (`type_document`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossiers_medicaux`
--

DROP TABLE IF EXISTS `dossiers_medicaux`;
CREATE TABLE IF NOT EXISTS `dossiers_medicaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_dme` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_ouverture` date NOT NULL,
  `date_fermeture` date DEFAULT NULL,
  `medecin_referent_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Actif, Archivé, Suspendu',
  `notes_generales` longtext COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_dme` (`hopital_id`,`numero_dme`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `medecin_referent_id` (`medecin_referent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipements`
--

DROP TABLE IF EXISTS `equipements`;
CREATE TABLE IF NOT EXISTS `equipements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `code_equipement` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_equipement` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_equipement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Biomédical, Informatique, Mobilier, etc.',
  `marque` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modele` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_serie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_acquisition` date DEFAULT NULL,
  `date_mise_en_service` date DEFAULT NULL,
  `prix_acquisition` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `duree_vie_utile` int DEFAULT NULL COMMENT 'En années',
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Actif, Maintenance, Hors service, Retiré',
  `localisation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fournisseur_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_equipement`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_service` (`service_id`),
  KEY `fournisseur_id` (`fournisseur_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipes_chirurgicales`
--

DROP TABLE IF EXISTS `equipes_chirurgicales`;
CREATE TABLE IF NOT EXISTS `equipes_chirurgicales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `planning_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `role_equipe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chirurgien, Assistant, Anesthésiste, Infirmier, etc.',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_planning_user` (`planning_id`,`utilisateur_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `examens_imagerie`
--

DROP TABLE IF EXISTS `examens_imagerie`;
CREATE TABLE IF NOT EXISTS `examens_imagerie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordonnance_id` int NOT NULL,
  `imagerie_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_examen` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_examen` datetime NOT NULL,
  `technicien_id` int NOT NULL,
  `modalite` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_anatomique` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observations_technique` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Programmé, En cours, Complété, Rejeté',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_examen` (`hopital_id`,`numero_examen`),
  KEY `idx_ordonnance` (`ordonnance_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `imagerie_id` (`imagerie_id`),
  KEY `technicien_id` (`technicien_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `exports_rapports`
--

DROP TABLE IF EXISTS `exports_rapports`;
CREATE TABLE IF NOT EXISTS `exports_rapports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rapport_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `format_export` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'PDF, CSV, Excel, JSON',
  `chemin_fichier` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille_fichier` int DEFAULT NULL COMMENT 'En octets',
  `date_export` datetime NOT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Généré, Téléchargé, Expiré',
  `date_expiration` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rapport` (`rapport_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

DROP TABLE IF EXISTS `factures`;
CREATE TABLE IF NOT EXISTS `factures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `admission_id` int DEFAULT NULL,
  `numero_facture` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_facture` date NOT NULL,
  `date_echeance` date DEFAULT NULL,
  `montant_total` decimal(12,2) DEFAULT NULL,
  `montant_assurance` decimal(12,2) DEFAULT NULL,
  `montant_patient` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validée, Envoyée, Payée, Partiellement payée, Impayée',
  `type_facture` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Consultation, Hospitalisation, Acte, Autre',
  `notes_facture` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_facture` (`hopital_id`,`numero_facture`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_date` (`date_facture`),
  KEY `idx_statut` (`statut`),
  KEY `admission_id` (`admission_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `feuilles_sortie`
--

DROP TABLE IF EXISTS `feuilles_sortie`;
CREATE TABLE IF NOT EXISTS `feuilles_sortie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admission_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `numero_feuille` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_sortie` date NOT NULL,
  `diagnostic_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diagnostic_secondaire` text COLLATE utf8mb4_unicode_ci,
  `procedures_effectuees` text COLLATE utf8mb4_unicode_ci,
  `medicaments_sortie` text COLLATE utf8mb4_unicode_ci,
  `recommandations_sortie` text COLLATE utf8mb4_unicode_ci,
  `suivi_recommande` text COLLATE utf8mb4_unicode_ci,
  `date_prochain_rendez_vous` date DEFAULT NULL,
  `medecin_suivi_id` int DEFAULT NULL,
  `restrictions_activites` text COLLATE utf8mb4_unicode_ci,
  `regime_alimentaire` text COLLATE utf8mb4_unicode_ci,
  `soins_plaies` text COLLATE utf8mb4_unicode_ci,
  `signes_alerte` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_feuille` (`hopital_id`,`numero_feuille`),
  KEY `idx_admission` (`admission_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `medecin_id` (`medecin_id`),
  KEY `medecin_suivi_id` (`medecin_suivi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fichiers_dicom`
--

DROP TABLE IF EXISTS `fichiers_dicom`;
CREATE TABLE IF NOT EXISTS `fichiers_dicom` (
  `id` int NOT NULL AUTO_INCREMENT,
  `examen_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_serie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_instance` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chemin_fichier` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille_fichier` int DEFAULT NULL COMMENT 'En octets',
  `format_fichier` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation_dicom` datetime DEFAULT NULL,
  `date_archivage` datetime DEFAULT NULL,
  `localisation_pacs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_examen` (`examen_id`),
  KEY `idx_hopital` (`hopital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formations`
--

DROP TABLE IF EXISTS `formations`;
CREATE TABLE IF NOT EXISTS `formations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `code_formation` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_formation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_formation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Interne, Externe, E-learning',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `formateur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_heures` int DEFAULT NULL,
  `lieu_formation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cout_formation` decimal(10,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_formation`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fournisseurs`
--

DROP TABLE IF EXISTS `fournisseurs`;
CREATE TABLE IF NOT EXISTS `fournisseurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `code_fournisseur` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_fournisseur` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_fournisseur` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pharmacie, Équipement, Consommables, etc.',
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_postal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_personne` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_siret` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_tva` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditions_paiement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delai_livraison` int DEFAULT NULL COMMENT 'En jours',
  `logo_fournisseur` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_fournisseur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `hopitaux`
--

DROP TABLE IF EXISTS `hopitaux`;
CREATE TABLE IF NOT EXISTS `hopitaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_postal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `directeur_id` int DEFAULT NULL,
  `type_hopital` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public, Privé, Clinique',
  `nombre_lits` int DEFAULT NULL,
  `logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icone_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur_primaire` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur_secondaire` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_siret` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_tva` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`),
  KEY `idx_actif` (`actif`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `hopitaux`
--

INSERT INTO `hopitaux` (`id`, `code`, `nom`, `adresse`, `ville`, `code_postal`, `telephone`, `email`, `directeur_id`, `type_hopital`, `nombre_lits`, `logo_url`, `icone_url`, `couleur_primaire`, `couleur_secondaire`, `site_web`, `numero_siret`, `numero_tva`, `actif`, `date_creation`, `date_modification`) VALUES
(1, 'REHOBOTH', 'Rehoboth Hospital', '123 Rue de la Santé', 'Paris', '75000', '+33123456789', 'contact@rehoboth.com', NULL, 'Hôpital Général', 500, 'https://example.com/logo.png', 'https://example.com/icon.png', '#0066CC', '#00CC99', 'https://www.rehoboth.com', '12345678901234', 'FR12345678901', 1, '2026-01-03 09:01:02', '2026-01-03 09:01:02');

-- --------------------------------------------------------

--
-- Structure de la table `indicateurs_qualite`
--

DROP TABLE IF EXISTS `indicateurs_qualite`;
CREATE TABLE IF NOT EXISTS `indicateurs_qualite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `code_indicateur` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_indicateur` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_indicateur` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Infection, Mortalité, Réadmission, Satisfaction, etc.',
  `unite_mesure` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valeur_cible` decimal(10,2) DEFAULT NULL,
  `frequence_mesure` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Quotidienne, Hebdomadaire, Mensuelle',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_indicateur`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `integrations_externes`
--

DROP TABLE IF EXISTS `integrations_externes`;
CREATE TABLE IF NOT EXISTS `integrations_externes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `code_integration` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_integration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_integration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'HL7, FHIR, API REST, SFTP, etc.',
  `url_endpoint` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authentification_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'API Key, OAuth2, Basic Auth',
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Active, Inactive, Erreur',
  `derniere_synchronisation` datetime DEFAULT NULL,
  `frequence_synchronisation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_integration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `interactions_medicamenteuses`
--

DROP TABLE IF EXISTS `interactions_medicamenteuses`;
CREATE TABLE IF NOT EXISTS `interactions_medicamenteuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medicament_1_id` int NOT NULL,
  `medicament_2_id` int NOT NULL,
  `niveau_severite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Légère, Modérée, Grave, Contre-indication',
  `description` text COLLATE utf8mb4_unicode_ci,
  `recommandation` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_interaction` (`medicament_1_id`,`medicament_2_id`),
  KEY `medicament_2_id` (`medicament_2_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `interventions_maintenance`
--

DROP TABLE IF EXISTS `interventions_maintenance`;
CREATE TABLE IF NOT EXISTS `interventions_maintenance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `equipement_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_intervention` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_intervention` date NOT NULL,
  `type_intervention` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Préventive, Corrective, Inspection',
  `technicien_id` int NOT NULL,
  `description_intervention` text COLLATE utf8mb4_unicode_ci,
  `pieces_remplacees` text COLLATE utf8mb4_unicode_ci,
  `duree_intervention` int DEFAULT NULL COMMENT 'En heures',
  `cout_intervention` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Programmée, En cours, Complétée, Annulée',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_intervention` (`hopital_id`,`numero_intervention`),
  KEY `idx_equipement` (`equipement_id`),
  KEY `idx_date` (`date_intervention`),
  KEY `technicien_id` (`technicien_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lignes_bons_commande`
--

DROP TABLE IF EXISTS `lignes_bons_commande`;
CREATE TABLE IF NOT EXISTS `lignes_bons_commande` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bon_commande_id` int NOT NULL,
  `medicament_id` int DEFAULT NULL,
  `description_article` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantite` int NOT NULL,
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `montant_ligne` decimal(12,2) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `bon_commande_id` (`bon_commande_id`),
  KEY `medicament_id` (`medicament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lignes_distributions`
--

DROP TABLE IF EXISTS `lignes_distributions`;
CREATE TABLE IF NOT EXISTS `lignes_distributions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `distribution_id` int NOT NULL,
  `medicament_id` int NOT NULL,
  `quantite` int NOT NULL,
  `lot_numero` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `distribution_id` (`distribution_id`),
  KEY `medicament_id` (`medicament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lignes_factures`
--

DROP TABLE IF EXISTS `lignes_factures`;
CREATE TABLE IF NOT EXISTS `lignes_factures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `facture_id` int NOT NULL,
  `acte_id` int DEFAULT NULL,
  `description_ligne` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantite` int DEFAULT '1',
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `montant_ligne` decimal(12,2) DEFAULT NULL,
  `taux_tva` decimal(5,2) DEFAULT NULL,
  `montant_tva` decimal(12,2) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `facture_id` (`facture_id`),
  KEY `acte_id` (`acte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lignes_ordonnances_imagerie`
--

DROP TABLE IF EXISTS `lignes_ordonnances_imagerie`;
CREATE TABLE IF NOT EXISTS `lignes_ordonnances_imagerie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordonnance_id` int NOT NULL,
  `imagerie_id` int NOT NULL,
  `quantite` int DEFAULT '1',
  `priorite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Routine, Urgent',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ordonnance_id` (`ordonnance_id`),
  KEY `imagerie_id` (`imagerie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lignes_ordonnances_labo`
--

DROP TABLE IF EXISTS `lignes_ordonnances_labo`;
CREATE TABLE IF NOT EXISTS `lignes_ordonnances_labo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordonnance_id` int NOT NULL,
  `examen_id` int NOT NULL,
  `panel_id` int DEFAULT NULL,
  `quantite` int DEFAULT '1',
  `priorite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Routine, Urgent',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ordonnance_id` (`ordonnance_id`),
  KEY `examen_id` (`examen_id`),
  KEY `panel_id` (`panel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lignes_prescriptions`
--

DROP TABLE IF EXISTS `lignes_prescriptions`;
CREATE TABLE IF NOT EXISTS `lignes_prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `medicament_id` int NOT NULL,
  `quantite` decimal(10,2) NOT NULL,
  `unite_quantite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequence_administration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '1x/jour, 2x/jour, etc.',
  `voie_administration` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Orale, IV, IM, SC, etc.',
  `duree_traitement` int DEFAULT NULL COMMENT 'En jours',
  `instructions_speciales` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_prescription` (`prescription_id`),
  KEY `idx_medicament` (`medicament_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lits`
--

DROP TABLE IF EXISTS `lits`;
CREATE TABLE IF NOT EXISTS `lits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_lit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_lit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Standard, Semi-privé, Privé, Soins intensifs',
  `etage` int DEFAULT NULL,
  `chambre` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Disponible, Occupé, Maintenance, Fermé',
  `date_derniere_maintenance` date DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_lit` (`hopital_id`,`numero_lit`),
  KEY `idx_service` (`service_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logs_audit`
--

DROP TABLE IF EXISTS `logs_audit`;
CREATE TABLE IF NOT EXISTS `logs_audit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int DEFAULT NULL,
  `hopital_id` int DEFAULT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entite` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entite_id` int DEFAULT NULL,
  `ancienne_valeur` longtext COLLATE utf8mb4_unicode_ci,
  `nouvelle_valeur` longtext COLLATE utf8mb4_unicode_ci,
  `adresse_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_utilisateur` (`utilisateur_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_module` (`module`),
  KEY `idx_date` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logs_integrations`
--

DROP TABLE IF EXISTS `logs_integrations`;
CREATE TABLE IF NOT EXISTS `logs_integrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `integration_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `date_log` datetime NOT NULL,
  `type_log` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Info, Avertissement, Erreur',
  `message_log` text COLLATE utf8mb4_unicode_ci,
  `donnees_envoyees` longtext COLLATE utf8mb4_unicode_ci,
  `donnees_recues` longtext COLLATE utf8mb4_unicode_ci,
  `statut_reponse` int DEFAULT NULL COMMENT 'Code HTTP',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_integration` (`integration_id`),
  KEY `idx_date` (`date_log`),
  KEY `hopital_id` (`hopital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `medicaments`
--

DROP TABLE IF EXISTS `medicaments`;
CREATE TABLE IF NOT EXISTS `medicaments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_medicament` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_commercial` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_generique` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forme_pharmaceutique` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Comprimé, Injection, Sirop, etc.',
  `dosage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unite_dosage` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fabricant` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_atc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_cip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix_unitaire` decimal(10,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `taux_tva_id` int DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_medicament` (`code_medicament`),
  KEY `idx_code` (`code_medicament`),
  KEY `idx_nom` (`nom_commercial`),
  KEY `devise_id` (`devise_id`),
  KEY `taux_tva_id` (`taux_tva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages_securises`
--

DROP TABLE IF EXISTS `messages_securises`;
CREATE TABLE IF NOT EXISTS `messages_securises` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `sujet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenu_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_message` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Consultation, Résultat, Rappel, Autre',
  `date_envoi` datetime NOT NULL,
  `date_lecture` datetime DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Envoyé, Lu, Répondu, Archivé',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mesures_indicateurs`
--

DROP TABLE IF EXISTS `mesures_indicateurs`;
CREATE TABLE IF NOT EXISTS `mesures_indicateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `indicateur_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `valeur_mesuree` decimal(10,2) DEFAULT NULL,
  `date_mesure` date NOT NULL,
  `notes_mesure` text COLLATE utf8mb4_unicode_ci,
  `utilisateur_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_indicateur` (`indicateur_id`),
  KEY `idx_date` (`date_mesure`),
  KEY `hopital_id` (`hopital_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `modes_paiement`
--

DROP TABLE IF EXISTS `modes_paiement`;
CREATE TABLE IF NOT EXISTS `modes_paiement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_paiement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Espèces, Chèque, Carte, Virement, Assurance, Autre',
  `frais_transaction` decimal(5,2) DEFAULT NULL COMMENT 'En pourcentage',
  `delai_encaissement` int DEFAULT NULL COMMENT 'En jours',
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `mouvements_stock`
--

DROP TABLE IF EXISTS `mouvements_stock`;
CREATE TABLE IF NOT EXISTS `mouvements_stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stock_id` int NOT NULL,
  `type_mouvement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Entrée, Sortie, Ajustement, Perte',
  `quantite` int NOT NULL,
  `motif` text COLLATE utf8mb4_unicode_ci,
  `utilisateur_id` int DEFAULT NULL,
  `reference_document` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_mouvement` datetime NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stock` (`stock_id`),
  KEY `idx_date` (`date_mouvement`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `niveaux_triage`
--

DROP TABLE IF EXISTS `niveaux_triage`;
CREATE TABLE IF NOT EXISTS `niveaux_triage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `couleur` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icone` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priorite` int DEFAULT NULL,
  `delai_prise_en_charge` int DEFAULT NULL COMMENT 'En minutes',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notes_infirmieres`
--

DROP TABLE IF EXISTS `notes_infirmieres`;
CREATE TABLE IF NOT EXISTS `notes_infirmieres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admission_id` int NOT NULL,
  `infirmier_id` int NOT NULL,
  `date_note` datetime NOT NULL,
  `type_note` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Observation, Ronde, Incident, Alerte',
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `signes_vitaux_tension` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signes_vitaux_frequence_cardiaque` int DEFAULT NULL,
  `signes_vitaux_temperature` decimal(4,1) DEFAULT NULL,
  `signes_vitaux_frequence_respiratoire` int DEFAULT NULL,
  `signes_vitaux_saturation_o2` decimal(5,2) DEFAULT NULL,
  `observations_generales` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admission` (`admission_id`),
  KEY `idx_infirmier` (`infirmier_id`),
  KEY `idx_date` (`date_note`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `type_notification` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Alerte, Rappel, Information, Urgence',
  `titre_notification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenu_notification` text COLLATE utf8mb4_unicode_ci,
  `priorite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Basse, Normale, Haute, Critique',
  `date_notification` datetime NOT NULL,
  `date_lecture` datetime DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Non lue, Lue, Archivée',
  `lien_action` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_utilisateur` (`utilisateur_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_date` (`date_notification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ordonnances_imagerie`
--

DROP TABLE IF EXISTS `ordonnances_imagerie`;
CREATE TABLE IF NOT EXISTS `ordonnances_imagerie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `consultation_id` int DEFAULT NULL,
  `admission_id` int DEFAULT NULL,
  `numero_ordonnance` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_ordonnance` datetime NOT NULL,
  `motif_examen` text COLLATE utf8mb4_unicode_ci,
  `urgence` tinyint(1) DEFAULT '0',
  `zone_anatomique` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `antecedents_pertinents` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validée, Programmée, Réalisée, Rapportée, Annulée',
  `notes_ordonnance` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_ordonnance` (`hopital_id`,`numero_ordonnance`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_medecin` (`medecin_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `admission_id` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ordonnances_labo`
--

DROP TABLE IF EXISTS `ordonnances_labo`;
CREATE TABLE IF NOT EXISTS `ordonnances_labo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `consultation_id` int DEFAULT NULL,
  `admission_id` int DEFAULT NULL,
  `numero_ordonnance` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_ordonnance` datetime NOT NULL,
  `motif_examen` text COLLATE utf8mb4_unicode_ci,
  `urgence` tinyint(1) DEFAULT '0',
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validée, Reçue au labo, En cours, Complétée, Annulée',
  `notes_ordonnance` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_ordonnance` (`hopital_id`,`numero_ordonnance`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_medecin` (`medecin_id`),
  KEY `idx_date` (`date_ordonnance`),
  KEY `consultation_id` (`consultation_id`),
  KEY `admission_id` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `facture_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_paiement` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_paiement` date NOT NULL,
  `montant_paiement` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `mode_paiement_id` int DEFAULT NULL,
  `reference_paiement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taux_paiement` decimal(5,2) DEFAULT NULL,
  `frais_transaction` decimal(10,2) DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Enregistré, Validé, Annulé',
  `notes_paiement` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_paiement` (`hopital_id`,`numero_paiement`),
  KEY `idx_facture` (`facture_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_date` (`date_paiement`),
  KEY `devise_id` (`devise_id`),
  KEY `mode_paiement_id` (`mode_paiement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `panels_examens`
--

DROP TABLE IF EXISTS `panels_examens`;
CREATE TABLE IF NOT EXISTS `panels_examens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_panel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_panel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `prix_panel` decimal(10,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `taux_tva_id` int DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_panel` (`code_panel`),
  KEY `devise_id` (`devise_id`),
  KEY `taux_tva_id` (`taux_tva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `panel_examens`
--

DROP TABLE IF EXISTS `panel_examens`;
CREATE TABLE IF NOT EXISTS `panel_examens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `panel_id` int NOT NULL,
  `examen_id` int NOT NULL,
  `ordre` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_panel_examen` (`panel_id`,`examen_id`),
  KEY `examen_id` (`examen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parametres_configuration`
--

DROP TABLE IF EXISTS `parametres_configuration`;
CREATE TABLE IF NOT EXISTS `parametres_configuration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int DEFAULT NULL,
  `cle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valeur` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'string, int, boolean, json, decimal',
  `description` text COLLATE utf8mb4_unicode_ci,
  `categorie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Général, Affichage, Paiement, Email, SMS, Sécurité, etc.',
  `logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icone_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur_primaire` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur_secondaire` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taux_paiement_defaut` decimal(5,2) DEFAULT NULL,
  `taux_tva_defaut` decimal(5,2) DEFAULT NULL,
  `devise_defaut_id` int DEFAULT NULL,
  `mode_paiement_defaut_id` int DEFAULT NULL,
  `email_expediteur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_support` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone_support` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_logo_email` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_email` text COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) DEFAULT '1',
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_cle` (`hopital_id`,`cle`),
  KEY `devise_defaut_id` (`devise_defaut_id`),
  KEY `mode_paiement_defaut_id` (`mode_paiement_defaut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participations_formations`
--

DROP TABLE IF EXISTS `participations_formations`;
CREATE TABLE IF NOT EXISTS `participations_formations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formation_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `date_participation` date DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Inscrit, Participé, Absent, Complété',
  `note_evaluation` decimal(5,2) DEFAULT NULL,
  `certificat_obtenu` tinyint(1) DEFAULT '0',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_formation_user` (`formation_id`,`utilisateur_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `patients`
--

DROP TABLE IF EXISTS `patients`;
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `numero_dossier` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_naissance` date NOT NULL,
  `sexe` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M ou F',
  `numero_identite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_identite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CNI, Passeport, Permis',
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_postal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_urgence_nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_urgence_telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_urgence_lien` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `groupe_sanguin` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allergies` text COLLATE utf8mb4_unicode_ci,
  `antecedents_medicaux` text COLLATE utf8mb4_unicode_ci,
  `antecedents_chirurgicaux` text COLLATE utf8mb4_unicode_ci,
  `medicaments_actuels` text COLLATE utf8mb4_unicode_ci,
  `statut_civil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Célibataire, Marié, Divorcé, Veuf',
  `profession` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `langue_preference` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_patient` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_dossier` (`hopital_id`,`numero_dossier`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_nom_prenom` (`nom`,`prenom`),
  KEY `idx_numero_identite` (`numero_identite`),
  KEY `idx_date_naissance` (`date_naissance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES
(1, 'patients.consulter', 'Consulter les patients', NULL, 'patients', 'consulter', '2026-01-03 08:56:40'),
(2, 'patients.creer', 'Créer un patient', NULL, 'patients', 'creer', '2026-01-03 08:56:40'),
(3, 'patients.modifier', 'Modifier un patient', NULL, 'patients', 'modifier', '2026-01-03 08:56:40'),
(4, 'patients.supprimer', 'Supprimer un patient', NULL, 'patients', 'supprimer', '2026-01-03 08:56:40'),
(5, 'patients.exporter', 'Exporter les données patients', NULL, 'patients', 'exporter', '2026-01-03 08:56:40'),
(6, 'dossiers_medicaux.consulter', 'Consulter les dossiers médicaux', NULL, 'dossiers_medicaux', 'consulter', '2026-01-03 08:56:40'),
(7, 'dossiers_medicaux.creer', 'Créer un dossier médical', NULL, 'dossiers_medicaux', 'creer', '2026-01-03 08:56:40'),
(8, 'dossiers_medicaux.modifier', 'Modifier un dossier médical', NULL, 'dossiers_medicaux', 'modifier', '2026-01-03 08:56:40'),
(9, 'dossiers_medicaux.supprimer', 'Supprimer un dossier médical', NULL, 'dossiers_medicaux', 'supprimer', '2026-01-03 08:56:40'),
(10, 'admissions.consulter', 'Consulter les admissions', NULL, 'admissions', 'consulter', '2026-01-03 08:56:40'),
(11, 'admissions.creer', 'Créer une admission', NULL, 'admissions', 'creer', '2026-01-03 08:56:40'),
(12, 'admissions.modifier', 'Modifier une admission', NULL, 'admissions', 'modifier', '2026-01-03 08:56:40'),
(13, 'admissions.supprimer', 'Supprimer une admission', NULL, 'admissions', 'supprimer', '2026-01-03 08:56:40'),
(14, 'transferts.consulter', 'Consulter les transferts', NULL, 'transferts', 'consulter', '2026-01-03 08:56:40'),
(15, 'transferts.creer', 'Créer un transfert', NULL, 'transferts', 'creer', '2026-01-03 08:56:40'),
(16, 'transferts.modifier', 'Modifier un transfert', NULL, 'transferts', 'modifier', '2026-01-03 08:56:40'),
(17, 'sorties.consulter', 'Consulter les sorties', NULL, 'sorties', 'consulter', '2026-01-03 08:56:40'),
(18, 'sorties.creer', 'Créer une sortie', NULL, 'sorties', 'creer', '2026-01-03 08:56:40'),
(19, 'sorties.modifier', 'Modifier une sortie', NULL, 'sorties', 'modifier', '2026-01-03 08:56:40'),
(20, 'consultations.consulter', 'Consulter les consultations', NULL, 'consultations', 'consulter', '2026-01-03 08:56:40'),
(21, 'consultations.creer', 'Créer une consultation', NULL, 'consultations', 'creer', '2026-01-03 08:56:40'),
(22, 'consultations.modifier', 'Modifier une consultation', NULL, 'consultations', 'modifier', '2026-01-03 08:56:40'),
(23, 'consultations.supprimer', 'Supprimer une consultation', NULL, 'consultations', 'supprimer', '2026-01-03 08:56:40'),
(24, 'rendez_vous.consulter', 'Consulter les rendez-vous', NULL, 'rendez_vous', 'consulter', '2026-01-03 08:56:40'),
(25, 'rendez_vous.creer', 'Créer un rendez-vous', NULL, 'rendez_vous', 'creer', '2026-01-03 08:56:40'),
(26, 'rendez_vous.modifier', 'Modifier un rendez-vous', NULL, 'rendez_vous', 'modifier', '2026-01-03 08:56:40'),
(27, 'rendez_vous.annuler', 'Annuler un rendez-vous', NULL, 'rendez_vous', 'annuler', '2026-01-03 08:56:40'),
(28, 'rendez_vous.confirmer', 'Confirmer un rendez-vous', NULL, 'rendez_vous', 'confirmer', '2026-01-03 08:56:40'),
(29, 'creneaux.consulter', 'Consulter les créneaux', NULL, 'creneaux', 'consulter', '2026-01-03 08:56:40'),
(30, 'creneaux.creer', 'Créer un créneau', NULL, 'creneaux', 'creer', '2026-01-03 08:56:40'),
(31, 'creneaux.modifier', 'Modifier un créneau', NULL, 'creneaux', 'modifier', '2026-01-03 08:56:40'),
(32, 'creneaux.supprimer', 'Supprimer un créneau', NULL, 'creneaux', 'supprimer', '2026-01-03 08:56:40'),
(33, 'prescriptions.consulter', 'Consulter les prescriptions', NULL, 'prescriptions', 'consulter', '2026-01-03 08:56:40'),
(34, 'prescriptions.creer', 'Créer une prescription', NULL, 'prescriptions', 'creer', '2026-01-03 08:56:40'),
(35, 'prescriptions.modifier', 'Modifier une prescription', NULL, 'prescriptions', 'modifier', '2026-01-03 08:56:40'),
(36, 'prescriptions.valider', 'Valider une prescription', NULL, 'prescriptions', 'valider', '2026-01-03 08:56:40'),
(37, 'prescriptions.annuler', 'Annuler une prescription', NULL, 'prescriptions', 'annuler', '2026-01-03 08:56:40'),
(38, 'medicaments.consulter', 'Consulter les médicaments', NULL, 'medicaments', 'consulter', '2026-01-03 08:56:40'),
(39, 'medicaments.creer', 'Créer un médicament', NULL, 'medicaments', 'creer', '2026-01-03 08:56:40'),
(40, 'medicaments.modifier', 'Modifier un médicament', NULL, 'medicaments', 'modifier', '2026-01-03 08:56:40'),
(41, 'medicaments.supprimer', 'Supprimer un médicament', NULL, 'medicaments', 'supprimer', '2026-01-03 08:56:40'),
(42, 'administrations.consulter', 'Consulter les administrations', NULL, 'administrations', 'consulter', '2026-01-03 08:56:40'),
(43, 'administrations.creer', 'Enregistrer une administration', NULL, 'administrations', 'creer', '2026-01-03 08:56:40'),
(44, 'administrations.modifier', 'Modifier une administration', NULL, 'administrations', 'modifier', '2026-01-03 08:56:40'),
(45, 'ordonnances_labo.consulter', 'Consulter les ordonnances labo', NULL, 'ordonnances_labo', 'consulter', '2026-01-03 08:56:40'),
(46, 'ordonnances_labo.creer', 'Créer une ordonnance labo', NULL, 'ordonnances_labo', 'creer', '2026-01-03 08:56:40'),
(47, 'ordonnances_labo.modifier', 'Modifier une ordonnance labo', NULL, 'ordonnances_labo', 'modifier', '2026-01-03 08:56:40'),
(48, 'ordonnances_labo.valider', 'Valider une ordonnance labo', NULL, 'ordonnances_labo', 'valider', '2026-01-03 08:56:40'),
(49, 'prelevements.consulter', 'Consulter les prélèvements', NULL, 'prelevements', 'consulter', '2026-01-03 08:56:40'),
(50, 'prelevements.creer', 'Créer un prélèvement', NULL, 'prelevements', 'creer', '2026-01-03 08:56:40'),
(51, 'prelevements.modifier', 'Modifier un prélèvement', NULL, 'prelevements', 'modifier', '2026-01-03 08:56:40'),
(52, 'prelevements.recevoir', 'Recevoir un prélèvement', NULL, 'prelevements', 'recevoir', '2026-01-03 08:56:40'),
(53, 'resultats_labo.consulter', 'Consulter les résultats labo', NULL, 'resultats_labo', 'consulter', '2026-01-03 08:56:40'),
(54, 'resultats_labo.creer', 'Créer un résultat labo', NULL, 'resultats_labo', 'creer', '2026-01-03 08:56:40'),
(55, 'resultats_labo.modifier', 'Modifier un résultat labo', NULL, 'resultats_labo', 'modifier', '2026-01-03 08:56:40'),
(56, 'resultats_labo.valider', 'Valider un résultat labo', NULL, 'resultats_labo', 'valider', '2026-01-03 08:56:40'),
(57, 'ordonnances_imagerie.consulter', 'Consulter les ordonnances imagerie', NULL, 'ordonnances_imagerie', 'consulter', '2026-01-03 08:56:40'),
(58, 'ordonnances_imagerie.creer', 'Créer une ordonnance imagerie', NULL, 'ordonnances_imagerie', 'creer', '2026-01-03 08:56:40'),
(59, 'ordonnances_imagerie.modifier', 'Modifier une ordonnance imagerie', NULL, 'ordonnances_imagerie', 'modifier', '2026-01-03 08:56:40'),
(60, 'ordonnances_imagerie.valider', 'Valider une ordonnance imagerie', NULL, 'ordonnances_imagerie', 'valider', '2026-01-03 08:56:40'),
(61, 'examens_imagerie.consulter', 'Consulter les examens imagerie', NULL, 'examens_imagerie', 'consulter', '2026-01-03 08:56:40'),
(62, 'examens_imagerie.creer', 'Créer un examen imagerie', NULL, 'examens_imagerie', 'creer', '2026-01-03 08:56:40'),
(63, 'examens_imagerie.modifier', 'Modifier un examen imagerie', NULL, 'examens_imagerie', 'modifier', '2026-01-03 08:56:40'),
(64, 'rapports_radiologiques.consulter', 'Consulter les rapports radiologiques', NULL, 'rapports_radiologiques', 'consulter', '2026-01-03 08:56:40'),
(65, 'rapports_radiologiques.creer', 'Créer un rapport radiologique', NULL, 'rapports_radiologiques', 'creer', '2026-01-03 08:56:40'),
(66, 'rapports_radiologiques.modifier', 'Modifier un rapport radiologique', NULL, 'rapports_radiologiques', 'modifier', '2026-01-03 08:56:40'),
(67, 'rapports_radiologiques.valider', 'Valider un rapport radiologique', NULL, 'rapports_radiologiques', 'valider', '2026-01-03 08:56:40'),
(68, 'demandes_interventions.consulter', 'Consulter les demandes d\'intervention', NULL, 'demandes_interventions', 'consulter', '2026-01-03 08:56:40'),
(69, 'demandes_interventions.creer', 'Créer une demande d\'intervention', NULL, 'demandes_interventions', 'creer', '2026-01-03 08:56:40'),
(70, 'demandes_interventions.modifier', 'Modifier une demande d\'intervention', NULL, 'demandes_interventions', 'modifier', '2026-01-03 08:56:40'),
(71, 'demandes_interventions.valider', 'Valider une demande d\'intervention', NULL, 'demandes_interventions', 'valider', '2026-01-03 08:56:40'),
(72, 'planning_operatoire.consulter', 'Consulter le planning opératoire', NULL, 'planning_operatoire', 'consulter', '2026-01-03 08:56:40'),
(73, 'planning_operatoire.creer', 'Créer un planning opératoire', NULL, 'planning_operatoire', 'creer', '2026-01-03 08:56:40'),
(74, 'planning_operatoire.modifier', 'Modifier un planning opératoire', NULL, 'planning_operatoire', 'modifier', '2026-01-03 08:56:40'),
(75, 'planning_operatoire.annuler', 'Annuler un planning opératoire', NULL, 'planning_operatoire', 'annuler', '2026-01-03 08:56:40'),
(76, 'rapports_operatoires.consulter', 'Consulter les rapports opératoires', NULL, 'rapports_operatoires', 'consulter', '2026-01-03 08:56:40'),
(77, 'rapports_operatoires.creer', 'Créer un rapport opératoire', NULL, 'rapports_operatoires', 'creer', '2026-01-03 08:56:40'),
(78, 'rapports_operatoires.modifier', 'Modifier un rapport opératoire', NULL, 'rapports_operatoires', 'modifier', '2026-01-03 08:56:40'),
(79, 'rapports_operatoires.valider', 'Valider un rapport opératoire', NULL, 'rapports_operatoires', 'valider', '2026-01-03 08:56:40'),
(80, 'triages.consulter', 'Consulter les triages', NULL, 'triages', 'consulter', '2026-01-03 08:56:40'),
(81, 'triages.creer', 'Créer un triage', NULL, 'triages', 'creer', '2026-01-03 08:56:40'),
(82, 'triages.modifier', 'Modifier un triage', NULL, 'triages', 'modifier', '2026-01-03 08:56:40'),
(83, 'stocks_pharmacie.consulter', 'Consulter les stocks pharmacie', NULL, 'stocks_pharmacie', 'consulter', '2026-01-03 08:56:40'),
(84, 'stocks_pharmacie.creer', 'Créer un stock pharmacie', NULL, 'stocks_pharmacie', 'creer', '2026-01-03 08:56:40'),
(85, 'stocks_pharmacie.modifier', 'Modifier un stock pharmacie', NULL, 'stocks_pharmacie', 'modifier', '2026-01-03 08:56:40'),
(86, 'distributions_pharmacie.consulter', 'Consulter les distributions pharmacie', NULL, 'distributions_pharmacie', 'consulter', '2026-01-03 08:56:40'),
(87, 'distributions_pharmacie.creer', 'Créer une distribution pharmacie', NULL, 'distributions_pharmacie', 'creer', '2026-01-03 08:56:40'),
(88, 'distributions_pharmacie.modifier', 'Modifier une distribution pharmacie', NULL, 'distributions_pharmacie', 'modifier', '2026-01-03 08:56:40'),
(89, 'factures.consulter', 'Consulter les factures', NULL, 'factures', 'consulter', '2026-01-03 08:56:40'),
(90, 'factures.creer', 'Créer une facture', NULL, 'factures', 'creer', '2026-01-03 08:56:40'),
(91, 'factures.modifier', 'Modifier une facture', NULL, 'factures', 'modifier', '2026-01-03 08:56:40'),
(92, 'factures.valider', 'Valider une facture', NULL, 'factures', 'valider', '2026-01-03 08:56:40'),
(93, 'factures.annuler', 'Annuler une facture', NULL, 'factures', 'annuler', '2026-01-03 08:56:40'),
(94, 'paiements.consulter', 'Consulter les paiements', NULL, 'paiements', 'consulter', '2026-01-03 08:56:40'),
(95, 'paiements.creer', 'Créer un paiement', NULL, 'paiements', 'creer', '2026-01-03 08:56:40'),
(96, 'paiements.modifier', 'Modifier un paiement', NULL, 'paiements', 'modifier', '2026-01-03 08:56:40'),
(97, 'paiements.annuler', 'Annuler un paiement', NULL, 'paiements', 'annuler', '2026-01-03 08:56:40'),
(98, 'reclamations_assurance.consulter', 'Consulter les réclamations assurance', NULL, 'reclamations_assurance', 'consulter', '2026-01-03 08:56:40'),
(99, 'reclamations_assurance.creer', 'Créer une réclamation assurance', NULL, 'reclamations_assurance', 'creer', '2026-01-03 08:56:40'),
(100, 'reclamations_assurance.modifier', 'Modifier une réclamation assurance', NULL, 'reclamations_assurance', 'modifier', '2026-01-03 08:56:40'),
(101, 'utilisateurs.consulter', 'Consulter les utilisateurs', NULL, 'utilisateurs', 'consulter', '2026-01-03 08:56:40'),
(102, 'utilisateurs.creer', 'Créer un utilisateur', NULL, 'utilisateurs', 'creer', '2026-01-03 08:56:40'),
(103, 'utilisateurs.modifier', 'Modifier un utilisateur', NULL, 'utilisateurs', 'modifier', '2026-01-03 08:56:40'),
(104, 'utilisateurs.supprimer', 'Supprimer un utilisateur', NULL, 'utilisateurs', 'supprimer', '2026-01-03 08:56:40'),
(105, 'utilisateurs.reinitialiser_mdp', 'Réinitialiser le mot de passe', NULL, 'utilisateurs', 'reinitialiser_mdp', '2026-01-03 08:56:40'),
(106, 'formations.consulter', 'Consulter les formations', NULL, 'formations', 'consulter', '2026-01-03 08:56:40'),
(107, 'formations.creer', 'Créer une formation', NULL, 'formations', 'creer', '2026-01-03 08:56:40'),
(108, 'formations.modifier', 'Modifier une formation', NULL, 'formations', 'modifier', '2026-01-03 08:56:40'),
(109, 'formations.supprimer', 'Supprimer une formation', NULL, 'formations', 'supprimer', '2026-01-03 08:56:40'),
(110, 'conges.consulter', 'Consulter les congés', NULL, 'conges', 'consulter', '2026-01-03 08:56:40'),
(111, 'conges.creer', 'Demander un congé', NULL, 'conges', 'creer', '2026-01-03 08:56:40'),
(112, 'conges.modifier', 'Modifier un congé', NULL, 'conges', 'modifier', '2026-01-03 08:56:40'),
(113, 'conges.approuver', 'Approuver un congé', NULL, 'conges', 'approuver', '2026-01-03 08:56:40'),
(114, 'conges.rejeter', 'Rejeter un congé', NULL, 'conges', 'rejeter', '2026-01-03 08:56:40'),
(115, 'paie.consulter', 'Consulter la paie', NULL, 'paie', 'consulter', '2026-01-03 08:56:40'),
(116, 'paie.creer', 'Créer un bulletin de paie', NULL, 'paie', 'creer', '2026-01-03 08:56:40'),
(117, 'paie.modifier', 'Modifier un bulletin de paie', NULL, 'paie', 'modifier', '2026-01-03 08:56:40'),
(118, 'hopitaux.consulter', 'Consulter les hôpitaux', NULL, 'hopitaux', 'consulter', '2026-01-03 08:56:40'),
(119, 'hopitaux.creer', 'Créer un hôpital', NULL, 'hopitaux', 'creer', '2026-01-03 08:56:40'),
(120, 'hopitaux.modifier', 'Modifier un hôpital', NULL, 'hopitaux', 'modifier', '2026-01-03 08:56:40'),
(121, 'services.consulter', 'Consulter les services', NULL, 'services', 'consulter', '2026-01-03 08:56:40'),
(122, 'services.creer', 'Créer un service', NULL, 'services', 'creer', '2026-01-03 08:56:40'),
(123, 'services.modifier', 'Modifier un service', NULL, 'services', 'modifier', '2026-01-03 08:56:40'),
(124, 'lits.consulter', 'Consulter les lits', NULL, 'lits', 'consulter', '2026-01-03 08:56:40'),
(125, 'lits.creer', 'Créer un lit', NULL, 'lits', 'creer', '2026-01-03 08:56:40'),
(126, 'lits.modifier', 'Modifier un lit', NULL, 'lits', 'modifier', '2026-01-03 08:56:40'),
(127, 'fournisseurs.consulter', 'Consulter les fournisseurs', NULL, 'fournisseurs', 'consulter', '2026-01-03 08:56:40'),
(128, 'fournisseurs.creer', 'Créer un fournisseur', NULL, 'fournisseurs', 'creer', '2026-01-03 08:56:40'),
(129, 'fournisseurs.modifier', 'Modifier un fournisseur', NULL, 'fournisseurs', 'modifier', '2026-01-03 08:56:40'),
(130, 'bons_commande.consulter', 'Consulter les bons de commande', NULL, 'bons_commande', 'consulter', '2026-01-03 08:56:40'),
(131, 'bons_commande.creer', 'Créer un bon de commande', NULL, 'bons_commande', 'creer', '2026-01-03 08:56:40'),
(132, 'bons_commande.modifier', 'Modifier un bon de commande', NULL, 'bons_commande', 'modifier', '2026-01-03 08:56:40'),
(133, 'bons_commande.valider', 'Valider un bon de commande', NULL, 'bons_commande', 'valider', '2026-01-03 08:56:40'),
(134, 'equipements.consulter', 'Consulter les équipements', NULL, 'equipements', 'consulter', '2026-01-03 08:56:40'),
(135, 'equipements.creer', 'Créer un équipement', NULL, 'equipements', 'creer', '2026-01-03 08:56:40'),
(136, 'equipements.modifier', 'Modifier un équipement', NULL, 'equipements', 'modifier', '2026-01-03 08:56:40'),
(137, 'maintenance.consulter', 'Consulter la maintenance', NULL, 'maintenance', 'consulter', '2026-01-03 08:56:40'),
(138, 'maintenance.creer', 'Créer une maintenance', NULL, 'maintenance', 'creer', '2026-01-03 08:56:40'),
(139, 'maintenance.modifier', 'Modifier une maintenance', NULL, 'maintenance', 'modifier', '2026-01-03 08:56:40'),
(140, 'plaintes.consulter', 'Consulter les plaintes', NULL, 'plaintes', 'consulter', '2026-01-03 08:56:40'),
(141, 'plaintes.creer', 'Créer une plainte', NULL, 'plaintes', 'creer', '2026-01-03 08:56:40'),
(142, 'plaintes.modifier', 'Modifier une plainte', NULL, 'plaintes', 'modifier', '2026-01-03 08:56:40'),
(143, 'plaintes.resoudre', 'Résoudre une plainte', NULL, 'plaintes', 'resoudre', '2026-01-03 08:56:40'),
(144, 'indicateurs.consulter', 'Consulter les indicateurs', NULL, 'indicateurs', 'consulter', '2026-01-03 08:56:40'),
(145, 'indicateurs.creer', 'Créer un indicateur', NULL, 'indicateurs', 'creer', '2026-01-03 08:56:40'),
(146, 'indicateurs.modifier', 'Modifier un indicateur', NULL, 'indicateurs', 'modifier', '2026-01-03 08:56:40'),
(147, 'rapports.consulter', 'Consulter les rapports', NULL, 'rapports', 'consulter', '2026-01-03 08:56:40'),
(148, 'rapports.creer', 'Créer un rapport', NULL, 'rapports', 'creer', '2026-01-03 08:56:40'),
(149, 'rapports.modifier', 'Modifier un rapport', NULL, 'rapports', 'modifier', '2026-01-03 08:56:40'),
(150, 'rapports.exporter', 'Exporter un rapport', NULL, 'rapports', 'exporter', '2026-01-03 08:56:40'),
(151, 'logs.consulter', 'Consulter les logs', NULL, 'logs', 'consulter', '2026-01-03 08:56:40'),
(152, 'parametres.consulter', 'Consulter les paramètres', NULL, 'parametres', 'consulter', '2026-01-03 08:56:40'),
(153, 'parametres.modifier', 'Modifier les paramètres', NULL, 'parametres', 'modifier', '2026-01-03 08:56:40'),
(154, 'roles.consulter', 'Consulter les rôles', NULL, 'roles', 'consulter', '2026-01-03 08:56:40'),
(155, 'roles.creer', 'Créer un rôle', NULL, 'roles', 'creer', '2026-01-03 08:56:40'),
(156, 'roles.modifier', 'Modifier un rôle', NULL, 'roles', 'modifier', '2026-01-03 08:56:40'),
(157, 'permissions.consulter', 'Consulter les permissions', NULL, 'permissions', 'consulter', '2026-01-03 08:56:40'),
(158, 'permissions.modifier', 'Modifier les permissions', NULL, 'permissions', 'modifier', '2026-01-03 08:56:40'),
(159, 'sauvegardes.consulter', 'Consulter les sauvegardes', NULL, 'sauvegardes', 'consulter', '2026-01-03 08:56:40'),
(160, 'sauvegardes.creer', 'Créer une sauvegarde', NULL, 'sauvegardes', 'creer', '2026-01-03 08:56:40'),
(161, 'archives.consulter', 'Consulter les archives', NULL, 'archives', 'consulter', '2026-01-03 08:56:40'),
(162, 'archives.creer', 'Créer une archive', NULL, 'archives', 'creer', '2026-01-03 08:56:40');

-- --------------------------------------------------------

--
-- Structure de la table `plaintes_incidents`
--

DROP TABLE IF EXISTS `plaintes_incidents`;
CREATE TABLE IF NOT EXISTS `plaintes_incidents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `patient_id` int DEFAULT NULL,
  `utilisateur_id` int DEFAULT NULL,
  `numero_plainte` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_plainte` datetime NOT NULL,
  `type_plainte` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Clinique, Administrative, Qualité, Sécurité',
  `description_plainte` text COLLATE utf8mb4_unicode_ci,
  `gravite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mineure, Modérée, Grave, Critique',
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ouverte, En investigation, Résolue, Fermée',
  `responsable_investigation_id` int DEFAULT NULL,
  `date_resolution` datetime DEFAULT NULL,
  `actions_correctives` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_plainte` (`hopital_id`,`numero_plainte`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_date` (`date_plainte`),
  KEY `patient_id` (`patient_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `responsable_investigation_id` (`responsable_investigation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `planning_operatoire`
--

DROP TABLE IF EXISTS `planning_operatoire`;
CREATE TABLE IF NOT EXISTS `planning_operatoire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `salle_operation_id` int NOT NULL,
  `demande_intervention_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `chirurgien_id` int NOT NULL,
  `date_operation` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time DEFAULT NULL,
  `duree_reelle` int DEFAULT NULL COMMENT 'En minutes',
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Programmée, En cours, Complétée, Annulée, Reportée',
  `raison_annulation` text COLLATE utf8mb4_unicode_ci,
  `date_annulation` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_salle` (`salle_operation_id`),
  KEY `idx_date` (`date_operation`),
  KEY `idx_statut` (`statut`),
  KEY `demande_intervention_id` (`demande_intervention_id`),
  KEY `patient_id` (`patient_id`),
  KEY `chirurgien_id` (`chirurgien_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `prelevements`
--

DROP TABLE IF EXISTS `prelevements`;
CREATE TABLE IF NOT EXISTS `prelevements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordonnance_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_prelevement` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_prelevement` datetime NOT NULL,
  `infirmier_id` int NOT NULL,
  `type_specimen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume_specimen` int DEFAULT NULL,
  `tube_prelevement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_tube` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditions_conservation` text COLLATE utf8mb4_unicode_ci,
  `observations_prelevement` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Prélevé, En transit, Reçu au labo, Rejeté',
  `date_reception_labo` datetime DEFAULT NULL,
  `raison_rejet` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_prelevement` (`hopital_id`,`numero_prelevement`),
  KEY `idx_ordonnance` (`ordonnance_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_date` (`date_prelevement`),
  KEY `infirmier_id` (`infirmier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `consultation_id` int DEFAULT NULL,
  `admission_id` int DEFAULT NULL,
  `patient_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_prescription` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_prescription` datetime NOT NULL,
  `date_debut_traitement` date DEFAULT NULL,
  `date_fin_traitement` date DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validée, Exécutée, Annulée',
  `notes_prescription` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_prescription` (`hopital_id`,`numero_prescription`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_medecin` (`medecin_id`),
  KEY `idx_date` (`date_prescription`),
  KEY `idx_statut` (`statut`),
  KEY `consultation_id` (`consultation_id`),
  KEY `admission_id` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `profils_utilisateurs`
--

DROP TABLE IF EXISTS `profils_utilisateurs`;
CREATE TABLE IF NOT EXISTS `profils_utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_profil` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Médecin, Infirmier, Admin, Pharmacien, Labo, Radiologue, RH, Finance, etc.',
  `icone` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `profils_utilisateurs`
--

INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES
(1, 'admin', 'Administrateur', 'Profil administrateur système', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(2, 'directeur', 'Directeur', 'Profil directeur d\'hôpital', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(3, 'medecin', 'Médecin', 'Profil médecin', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(4, 'infirmier', 'Infirmier', 'Profil infirmier', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(5, 'pharmacien', 'Pharmacien', 'Profil pharmacien', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(6, 'laborantin', 'Laborantin', 'Profil laborantin', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(7, 'radiologue', 'Radiologue', 'Profil radiologue', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(8, 'comptable', 'Comptable', 'Profil comptable', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(9, 'rh', 'Responsable RH', 'Profil responsable RH', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(10, 'maintenance', 'Technicien Maintenance', 'Profil technicien maintenance', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(11, 'receptionniste', 'Réceptionniste', 'Profil réceptionniste', NULL, NULL, NULL, 1, '2026-01-03 08:59:47'),
(12, 'patient', 'Patient', 'Profil patient', NULL, NULL, NULL, 1, '2026-01-03 08:59:47');

-- --------------------------------------------------------

--
-- Structure de la table `projets_recherche`
--

DROP TABLE IF EXISTS `projets_recherche`;
CREATE TABLE IF NOT EXISTS `projets_recherche` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `code_projet` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre_projet` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_projet` longtext COLLATE utf8mb4_unicode_ci,
  `chercheur_principal_id` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `budget_projet` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Planifié, En cours, Suspendu, Complété, Annulé',
  `comite_ethique_approuve` tinyint(1) DEFAULT '0',
  `date_approbation_ethique` date DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_projet`),
  KEY `chercheur_principal_id` (`chercheur_principal_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapports_operatoires`
--

DROP TABLE IF EXISTS `rapports_operatoires`;
CREATE TABLE IF NOT EXISTS `rapports_operatoires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `planning_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_rapport` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_rapport` datetime NOT NULL,
  `chirurgien_id` int NOT NULL,
  `titre_intervention` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_intervention` longtext COLLATE utf8mb4_unicode_ci,
  `complications` text COLLATE utf8mb4_unicode_ci,
  `produits_utilises` text COLLATE utf8mb4_unicode_ci,
  `specimens_preleves` text COLLATE utf8mb4_unicode_ci,
  `recommandations_postop` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validé, Signé, Archivé',
  `date_signature` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_rapport` (`hopital_id`,`numero_rapport`),
  KEY `idx_planning` (`planning_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `chirurgien_id` (`chirurgien_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapports_personnalises`
--

DROP TABLE IF EXISTS `rapports_personnalises`;
CREATE TABLE IF NOT EXISTS `rapports_personnalises` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `nom_rapport` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_rapport` text COLLATE utf8mb4_unicode_ci,
  `type_rapport` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Activité, Financier, Qualité, RH, etc.',
  `configuration_rapport` longtext COLLATE utf8mb4_unicode_ci COMMENT 'JSON avec paramètres du rapport',
  `frequence_generation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Manuelle, Quotidienne, Hebdomadaire, Mensuelle',
  `date_derniere_generation` datetime DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Actif, Inactif',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapports_radiologiques`
--

DROP TABLE IF EXISTS `rapports_radiologiques`;
CREATE TABLE IF NOT EXISTS `rapports_radiologiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `examen_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_rapport` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_rapport` datetime NOT NULL,
  `radiologue_id` int NOT NULL,
  `titre_rapport` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenu_rapport` longtext COLLATE utf8mb4_unicode_ci,
  `conclusion` text COLLATE utf8mb4_unicode_ci,
  `recommandations` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brouillon, Validé, Signé, Archivé',
  `date_signature` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_rapport` (`hopital_id`,`numero_rapport`),
  KEY `idx_examen` (`examen_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `radiologue_id` (`radiologue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reclamations_assurance`
--

DROP TABLE IF EXISTS `reclamations_assurance`;
CREATE TABLE IF NOT EXISTS `reclamations_assurance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `facture_id` int NOT NULL,
  `convention_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_reclamation` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_reclamation` date NOT NULL,
  `montant_reclame` decimal(12,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Soumise, En cours, Acceptée, Rejetée, Partiellement acceptée',
  `motif_rejet` text COLLATE utf8mb4_unicode_ci,
  `date_reponse` date DEFAULT NULL,
  `montant_accepte` decimal(12,2) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_reclamation` (`hopital_id`,`numero_reclamation`),
  KEY `facture_id` (`facture_id`),
  KEY `convention_id` (`convention_id`),
  KEY `devise_id` (`devise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

DROP TABLE IF EXISTS `rendez_vous`;
CREATE TABLE IF NOT EXISTS `rendez_vous` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `creneau_id` int NOT NULL,
  `medecin_id` int NOT NULL,
  `service_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `date_rendez_vous` date NOT NULL,
  `heure_rendez_vous` time NOT NULL,
  `motif_consultation` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Programmé, Confirmé, Réalisé, Annulé, No-show',
  `type_consultation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes_pre_consultation` text COLLATE utf8mb4_unicode_ci,
  `date_confirmation` datetime DEFAULT NULL,
  `date_realisation` datetime DEFAULT NULL,
  `date_annulation` datetime DEFAULT NULL,
  `raison_annulation` text COLLATE utf8mb4_unicode_ci,
  `rappel_sms_envoye` tinyint(1) DEFAULT '0',
  `rappel_email_envoye` tinyint(1) DEFAULT '0',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_medecin` (`medecin_id`),
  KEY `idx_date` (`date_rendez_vous`),
  KEY `idx_statut` (`statut`),
  KEY `creneau_id` (`creneau_id`),
  KEY `service_id` (`service_id`),
  KEY `hopital_id` (`hopital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `resultats_labo`
--

DROP TABLE IF EXISTS `resultats_labo`;
CREATE TABLE IF NOT EXISTS `resultats_labo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prelevement_id` int NOT NULL,
  `examen_id` int NOT NULL,
  `valeur_resultat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unite_resultat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valeur_reference_min` decimal(10,2) DEFAULT NULL,
  `valeur_reference_max` decimal(10,2) DEFAULT NULL,
  `statut_resultat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Normal, Anormal, Critique',
  `interpretation` text COLLATE utf8mb4_unicode_ci,
  `technicien_id` int DEFAULT NULL,
  `date_analyse` datetime DEFAULT NULL,
  `validateur_id` int DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_prelevement` (`prelevement_id`),
  KEY `idx_examen` (`examen_id`),
  KEY `technicien_id` (`technicien_id`),
  KEY `validateur_id` (`validateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `niveau_acces` int DEFAULT NULL COMMENT '0=Utilisateur, 1=Superviseur, 2=Admin, 3=Super-Admin',
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES
(1, 'ROLE_ADMIN', 'Administrateur', 'Accès complet au système', 100, 1, '2026-01-03 08:53:09'),
(2, 'ROLE_DIRECTEUR', 'Directeur', 'Gestion générale de l\'hôpital', 90, 1, '2026-01-03 08:53:09'),
(3, 'ROLE_MEDECIN', 'Médecin', 'Gestion des patients et consultations', 70, 1, '2026-01-03 08:53:09'),
(4, 'ROLE_INFIRMIER', 'Infirmier', 'Soins et suivi des patients', 60, 1, '2026-01-03 08:53:09'),
(5, 'ROLE_PHARMACIEN', 'Pharmacien', 'Gestion de la pharmacie', 65, 1, '2026-01-03 08:53:09'),
(6, 'ROLE_LABORANTIN', 'Laborantin', 'Gestion du laboratoire', 55, 1, '2026-01-03 08:53:09'),
(7, 'ROLE_RADIOLOGUE', 'Radiologue', 'Gestion de l\'imagerie', 65, 1, '2026-01-03 08:53:09'),
(8, 'ROLE_COMPTABLE', 'Comptable', 'Gestion financière', 60, 1, '2026-01-03 08:53:09'),
(9, 'ROLE_RH', 'Responsable RH', 'Gestion des ressources humaines', 65, 1, '2026-01-03 08:53:09'),
(10, 'ROLE_MAINTENANCE', 'Technicien Maintenance', 'Maintenance des équipements', 50, 1, '2026-01-03 08:53:09'),
(11, 'ROLE_RECEPTIONNISTE', 'Réceptionniste', 'Accueil et rendez-vous', 40, 1, '2026-01-03 08:53:09'),
(12, 'ROLE_PATIENT', 'Patient', 'Accès au portail patient', 10, 1, '2026-01-03 08:53:09');

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `salles_operations`
--

DROP TABLE IF EXISTS `salles_operations`;
CREATE TABLE IF NOT EXISTS `salles_operations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `service_id` int NOT NULL,
  `numero_salle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_salle` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chirurgie générale, Orthopédie, Cardiologie, etc.',
  `capacite` int DEFAULT NULL,
  `equipements` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Disponible, Occupée, Maintenance, Fermée',
  `date_derniere_maintenance` date DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_salle` (`hopital_id`,`numero_salle`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sauvegardes`
--

DROP TABLE IF EXISTS `sauvegardes`;
CREATE TABLE IF NOT EXISTS `sauvegardes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `numero_sauvegarde` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_sauvegarde` datetime NOT NULL,
  `type_sauvegarde` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Complète, Incrémentale, Différentielle',
  `taille_sauvegarde` int DEFAULT NULL COMMENT 'En Mo',
  `localisation_sauvegarde` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'En cours, Complétée, Échouée',
  `duree_sauvegarde` int DEFAULT NULL COMMENT 'En secondes',
  `utilisateur_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_sauvegarde` (`hopital_id`,`numero_sauvegarde`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_service` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Urgences, Chirurgie, Médecine, Pédiatrie, etc.',
  `chef_service_id` int DEFAULT NULL,
  `nombre_lits` int DEFAULT NULL,
  `localisation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_service` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur_service` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code_hopital` (`hopital_id`,`code`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_type` (`type_service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions_utilisateurs`
--

DROP TABLE IF EXISTS `sessions_utilisateurs`;
CREATE TABLE IF NOT EXISTS `sessions_utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `token_session` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `date_connexion` datetime NOT NULL,
  `date_derniere_activite` datetime DEFAULT NULL,
  `date_deconnexion` datetime DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Active, Expirée, Fermée',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_session` (`token_session`),
  KEY `idx_utilisateur` (`utilisateur_id`),
  KEY `idx_token` (`token_session`),
  KEY `hopital_id` (`hopital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialites`
--

DROP TABLE IF EXISTS `specialites`;
CREATE TABLE IF NOT EXISTS `specialites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `code_snomed` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icone` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stocks_pharmacie`
--

DROP TABLE IF EXISTS `stocks_pharmacie`;
CREATE TABLE IF NOT EXISTS `stocks_pharmacie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `medicament_id` int NOT NULL,
  `quantite_stock` int NOT NULL,
  `quantite_minimale` int DEFAULT NULL,
  `quantite_maximale` int DEFAULT NULL,
  `lot_numero` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `prix_achat_unitaire` decimal(10,2) DEFAULT NULL,
  `date_reception` date DEFAULT NULL,
  `fournisseur_id` int DEFAULT NULL,
  `localisation_stockage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_medicament_lot` (`hopital_id`,`medicament_id`,`lot_numero`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_medicament` (`medicament_id`),
  KEY `idx_date_expiration` (`date_expiration`),
  KEY `fournisseur_id` (`fournisseur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `suivis_post_hospitalisation`
--

DROP TABLE IF EXISTS `suivis_post_hospitalisation`;
CREATE TABLE IF NOT EXISTS `suivis_post_hospitalisation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `feuille_sortie_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `date_suivi` date NOT NULL,
  `type_suivi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Téléphone, Visite, Consultation',
  `observations_suivi` text COLLATE utf8mb4_unicode_ci,
  `complications_observees` text COLLATE utf8mb4_unicode_ci,
  `adherence_traitement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Bonne, Partielle, Mauvaise',
  `readmission_necessaire` tinyint(1) DEFAULT '0',
  `raison_readmission` text COLLATE utf8mb4_unicode_ci,
  `utilisateur_suivi_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_feuille` (`feuille_sortie_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `hopital_id` (`hopital_id`),
  KEY `utilisateur_suivi_id` (`utilisateur_suivi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tarifs_lits`
--

DROP TABLE IF EXISTS `tarifs_lits`;
CREATE TABLE IF NOT EXISTS `tarifs_lits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `type_lit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant_journalier` decimal(10,2) NOT NULL,
  `devise_id` int DEFAULT NULL,
  `taux_tva_id` int DEFAULT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_type_date` (`hopital_id`,`type_lit`,`date_debut`),
  KEY `devise_id` (`devise_id`),
  KEY `taux_tva_id` (`taux_tva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `taux_tva`
--

DROP TABLE IF EXISTS `taux_tva`;
CREATE TABLE IF NOT EXISTS `taux_tva` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pourcentage` decimal(5,2) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transferts_patients`
--

DROP TABLE IF EXISTS `transferts_patients`;
CREATE TABLE IF NOT EXISTS `transferts_patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admission_id` int NOT NULL,
  `service_origine_id` int NOT NULL,
  `service_destination_id` int NOT NULL,
  `lit_origine_id` int DEFAULT NULL,
  `lit_destination_id` int DEFAULT NULL,
  `date_transfert` datetime NOT NULL,
  `motif_transfert` text COLLATE utf8mb4_unicode_ci,
  `utilisateur_id` int DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admission_id` (`admission_id`),
  KEY `service_origine_id` (`service_origine_id`),
  KEY `service_destination_id` (`service_destination_id`),
  KEY `lit_origine_id` (`lit_origine_id`),
  KEY `lit_destination_id` (`lit_destination_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `triages`
--

DROP TABLE IF EXISTS `triages`;
CREATE TABLE IF NOT EXISTS `triages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `service_urgences_id` int NOT NULL,
  `numero_triage` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_triage` datetime NOT NULL,
  `infirmier_triage_id` int NOT NULL,
  `niveau_triage_id` int NOT NULL,
  `motif_consultation` text COLLATE utf8mb4_unicode_ci,
  `antecedents_pertinents` text COLLATE utf8mb4_unicode_ci,
  `allergies` text COLLATE utf8mb4_unicode_ci,
  `medicaments_actuels` text COLLATE utf8mb4_unicode_ci,
  `tension_arterielle` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequence_cardiaque` int DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `frequence_respiratoire` int DEFAULT NULL,
  `saturation_o2` decimal(5,2) DEFAULT NULL,
  `poids` decimal(6,2) DEFAULT NULL,
  `observations_triage` text COLLATE utf8mb4_unicode_ci,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Triagé, En attente, Pris en charge, Transféré',
  `date_prise_en_charge` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_triage` (`hopital_id`,`numero_triage`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_date` (`date_triage`),
  KEY `service_urgences_id` (`service_urgences_id`),
  KEY `infirmier_triage_id` (`infirmier_triage_id`),
  KEY `niveau_triage_id` (`niveau_triage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `types_examens_labo`
--

DROP TABLE IF EXISTS `types_examens_labo`;
CREATE TABLE IF NOT EXISTS `types_examens_labo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_examen` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_examen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `code_loinc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_snomed` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_specimen` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sang, Urine, Selles, etc.',
  `volume_specimen` int DEFAULT NULL COMMENT 'En mL',
  `tube_prelevement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditions_conservation` text COLLATE utf8mb4_unicode_ci,
  `delai_resultat` int DEFAULT NULL COMMENT 'En heures',
  `prix_examen` decimal(10,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `taux_tva_id` int DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_examen` (`code_examen`),
  KEY `idx_code` (`code_examen`),
  KEY `devise_id` (`devise_id`),
  KEY `taux_tva_id` (`taux_tva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `types_imagerie`
--

DROP TABLE IF EXISTS `types_imagerie`;
CREATE TABLE IF NOT EXISTS `types_imagerie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_imagerie` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_imagerie` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_modalite` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Radiographie, Scanner, IRM, Échographie, etc.',
  `code_snomed` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prix_examen` decimal(10,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `taux_tva_id` int DEFAULT NULL,
  `duree_examen` int DEFAULT NULL COMMENT 'En minutes',
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_imagerie` (`code_imagerie`),
  KEY `devise_id` (`devise_id`),
  KEY `taux_tva_id` (`taux_tva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `types_interventions`
--

DROP TABLE IF EXISTS `types_interventions`;
CREATE TABLE IF NOT EXISTS `types_interventions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_intervention` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom_intervention` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `code_ccam` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duree_moyenne` int DEFAULT NULL COMMENT 'En minutes',
  `niveau_complexite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Simple, Modérée, Complexe',
  `prix_intervention` decimal(10,2) DEFAULT NULL,
  `devise_id` int DEFAULT NULL,
  `taux_tva_id` int DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_intervention` (`code_intervention`),
  KEY `devise_id` (`devise_id`),
  KEY `taux_tva_id` (`taux_tva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `profil_id` int NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int NOT NULL,
  `specialite_id` int DEFAULT NULL,
  `numero_licence` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_ordre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_embauche` date DEFAULT NULL,
  `photo_profil` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_numerique` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_postal` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `sexe` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_identite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_identite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone_urgence` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_urgence_nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `compte_verrouille` tinyint(1) DEFAULT '0',
  `nombre_tentatives_connexion` int DEFAULT '0',
  `date_dernier_changement_mdp` datetime DEFAULT NULL,
  `mdp_temporaire` tinyint(1) DEFAULT '0',
  `authentification_2fa` tinyint(1) DEFAULT '0',
  `derniere_connexion` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_email` (`email`),
  KEY `idx_login` (`login`),
  KEY `idx_role` (`role_id`),
  KEY `idx_profil` (`profil_id`),
  KEY `idx_actif` (`actif`),
  KEY `specialite_id` (`specialite_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`) VALUES
(1, 1, 1, 'Dupont', 'Jean', 'admin@rehoboth.com', NULL, 'admin', '$2y$10$LP4/kQFz3h1B3VrQ6k8EDe5fsEQcLGk36rs1K4TqkMHtzdQLBWMju', 1, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, '2026-01-03 08:11:52', '2026-01-03 09:02:41', '2026-01-03 10:11:52'),
(2, 1, 2, 'Martin', 'Pierre', 'directeur@rehoboth.com', NULL, 'directeur', '$2y$10$LP4/kQFz3h1B3VrQ6k8EDe5fsEQcLGk36rs1K4TqkMHtzdQLBWMju', 2, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:37:30'),
(3, 1, 3, 'Bernard', 'Marie', 'medecin@rehoboth.com', NULL, 'medecin', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 3, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(4, 1, 4, 'Durand', 'Sophie', 'infirmier@rehoboth.com', NULL, 'infirmier', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 4, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(5, 1, 5, 'Lefevre', 'Thomas', 'pharmacien@rehoboth.com', NULL, 'pharmacien', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 5, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(6, 1, 6, 'Moreau', 'Luc', 'laborantin@rehoboth.com', NULL, 'laborantin', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 6, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(7, 1, 7, 'Girard', 'Anne', 'radiologue@rehoboth.com', NULL, 'radiologue', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 7, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(8, 1, 8, 'Petit', 'Jacques', 'comptable@rehoboth.com', NULL, 'comptable', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 8, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(9, 1, 9, 'Rousseau', 'Isabelle', 'rh@rehoboth.com', NULL, 'rh', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 9, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(10, 1, 10, 'Vincent', 'Marc', 'maintenance@rehoboth.com', NULL, 'maintenance', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 10, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41'),
(11, 1, 11, 'Fournier', 'Nathalie', 'receptionniste@rehoboth.com', NULL, 'receptionniste', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', 11, NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, NULL, '2026-01-03 09:02:45', '2026-01-03 09:02:45');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `actes_medicaux`
--
ALTER TABLE `actes_medicaux`
  ADD CONSTRAINT `actes_medicaux_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `actes_medicaux_ibfk_2` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `actes_medicaux_ibfk_3` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `administrations_medicaments`
--
ALTER TABLE `administrations_medicaments`
  ADD CONSTRAINT `administrations_medicaments_ibfk_1` FOREIGN KEY (`ligne_prescription_id`) REFERENCES `lignes_prescriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `administrations_medicaments_ibfk_2` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `administrations_medicaments_ibfk_3` FOREIGN KEY (`infirmier_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admissions_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admissions_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `admissions_ibfk_4` FOREIGN KEY (`lit_id`) REFERENCES `lits` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admissions_ibfk_5` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `admissions_ibfk_6` FOREIGN KEY (`medecin_sortie_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `affectations_utilisateurs`
--
ALTER TABLE `affectations_utilisateurs`
  ADD CONSTRAINT `affectations_utilisateurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affectations_utilisateurs_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `archives`
--
ALTER TABLE `archives`
  ADD CONSTRAINT `archives_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `archives_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `archives_ibfk_3` FOREIGN KEY (`utilisateur_archivage_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `assurances_patients`
--
ALTER TABLE `assurances_patients`
  ADD CONSTRAINT `assurances_patients_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assurances_patients_ibfk_2` FOREIGN KEY (`convention_id`) REFERENCES `conventions_assurance` (`id`);

--
-- Contraintes pour la table `bons_commande`
--
ALTER TABLE `bons_commande`
  ADD CONSTRAINT `bons_commande_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bons_commande_ibfk_2` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`),
  ADD CONSTRAINT `bons_commande_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `bons_commande_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `bulletins_paie`
--
ALTER TABLE `bulletins_paie`
  ADD CONSTRAINT `bulletins_paie_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulletins_paie_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulletins_paie_ibfk_3` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certifications_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `comptes_portail_patient`
--
ALTER TABLE `comptes_portail_patient`
  ADD CONSTRAINT `comptes_portail_patient_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comptes_portail_patient_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conges`
--
ALTER TABLE `conges`
  ADD CONSTRAINT `conges_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conges_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conges_ibfk_3` FOREIGN KEY (`approbateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `consentements_recherche`
--
ALTER TABLE `consentements_recherche`
  ADD CONSTRAINT `consentements_recherche_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `projets_recherche` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consentements_recherche_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consentements_recherche_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `consultations_ibfk_4` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `consultations_ibfk_5` FOREIGN KEY (`rendez_vous_id`) REFERENCES `rendez_vous` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `consultations_ibfk_6` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `contrats`
--
ALTER TABLE `contrats`
  ADD CONSTRAINT `contrats_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrats_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrats_ibfk_3` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `contrats_maintenance`
--
ALTER TABLE `contrats_maintenance`
  ADD CONSTRAINT `contrats_maintenance_ibfk_1` FOREIGN KEY (`equipement_id`) REFERENCES `equipements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrats_maintenance_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrats_maintenance_ibfk_3` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`),
  ADD CONSTRAINT `contrats_maintenance_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `conventions_assurance`
--
ALTER TABLE `conventions_assurance`
  ADD CONSTRAINT `conventions_assurance_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `creneaux_consultation`
--
ALTER TABLE `creneaux_consultation`
  ADD CONSTRAINT `creneaux_consultation_ibfk_1` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `creneaux_consultation_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_interventions`
--
ALTER TABLE `demandes_interventions`
  ADD CONSTRAINT `demandes_interventions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `demandes_interventions_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `demandes_interventions_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `demandes_interventions_ibfk_4` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `demandes_interventions_ibfk_5` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `demandes_interventions_ibfk_6` FOREIGN KEY (`intervention_id`) REFERENCES `types_interventions` (`id`);

--
-- Contraintes pour la table `distributions_pharmacie`
--
ALTER TABLE `distributions_pharmacie`
  ADD CONSTRAINT `distributions_pharmacie_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `distributions_pharmacie_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `distributions_pharmacie_ibfk_3` FOREIGN KEY (`pharmacien_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `dossiers_medicaux`
--
ALTER TABLE `dossiers_medicaux`
  ADD CONSTRAINT `dossiers_medicaux_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossiers_medicaux_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dossiers_medicaux_ibfk_3` FOREIGN KEY (`medecin_referent_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `equipements`
--
ALTER TABLE `equipements`
  ADD CONSTRAINT `equipements_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipements_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipements_ibfk_3` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipements_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `equipes_chirurgicales`
--
ALTER TABLE `equipes_chirurgicales`
  ADD CONSTRAINT `equipes_chirurgicales_ibfk_1` FOREIGN KEY (`planning_id`) REFERENCES `planning_operatoire` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipes_chirurgicales_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `examens_imagerie`
--
ALTER TABLE `examens_imagerie`
  ADD CONSTRAINT `examens_imagerie_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_imagerie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `examens_imagerie_ibfk_2` FOREIGN KEY (`imagerie_id`) REFERENCES `types_imagerie` (`id`),
  ADD CONSTRAINT `examens_imagerie_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `examens_imagerie_ibfk_4` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `examens_imagerie_ibfk_5` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `exports_rapports`
--
ALTER TABLE `exports_rapports`
  ADD CONSTRAINT `exports_rapports_ibfk_1` FOREIGN KEY (`rapport_id`) REFERENCES `rapports_personnalises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exports_rapports_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exports_rapports_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `factures`
--
ALTER TABLE `factures`
  ADD CONSTRAINT `factures_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `factures_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `factures_ibfk_3` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `factures_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `feuilles_sortie`
--
ALTER TABLE `feuilles_sortie`
  ADD CONSTRAINT `feuilles_sortie_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feuilles_sortie_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feuilles_sortie_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `feuilles_sortie_ibfk_4` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `feuilles_sortie_ibfk_5` FOREIGN KEY (`medecin_suivi_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `fichiers_dicom`
--
ALTER TABLE `fichiers_dicom`
  ADD CONSTRAINT `fichiers_dicom_ibfk_1` FOREIGN KEY (`examen_id`) REFERENCES `examens_imagerie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fichiers_dicom_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`);

--
-- Contraintes pour la table `formations`
--
ALTER TABLE `formations`
  ADD CONSTRAINT `formations_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `formations_ibfk_2` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `fournisseurs`
--
ALTER TABLE `fournisseurs`
  ADD CONSTRAINT `fournisseurs_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `indicateurs_qualite`
--
ALTER TABLE `indicateurs_qualite`
  ADD CONSTRAINT `indicateurs_qualite_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `indicateurs_qualite_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `integrations_externes`
--
ALTER TABLE `integrations_externes`
  ADD CONSTRAINT `integrations_externes_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `interactions_medicamenteuses`
--
ALTER TABLE `interactions_medicamenteuses`
  ADD CONSTRAINT `interactions_medicamenteuses_ibfk_1` FOREIGN KEY (`medicament_1_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interactions_medicamenteuses_ibfk_2` FOREIGN KEY (`medicament_2_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `interventions_maintenance`
--
ALTER TABLE `interventions_maintenance`
  ADD CONSTRAINT `interventions_maintenance_ibfk_1` FOREIGN KEY (`equipement_id`) REFERENCES `equipements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interventions_maintenance_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interventions_maintenance_ibfk_3` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `interventions_maintenance_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `lignes_bons_commande`
--
ALTER TABLE `lignes_bons_commande`
  ADD CONSTRAINT `lignes_bons_commande_ibfk_1` FOREIGN KEY (`bon_commande_id`) REFERENCES `bons_commande` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lignes_bons_commande_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `lignes_distributions`
--
ALTER TABLE `lignes_distributions`
  ADD CONSTRAINT `lignes_distributions_ibfk_1` FOREIGN KEY (`distribution_id`) REFERENCES `distributions_pharmacie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lignes_distributions_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`);

--
-- Contraintes pour la table `lignes_factures`
--
ALTER TABLE `lignes_factures`
  ADD CONSTRAINT `lignes_factures_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lignes_factures_ibfk_2` FOREIGN KEY (`acte_id`) REFERENCES `actes_medicaux` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `lignes_ordonnances_imagerie`
--
ALTER TABLE `lignes_ordonnances_imagerie`
  ADD CONSTRAINT `lignes_ordonnances_imagerie_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_imagerie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lignes_ordonnances_imagerie_ibfk_2` FOREIGN KEY (`imagerie_id`) REFERENCES `types_imagerie` (`id`);

--
-- Contraintes pour la table `lignes_ordonnances_labo`
--
ALTER TABLE `lignes_ordonnances_labo`
  ADD CONSTRAINT `lignes_ordonnances_labo_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_labo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lignes_ordonnances_labo_ibfk_2` FOREIGN KEY (`examen_id`) REFERENCES `types_examens_labo` (`id`),
  ADD CONSTRAINT `lignes_ordonnances_labo_ibfk_3` FOREIGN KEY (`panel_id`) REFERENCES `panels_examens` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `lignes_prescriptions`
--
ALTER TABLE `lignes_prescriptions`
  ADD CONSTRAINT `lignes_prescriptions_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lignes_prescriptions_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`);

--
-- Contraintes pour la table `lits`
--
ALTER TABLE `lits`
  ADD CONSTRAINT `lits_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lits_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `logs_audit`
--
ALTER TABLE `logs_audit`
  ADD CONSTRAINT `logs_audit_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `logs_audit_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `logs_integrations`
--
ALTER TABLE `logs_integrations`
  ADD CONSTRAINT `logs_integrations_ibfk_1` FOREIGN KEY (`integration_id`) REFERENCES `integrations_externes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logs_integrations_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `medicaments`
--
ALTER TABLE `medicaments`
  ADD CONSTRAINT `medicaments_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `medicaments_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `messages_securises`
--
ALTER TABLE `messages_securises`
  ADD CONSTRAINT `messages_securises_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_securises_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_securises_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `mesures_indicateurs`
--
ALTER TABLE `mesures_indicateurs`
  ADD CONSTRAINT `mesures_indicateurs_ibfk_1` FOREIGN KEY (`indicateur_id`) REFERENCES `indicateurs_qualite` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mesures_indicateurs_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mesures_indicateurs_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `mouvements_stock`
--
ALTER TABLE `mouvements_stock`
  ADD CONSTRAINT `mouvements_stock_ibfk_1` FOREIGN KEY (`stock_id`) REFERENCES `stocks_pharmacie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mouvements_stock_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notes_infirmieres`
--
ALTER TABLE `notes_infirmieres`
  ADD CONSTRAINT `notes_infirmieres_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_infirmieres_ibfk_2` FOREIGN KEY (`infirmier_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ordonnances_imagerie`
--
ALTER TABLE `ordonnances_imagerie`
  ADD CONSTRAINT `ordonnances_imagerie_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ordonnances_imagerie_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `ordonnances_imagerie_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `ordonnances_imagerie_ibfk_4` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ordonnances_imagerie_ibfk_5` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ordonnances_labo`
--
ALTER TABLE `ordonnances_labo`
  ADD CONSTRAINT `ordonnances_labo_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ordonnances_labo_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `ordonnances_labo_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `ordonnances_labo_ibfk_4` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ordonnances_labo_ibfk_5` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paiements_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paiements_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `paiements_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `paiements_ibfk_5` FOREIGN KEY (`mode_paiement_id`) REFERENCES `modes_paiement` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `panels_examens`
--
ALTER TABLE `panels_examens`
  ADD CONSTRAINT `panels_examens_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `panels_examens_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `panel_examens`
--
ALTER TABLE `panel_examens`
  ADD CONSTRAINT `panel_examens_ibfk_1` FOREIGN KEY (`panel_id`) REFERENCES `panels_examens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `panel_examens_ibfk_2` FOREIGN KEY (`examen_id`) REFERENCES `types_examens_labo` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `parametres_configuration`
--
ALTER TABLE `parametres_configuration`
  ADD CONSTRAINT `parametres_configuration_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parametres_configuration_ibfk_2` FOREIGN KEY (`devise_defaut_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `parametres_configuration_ibfk_3` FOREIGN KEY (`mode_paiement_defaut_id`) REFERENCES `modes_paiement` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `participations_formations`
--
ALTER TABLE `participations_formations`
  ADD CONSTRAINT `participations_formations_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participations_formations_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `plaintes_incidents`
--
ALTER TABLE `plaintes_incidents`
  ADD CONSTRAINT `plaintes_incidents_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `plaintes_incidents_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `plaintes_incidents_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `plaintes_incidents_ibfk_4` FOREIGN KEY (`responsable_investigation_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `planning_operatoire`
--
ALTER TABLE `planning_operatoire`
  ADD CONSTRAINT `planning_operatoire_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `planning_operatoire_ibfk_2` FOREIGN KEY (`salle_operation_id`) REFERENCES `salles_operations` (`id`),
  ADD CONSTRAINT `planning_operatoire_ibfk_3` FOREIGN KEY (`demande_intervention_id`) REFERENCES `demandes_interventions` (`id`),
  ADD CONSTRAINT `planning_operatoire_ibfk_4` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `planning_operatoire_ibfk_5` FOREIGN KEY (`chirurgien_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `prelevements`
--
ALTER TABLE `prelevements`
  ADD CONSTRAINT `prelevements_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_labo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prelevements_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prelevements_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `prelevements_ibfk_4` FOREIGN KEY (`infirmier_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_4` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_5` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`);

--
-- Contraintes pour la table `projets_recherche`
--
ALTER TABLE `projets_recherche`
  ADD CONSTRAINT `projets_recherche_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projets_recherche_ibfk_2` FOREIGN KEY (`chercheur_principal_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `projets_recherche_ibfk_3` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `rapports_operatoires`
--
ALTER TABLE `rapports_operatoires`
  ADD CONSTRAINT `rapports_operatoires_ibfk_1` FOREIGN KEY (`planning_id`) REFERENCES `planning_operatoire` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapports_operatoires_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapports_operatoires_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `rapports_operatoires_ibfk_4` FOREIGN KEY (`chirurgien_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `rapports_personnalises`
--
ALTER TABLE `rapports_personnalises`
  ADD CONSTRAINT `rapports_personnalises_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapports_personnalises_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rapports_radiologiques`
--
ALTER TABLE `rapports_radiologiques`
  ADD CONSTRAINT `rapports_radiologiques_ibfk_1` FOREIGN KEY (`examen_id`) REFERENCES `examens_imagerie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapports_radiologiques_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapports_radiologiques_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `rapports_radiologiques_ibfk_4` FOREIGN KEY (`radiologue_id`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `reclamations_assurance`
--
ALTER TABLE `reclamations_assurance`
  ADD CONSTRAINT `reclamations_assurance_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reclamations_assurance_ibfk_2` FOREIGN KEY (`convention_id`) REFERENCES `conventions_assurance` (`id`),
  ADD CONSTRAINT `reclamations_assurance_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `reclamations_assurance_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`creneau_id`) REFERENCES `creneaux_consultation` (`id`),
  ADD CONSTRAINT `rendez_vous_ibfk_3` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `rendez_vous_ibfk_4` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `rendez_vous_ibfk_5` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`);

--
-- Contraintes pour la table `resultats_labo`
--
ALTER TABLE `resultats_labo`
  ADD CONSTRAINT `resultats_labo_ibfk_1` FOREIGN KEY (`prelevement_id`) REFERENCES `prelevements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resultats_labo_ibfk_2` FOREIGN KEY (`examen_id`) REFERENCES `types_examens_labo` (`id`),
  ADD CONSTRAINT `resultats_labo_ibfk_3` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resultats_labo_ibfk_4` FOREIGN KEY (`validateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `salles_operations`
--
ALTER TABLE `salles_operations`
  ADD CONSTRAINT `salles_operations_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salles_operations_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Contraintes pour la table `sauvegardes`
--
ALTER TABLE `sauvegardes`
  ADD CONSTRAINT `sauvegardes_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sauvegardes_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sessions_utilisateurs`
--
ALTER TABLE `sessions_utilisateurs`
  ADD CONSTRAINT `sessions_utilisateurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sessions_utilisateurs_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stocks_pharmacie`
--
ALTER TABLE `stocks_pharmacie`
  ADD CONSTRAINT `stocks_pharmacie_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stocks_pharmacie_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`),
  ADD CONSTRAINT `stocks_pharmacie_ibfk_3` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `suivis_post_hospitalisation`
--
ALTER TABLE `suivis_post_hospitalisation`
  ADD CONSTRAINT `suivis_post_hospitalisation_ibfk_1` FOREIGN KEY (`feuille_sortie_id`) REFERENCES `feuilles_sortie` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suivis_post_hospitalisation_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `suivis_post_hospitalisation_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `suivis_post_hospitalisation_ibfk_4` FOREIGN KEY (`utilisateur_suivi_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `tarifs_lits`
--
ALTER TABLE `tarifs_lits`
  ADD CONSTRAINT `tarifs_lits_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarifs_lits_ibfk_2` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tarifs_lits_ibfk_3` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `transferts_patients`
--
ALTER TABLE `transferts_patients`
  ADD CONSTRAINT `transferts_patients_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transferts_patients_ibfk_2` FOREIGN KEY (`service_origine_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `transferts_patients_ibfk_3` FOREIGN KEY (`service_destination_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `transferts_patients_ibfk_4` FOREIGN KEY (`lit_origine_id`) REFERENCES `lits` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transferts_patients_ibfk_5` FOREIGN KEY (`lit_destination_id`) REFERENCES `lits` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transferts_patients_ibfk_6` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `triages`
--
ALTER TABLE `triages`
  ADD CONSTRAINT `triages_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `triages_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  ADD CONSTRAINT `triages_ibfk_3` FOREIGN KEY (`service_urgences_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `triages_ibfk_4` FOREIGN KEY (`infirmier_triage_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `triages_ibfk_5` FOREIGN KEY (`niveau_triage_id`) REFERENCES `niveaux_triage` (`id`);

--
-- Contraintes pour la table `types_examens_labo`
--
ALTER TABLE `types_examens_labo`
  ADD CONSTRAINT `types_examens_labo_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `types_examens_labo_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `types_imagerie`
--
ALTER TABLE `types_imagerie`
  ADD CONSTRAINT `types_imagerie_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `types_imagerie_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `types_interventions`
--
ALTER TABLE `types_interventions`
  ADD CONSTRAINT `types_interventions_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `types_interventions_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `utilisateurs_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `utilisateurs_ibfk_3` FOREIGN KEY (`profil_id`) REFERENCES `profils_utilisateurs` (`id`),
  ADD CONSTRAINT `utilisateurs_ibfk_4` FOREIGN KEY (`specialite_id`) REFERENCES `specialites` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
