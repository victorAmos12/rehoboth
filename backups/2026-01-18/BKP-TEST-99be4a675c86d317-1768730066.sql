-- Sauvegarde générée le 2026-01-18 11:54:26
-- Rehoboth Hospital Management System

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `actes_medicaux`;
CREATE TABLE `actes_medicaux` (
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
  KEY `taux_tva_id` (`taux_tva_id`),
  CONSTRAINT `actes_medicaux_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `actes_medicaux_ibfk_2` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `actes_medicaux_ibfk_3` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `administrations_medicaments`;
CREATE TABLE `administrations_medicaments` (
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
  KEY `idx_date` (`date_administration`),
  CONSTRAINT `administrations_medicaments_ibfk_1` FOREIGN KEY (`ligne_prescription_id`) REFERENCES `lignes_prescriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `administrations_medicaments_ibfk_2` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `administrations_medicaments_ibfk_3` FOREIGN KEY (`infirmier_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `admissions`;
CREATE TABLE `admissions` (
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
  KEY `medecin_sortie_id` (`medecin_sortie_id`),
  CONSTRAINT `admissions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admissions_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admissions_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `admissions_ibfk_4` FOREIGN KEY (`lit_id`) REFERENCES `lits` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_ibfk_5` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `admissions_ibfk_6` FOREIGN KEY (`medecin_sortie_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `affectations_utilisateurs`;
CREATE TABLE `affectations_utilisateurs` (
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
  KEY `service_id` (`service_id`),
  CONSTRAINT `affectations_utilisateurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `affectations_utilisateurs_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `affectations_utilisateurs` (`id`, `utilisateur_id`, `service_id`, `date_debut`, `date_fin`, `pourcentage_temps`, `actif`, `date_creation`) VALUES ('1', '3', '1', '2026-01-11', NULL, NULL, '1', '2026-01-11 01:56:10');

DROP TABLE IF EXISTS `archives`;
CREATE TABLE `archives` (
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
  KEY `utilisateur_archivage_id` (`utilisateur_archivage_id`),
  CONSTRAINT `archives_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `archives_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `archives_ibfk_3` FOREIGN KEY (`utilisateur_archivage_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `assurances_patients`;
CREATE TABLE `assurances_patients` (
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
  KEY `convention_id` (`convention_id`),
  CONSTRAINT `assurances_patients_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assurances_patients_ibfk_2` FOREIGN KEY (`convention_id`) REFERENCES `conventions_assurance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `backup_schedules`;
CREATE TABLE `backup_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `schedule_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_backup` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMPLETE',
  `frequency` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DAILY',
  `time` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '02:00',
  `day_of_week` int DEFAULT NULL,
  `day_of_month` int DEFAULT NULL,
  `localisation_backup` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `localisation_secondaire` longtext COLLATE utf8mb4_unicode_ci,
  `retention_days` int NOT NULL DEFAULT '30',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `prochaine_execution` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `derniere_execution` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `dernier_statut` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_erreur` longtext COLLATE utf8mb4_unicode_ci,
  `executions_reussies` int NOT NULL DEFAULT '0',
  `executions_echouees` int NOT NULL DEFAULT '0',
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)',
  `date_modification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_id` (`schedule_id`),
  UNIQUE KEY `UNIQ_schedule_id` (`schedule_id`),
  KEY `idx_hopital_id` (`hopital_id`),
  KEY `idx_utilisateur_id` (`utilisateur_id`),
  KEY `idx_actif` (`actif`),
  KEY `idx_prochaine_execution` (`prochaine_execution`),
  CONSTRAINT `FK_backup_schedules_hopital` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `FK_backup_schedules_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `backup_schedules` (`id`, `hopital_id`, `utilisateur_id`, `schedule_id`, `type_backup`, `frequency`, `time`, `day_of_week`, `day_of_month`, `localisation_backup`, `localisation_secondaire`, `retention_days`, `actif`, `prochaine_execution`, `derniere_execution`, `dernier_statut`, `message_erreur`, `executions_reussies`, `executions_echouees`, `notes`, `date_creation`, `date_modification`) VALUES ('1', '1', '1', 'SCHED-f423055cb0a7ff38-1768722778', 'COMPLETE', 'DAILY', '02:00', '1', '2', '/backups/', '', '30', '1', '2026-01-19 02:00:00', NULL, NULL, NULL, '0', '0', NULL, '2026-01-18 09:52:58', '2026-01-18 09:52:58');
INSERT INTO `backup_schedules` (`id`, `hopital_id`, `utilisateur_id`, `schedule_id`, `type_backup`, `frequency`, `time`, `day_of_week`, `day_of_month`, `localisation_backup`, `localisation_secondaire`, `retention_days`, `actif`, `prochaine_execution`, `derniere_execution`, `dernier_statut`, `message_erreur`, `executions_reussies`, `executions_echouees`, `notes`, `date_creation`, `date_modification`) VALUES ('2', '1', '1', 'SCHED-b3c3d44e0baf649e-1768722930', 'COMPLETE', 'DAILY', '09:57', '0', '18', '/sauvegardes/', '', '30', '1', '2026-01-18 09:57:00', NULL, NULL, NULL, '0', '0', NULL, '2026-01-18 09:55:30', '2026-01-18 09:55:30');

DROP TABLE IF EXISTS `bons_commande`;
CREATE TABLE `bons_commande` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `bons_commande_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bons_commande_ibfk_2` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`),
  CONSTRAINT `bons_commande_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `bons_commande_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `bulletins_paie`;
CREATE TABLE `bulletins_paie` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `bulletins_paie_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bulletins_paie_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bulletins_paie_ibfk_3` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `certifications`;
CREATE TABLE `certifications` (
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
  KEY `hopital_id` (`hopital_id`),
  CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `certifications_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `chambres`;
CREATE TABLE `chambres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `hopital_id` int NOT NULL,
  `numero_chambre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `etage` int DEFAULT NULL,
  `nombre_lits` int DEFAULT NULL,
  `type_chambre` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `localisation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `climatisee` tinyint(1) DEFAULT NULL,
  `sanitaires_prives` tinyint(1) DEFAULT NULL,
  `television` tinyint(1) DEFAULT NULL,
  `telephone` tinyint(1) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_statut` (`statut`),
  CONSTRAINT `FK_CHAMBRES_HOPITAL` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `FK_CHAMBRES_SERVICE` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chambres` (`id`, `service_id`, `hopital_id`, `numero_chambre`, `etage`, `nombre_lits`, `type_chambre`, `statut`, `description`, `localisation`, `climatisee`, `sanitaires_prives`, `television`, `telephone`, `date_creation`) VALUES ('1', '1', '1', '101', '1', '3', 'Simple', 'disponible', '', 'Aille B', '0', '0', '0', '0', '2026-01-10 12:38:03');

DROP TABLE IF EXISTS `comptes_portail_patient`;
CREATE TABLE `comptes_portail_patient` (
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
  KEY `hopital_id` (`hopital_id`),
  CONSTRAINT `comptes_portail_patient_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comptes_portail_patient_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `conges`;
CREATE TABLE `conges` (
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
  KEY `approbateur_id` (`approbateur_id`),
  CONSTRAINT `conges_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conges_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conges_ibfk_3` FOREIGN KEY (`approbateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `consentements_recherche`;
CREATE TABLE `consentements_recherche` (
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
  KEY `hopital_id` (`hopital_id`),
  CONSTRAINT `consentements_recherche_ibfk_1` FOREIGN KEY (`projet_id`) REFERENCES `projets_recherche` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consentements_recherche_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consentements_recherche_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `consultations`;
CREATE TABLE `consultations` (
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
  KEY `admission_id` (`admission_id`),
  CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `consultations_ibfk_4` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `consultations_ibfk_5` FOREIGN KEY (`rendez_vous_id`) REFERENCES `rendez_vous` (`id`) ON DELETE SET NULL,
  CONSTRAINT `consultations_ibfk_6` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `contrats`;
CREATE TABLE `contrats` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `contrats_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contrats_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contrats_ibfk_3` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `contrats_maintenance`;
CREATE TABLE `contrats_maintenance` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `contrats_maintenance_ibfk_1` FOREIGN KEY (`equipement_id`) REFERENCES `equipements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contrats_maintenance_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contrats_maintenance_ibfk_3` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`),
  CONSTRAINT `contrats_maintenance_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `conventions_assurance`;
CREATE TABLE `conventions_assurance` (
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
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code`),
  CONSTRAINT `conventions_assurance_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `creneaux_consultation`;
CREATE TABLE `creneaux_consultation` (
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
  KEY `idx_date` (`date_consultation`),
  CONSTRAINT `creneaux_consultation_ibfk_1` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `creneaux_consultation_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `demandes_interventions`;
CREATE TABLE `demandes_interventions` (
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
  KEY `intervention_id` (`intervention_id`),
  CONSTRAINT `demandes_interventions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `demandes_interventions_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `demandes_interventions_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `demandes_interventions_ibfk_4` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `demandes_interventions_ibfk_5` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `demandes_interventions_ibfk_6` FOREIGN KEY (`intervention_id`) REFERENCES `types_interventions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `devises`;
CREATE TABLE `devises` (
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


DROP TABLE IF EXISTS `distributions_pharmacie`;
CREATE TABLE `distributions_pharmacie` (
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
  KEY `pharmacien_id` (`pharmacien_id`),
  CONSTRAINT `distributions_pharmacie_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `distributions_pharmacie_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `distributions_pharmacie_ibfk_3` FOREIGN KEY (`pharmacien_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20240101000000CreateHISDatabase', '2026-01-03 03:19:35', '5948');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20250115CreateChambresTable', '2026-01-10 11:25:54', '0');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20250115CreateTypesServicesAndTypesPoles', '2026-01-11 01:20:13', '976');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20250115EnhanceLogsAuditAndBackups', '2026-01-17 06:58:14', '769');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20250115EnhanceServicesManagement', '2026-01-10 15:59:11', '2185');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20250120AddServiceCardFields', '2026-01-11 18:11:43', '1019');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20250120CreateBackupSchedules', '2026-01-17 09:07:16', '214');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20260103AddPatientMedicalInfo', '2026-01-07 11:41:13', '849');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20260103CreateMenusTable', '2026-01-03 08:28:58', '536');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20260108123505', '2026-01-08 12:35:51', '61');
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES ('DoctrineMigrations\\Version20260108130000', '2026-01-08 12:38:57', '1289');

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
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
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `dossiers_medicaux`;
CREATE TABLE `dossiers_medicaux` (
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
  KEY `medecin_referent_id` (`medecin_referent_id`),
  CONSTRAINT `dossiers_medicaux_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dossiers_medicaux_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dossiers_medicaux_ibfk_3` FOREIGN KEY (`medecin_referent_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dossiers_medicaux` (`id`, `patient_id`, `hopital_id`, `numero_dme`, `date_ouverture`, `date_fermeture`, `medecin_referent_id`, `statut`, `notes_generales`, `date_creation`, `date_modification`) VALUES ('1', '2', '1', '12323', '2026-01-07', NULL, '3', 'ACTIF', NULL, '2026-01-07 15:18:19', '2026-01-07 15:18:19');
INSERT INTO `dossiers_medicaux` (`id`, `patient_id`, `hopital_id`, `numero_dme`, `date_ouverture`, `date_fermeture`, `medecin_referent_id`, `statut`, `notes_generales`, `date_creation`, `date_modification`) VALUES ('2', '2', '1', '1111', '2026-01-08', NULL, '3', 'ACTIF', NULL, '2026-01-08 16:28:37', '2026-01-08 16:28:37');
INSERT INTO `dossiers_medicaux` (`id`, `patient_id`, `hopital_id`, `numero_dme`, `date_ouverture`, `date_fermeture`, `medecin_referent_id`, `statut`, `notes_generales`, `date_creation`, `date_modification`) VALUES ('3', '3', '1', '12EED', '2026-01-09', NULL, '4', 'ACTIF', NULL, '2026-01-09 14:12:28', '2026-01-09 14:12:28');

DROP TABLE IF EXISTS `equipements`;
CREATE TABLE `equipements` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `equipements_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipements_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipements_ibfk_3` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipements_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `equipes_chirurgicales`;
CREATE TABLE `equipes_chirurgicales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `planning_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `role_equipe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chirurgien, Assistant, Anesthésiste, Infirmier, etc.',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_planning_user` (`planning_id`,`utilisateur_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `equipes_chirurgicales_ibfk_1` FOREIGN KEY (`planning_id`) REFERENCES `planning_operatoire` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipes_chirurgicales_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `examens_imagerie`;
CREATE TABLE `examens_imagerie` (
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
  KEY `technicien_id` (`technicien_id`),
  CONSTRAINT `examens_imagerie_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_imagerie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `examens_imagerie_ibfk_2` FOREIGN KEY (`imagerie_id`) REFERENCES `types_imagerie` (`id`),
  CONSTRAINT `examens_imagerie_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `examens_imagerie_ibfk_4` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `examens_imagerie_ibfk_5` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `exports_rapports`;
CREATE TABLE `exports_rapports` (
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
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `exports_rapports_ibfk_1` FOREIGN KEY (`rapport_id`) REFERENCES `rapports_personnalises` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exports_rapports_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exports_rapports_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `factures`;
CREATE TABLE `factures` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `factures_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `factures_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `factures_ibfk_3` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `factures_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `feuilles_sortie`;
CREATE TABLE `feuilles_sortie` (
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
  KEY `medecin_suivi_id` (`medecin_suivi_id`),
  CONSTRAINT `feuilles_sortie_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feuilles_sortie_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `feuilles_sortie_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `feuilles_sortie_ibfk_4` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `feuilles_sortie_ibfk_5` FOREIGN KEY (`medecin_suivi_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `fichiers_dicom`;
CREATE TABLE `fichiers_dicom` (
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
  KEY `idx_hopital` (`hopital_id`),
  CONSTRAINT `fichiers_dicom_ibfk_1` FOREIGN KEY (`examen_id`) REFERENCES `examens_imagerie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fichiers_dicom_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `formations`;
CREATE TABLE `formations` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `formations_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formations_ibfk_2` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `fournisseurs`;
CREATE TABLE `fournisseurs` (
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
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_fournisseur`),
  CONSTRAINT `fournisseurs_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `hopitaux`;
CREATE TABLE `hopitaux` (
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

INSERT INTO `hopitaux` (`id`, `code`, `nom`, `adresse`, `ville`, `code_postal`, `telephone`, `email`, `directeur_id`, `type_hopital`, `nombre_lits`, `logo_url`, `icone_url`, `couleur_primaire`, `couleur_secondaire`, `site_web`, `numero_siret`, `numero_tva`, `actif`, `date_creation`, `date_modification`) VALUES ('1', 'REHOBOTH', 'Rehoboth Hospital', '123 Rue de la Santé', 'Paris', '75000', '+33123456789', 'contact@rehoboth.com', NULL, 'Hôpital Général', '500', 'public\\assets\\image_rehoboth.png', 'https://example.com/icon.png', '#0066CC', '#00CC99', 'https://www.rehoboth.com', '12345678901234', 'FR12345678901', '1', '2026-01-03 09:01:02', '2026-01-06 23:04:14');

DROP TABLE IF EXISTS `indicateurs_qualite`;
CREATE TABLE `indicateurs_qualite` (
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
  KEY `service_id` (`service_id`),
  CONSTRAINT `indicateurs_qualite_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indicateurs_qualite_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `integrations_externes`;
CREATE TABLE `integrations_externes` (
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
  UNIQUE KEY `unique_hopital_code` (`hopital_id`,`code_integration`),
  CONSTRAINT `integrations_externes_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `interactions_medicamenteuses`;
CREATE TABLE `interactions_medicamenteuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medicament_1_id` int NOT NULL,
  `medicament_2_id` int NOT NULL,
  `niveau_severite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Légère, Modérée, Grave, Contre-indication',
  `description` text COLLATE utf8mb4_unicode_ci,
  `recommandation` text COLLATE utf8mb4_unicode_ci,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_interaction` (`medicament_1_id`,`medicament_2_id`),
  KEY `medicament_2_id` (`medicament_2_id`),
  CONSTRAINT `interactions_medicamenteuses_ibfk_1` FOREIGN KEY (`medicament_1_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interactions_medicamenteuses_ibfk_2` FOREIGN KEY (`medicament_2_id`) REFERENCES `medicaments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `interventions_maintenance`;
CREATE TABLE `interventions_maintenance` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `interventions_maintenance_ibfk_1` FOREIGN KEY (`equipement_id`) REFERENCES `equipements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interventions_maintenance_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interventions_maintenance_ibfk_3` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `interventions_maintenance_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lignes_bons_commande`;
CREATE TABLE `lignes_bons_commande` (
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
  KEY `medicament_id` (`medicament_id`),
  CONSTRAINT `lignes_bons_commande_ibfk_1` FOREIGN KEY (`bon_commande_id`) REFERENCES `bons_commande` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lignes_bons_commande_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lignes_distributions`;
CREATE TABLE `lignes_distributions` (
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
  KEY `medicament_id` (`medicament_id`),
  CONSTRAINT `lignes_distributions_ibfk_1` FOREIGN KEY (`distribution_id`) REFERENCES `distributions_pharmacie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lignes_distributions_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lignes_factures`;
CREATE TABLE `lignes_factures` (
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
  KEY `acte_id` (`acte_id`),
  CONSTRAINT `lignes_factures_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lignes_factures_ibfk_2` FOREIGN KEY (`acte_id`) REFERENCES `actes_medicaux` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lignes_ordonnances_imagerie`;
CREATE TABLE `lignes_ordonnances_imagerie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordonnance_id` int NOT NULL,
  `imagerie_id` int NOT NULL,
  `quantite` int DEFAULT '1',
  `priorite` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Routine, Urgent',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ordonnance_id` (`ordonnance_id`),
  KEY `imagerie_id` (`imagerie_id`),
  CONSTRAINT `lignes_ordonnances_imagerie_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_imagerie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lignes_ordonnances_imagerie_ibfk_2` FOREIGN KEY (`imagerie_id`) REFERENCES `types_imagerie` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lignes_ordonnances_labo`;
CREATE TABLE `lignes_ordonnances_labo` (
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
  KEY `panel_id` (`panel_id`),
  CONSTRAINT `lignes_ordonnances_labo_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_labo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lignes_ordonnances_labo_ibfk_2` FOREIGN KEY (`examen_id`) REFERENCES `types_examens_labo` (`id`),
  CONSTRAINT `lignes_ordonnances_labo_ibfk_3` FOREIGN KEY (`panel_id`) REFERENCES `panels_examens` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lignes_prescriptions`;
CREATE TABLE `lignes_prescriptions` (
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
  KEY `idx_medicament` (`medicament_id`),
  CONSTRAINT `lignes_prescriptions_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lignes_prescriptions_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `lits`;
CREATE TABLE `lits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_id` int NOT NULL,
  `chambre_id` int NOT NULL,
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
  KEY `idx_statut` (`statut`),
  CONSTRAINT `lits_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lits_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `lits` (`id`, `service_id`, `chambre_id`, `hopital_id`, `numero_lit`, `type_lit`, `etage`, `chambre`, `statut`, `date_derniere_maintenance`, `date_creation`) VALUES ('2', '1', '1', '1', 'A12', 'Standard', '1', NULL, 'disponible', NULL, '2026-01-10 12:54:32');
INSERT INTO `lits` (`id`, `service_id`, `chambre_id`, `hopital_id`, `numero_lit`, `type_lit`, `etage`, `chambre`, `statut`, `date_derniere_maintenance`, `date_creation`) VALUES ('3', '1', '1', '1', 'A1112', 'Standard', '1', NULL, 'disponible', NULL, '2026-01-10 13:38:27');

DROP TABLE IF EXISTS `logs_audit`;
CREATE TABLE `logs_audit` (
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
  `type_log` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TECHNIQUE',
  `niveau` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entite_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `contexte` json DEFAULT NULL,
  `stack_trace` longtext COLLATE utf8mb4_unicode_ci,
  `endpoint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `methode_http` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temps_reponse_ms` int DEFAULT NULL,
  `code_http` int DEFAULT NULL,
  `statut` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_erreur` longtext COLLATE utf8mb4_unicode_ci,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_utilisateur` (`utilisateur_id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_module` (`module`),
  KEY `idx_date` (`date_creation`),
  KEY `idx_type_log` (`type_log`),
  KEY `idx_niveau` (`niveau`),
  KEY `idx_action_type` (`action_type`),
  CONSTRAINT `logs_audit_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `logs_audit_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `logs_integrations`;
CREATE TABLE `logs_integrations` (
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
  KEY `hopital_id` (`hopital_id`),
  CONSTRAINT `logs_integrations_ibfk_1` FOREIGN KEY (`integration_id`) REFERENCES `integrations_externes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `logs_integrations_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `medicaments`;
CREATE TABLE `medicaments` (
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
  KEY `taux_tva_id` (`taux_tva_id`),
  CONSTRAINT `medicaments_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medicaments_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `menu_roles`;
CREATE TABLE `menu_roles` (
  `menu_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`menu_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `menu_roles_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `menu_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('1', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('2', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('3', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('4', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('5', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('6', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('7', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('8', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('9', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('10', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('11', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('12', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('13', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('14', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('15', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('16', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('17', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('18', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('19', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('20', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('21', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('22', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('23', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('24', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('25', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('26', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('27', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('28', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('29', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('30', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('31', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('32', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('33', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('34', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('35', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('36', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('37', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('38', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('39', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('40', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('41', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('42', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('43', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('44', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('45', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('46', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('47', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('48', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('49', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('50', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('51', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('52', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('53', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('54', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('55', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('56', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('57', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('58', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('59', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('60', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('61', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('62', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('63', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('64', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('65', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('66', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('67', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('68', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('69', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('70', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('71', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('72', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('73', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('74', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('75', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('76', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('77', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('78', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('79', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('80', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('81', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('82', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('83', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('84', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('85', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('86', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('87', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('88', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('89', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('90', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('91', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('92', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('93', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('94', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('95', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('96', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('97', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('98', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('99', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('100', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('101', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('102', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('103', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('104', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('105', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('106', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('107', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('108', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('109', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('110', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('111', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('112', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('113', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('114', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('115', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('116', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('117', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('118', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('119', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('120', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('121', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('122', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('123', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('124', '1');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('2', '3');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('3', '3');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('5', '3');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('6', '3');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('7', '3');
INSERT INTO `menu_roles` (`menu_id`, `role_id`) VALUES ('8', '3');

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `icone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `ordre` int DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('1', 'dashboard', 'Tableau de bord', NULL, 'dashboard', '/dashboard', 'dashboard', NULL, '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('2', 'patients', 'Patients', NULL, 'people', '/patients', 'patients', NULL, '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('3', 'patients.list', 'Liste des patients', NULL, 'list', '/patients/list', 'patients', '2', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('4', 'patients.create', 'Nouveau patient', NULL, 'add', '/patients/create', 'patients', '2', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('5', 'patients.export', 'Exporter', NULL, 'download', '/patients/export', 'patients', '2', '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('6', 'dossiers_medicaux', 'Dossiers Médicaux', NULL, 'folder_open', '/dossiers-medicaux', 'dossiers_medicaux', NULL, '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('7', 'dossiers_medicaux.list', 'Liste des dossiers', NULL, 'list', '/dossiers-medicaux/list', 'dossiers_medicaux', '6', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('8', 'dossiers_medicaux.create', 'Nouveau dossier', NULL, 'add', '/dossiers-medicaux/create', 'dossiers_medicaux', '6', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('9', 'admissions', 'Admissions', NULL, 'assignment', '/admissions', 'admissions', NULL, '4', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('10', 'admissions.list', 'Liste des admissions', NULL, 'list', '/admissions/list', 'admissions', '9', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('11', 'admissions.create', 'Nouvelle admission', NULL, 'add', '/admissions/create', 'admissions', '9', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('12', 'transferts', 'Transferts', NULL, 'compare_arrows', '/transferts', 'transferts', '9', '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('13', 'sorties', 'Sorties', NULL, 'exit_to_app', '/sorties', 'sorties', '9', '4', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('14', 'consultations', 'Consultations', NULL, 'medical_services', '/consultations', 'consultations', NULL, '5', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('15', 'consultations.list', 'Liste des consultations', NULL, 'list', '/consultations/list', 'consultations', '14', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('16', 'consultations.create', 'Nouvelle consultation', NULL, 'add', '/consultations/create', 'consultations', '14', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('17', 'rendez_vous', 'Rendez-vous', NULL, 'event', '/rendez-vous', 'rendez_vous', NULL, '6', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('18', 'rendez_vous.calendar', 'Calendrier', NULL, 'calendar_today', '/rendez-vous/calendar', 'rendez_vous', '17', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('19', 'rendez_vous.list', 'Liste', NULL, 'list', '/rendez-vous/list', 'rendez_vous', '17', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('20', 'rendez_vous.create', 'Nouveau rendez-vous', NULL, 'add', '/rendez-vous/create', 'rendez_vous', '17', '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('21', 'creneaux', 'Créneaux', NULL, 'schedule', '/creneaux', 'creneaux', '17', '4', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('22', 'prescriptions', 'Prescriptions', NULL, 'prescription', '/prescriptions', 'prescriptions', NULL, '7', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('23', 'prescriptions.list', 'Liste des prescriptions', NULL, 'list', '/prescriptions/list', 'prescriptions', '22', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('24', 'prescriptions.create', 'Nouvelle prescription', NULL, 'add', '/prescriptions/create', 'prescriptions', '22', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('25', 'prescriptions.valider', 'Valider', NULL, 'check_circle', '/prescriptions/valider', 'prescriptions', '22', '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('26', 'medicaments', 'Médicaments', NULL, 'medication', '/medicaments', 'medicaments', '22', '4', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('27', 'administrations', 'Administrations', NULL, 'local_hospital', '/administrations', 'administrations', '22', '5', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('28', 'pharmacie', 'Pharmacie', NULL, 'local_pharmacy', '/pharmacie', 'pharmacie', NULL, '8', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('29', 'stocks_pharmacie', 'Stocks', NULL, 'inventory', '/pharmacie/stocks', 'stocks_pharmacie', '28', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('30', 'stocks_pharmacie.list', 'Consulter stocks', NULL, 'list', '/pharmacie/stocks/list', 'stocks_pharmacie', '29', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('31', 'stocks_pharmacie.create', 'Ajouter stock', NULL, 'add', '/pharmacie/stocks/create', 'stocks_pharmacie', '29', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('32', 'distributions_pharmacie', 'Distributions', NULL, 'local_shipping', '/pharmacie/distributions', 'distributions_pharmacie', '28', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('33', 'distributions_pharmacie.list', 'Liste', NULL, 'list', '/pharmacie/distributions/list', 'distributions_pharmacie', '32', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('34', 'distributions_pharmacie.create', 'Nouvelle distribution', NULL, 'add', '/pharmacie/distributions/create', 'distributions_pharmacie', '32', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('35', 'laboratoire', 'Laboratoire', NULL, 'science', '/laboratoire', 'laboratoire', NULL, '9', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('36', 'ordonnances_labo', 'Ordonnances', NULL, 'description', '/laboratoire/ordonnances', 'ordonnances_labo', '35', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('37', 'ordonnances_labo.list', 'Liste', NULL, 'list', '/laboratoire/ordonnances/list', 'ordonnances_labo', '36', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('38', 'ordonnances_labo.create', 'Nouvelle ordonnance', NULL, 'add', '/laboratoire/ordonnances/create', 'ordonnances_labo', '36', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('39', 'prelevements', 'Prélèvements', NULL, 'bloodtype', '/laboratoire/prelevements', 'prelevements', '35', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('40', 'prelevements.list', 'Liste', NULL, 'list', '/laboratoire/prelevements/list', 'prelevements', '39', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('41', 'prelevements.create', 'Nouveau prélèvement', NULL, 'add', '/laboratoire/prelevements/create', 'prelevements', '39', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('42', 'resultats_labo', 'Résultats', NULL, 'assessment', '/laboratoire/resultats', 'resultats_labo', '35', '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('43', 'resultats_labo.list', 'Liste', NULL, 'list', '/laboratoire/resultats/list', 'resultats_labo', '42', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('44', 'resultats_labo.create', 'Nouveau résultat', NULL, 'add', '/laboratoire/resultats/create', 'resultats_labo', '42', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('45', 'imagerie', 'Imagerie', NULL, 'image', '/imagerie', 'imagerie', NULL, '10', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('46', 'ordonnances_imagerie', 'Ordonnances', NULL, 'description', '/imagerie/ordonnances', 'ordonnances_imagerie', '45', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('47', 'ordonnances_imagerie.list', 'Liste', NULL, 'list', '/imagerie/ordonnances/list', 'ordonnances_imagerie', '46', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('48', 'ordonnances_imagerie.create', 'Nouvelle ordonnance', NULL, 'add', '/imagerie/ordonnances/create', 'ordonnances_imagerie', '46', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('49', 'examens_imagerie', 'Examens', NULL, 'image_search', '/imagerie/examens', 'examens_imagerie', '45', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('50', 'examens_imagerie.list', 'Liste', NULL, 'list', '/imagerie/examens/list', 'examens_imagerie', '49', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('51', 'examens_imagerie.create', 'Nouvel examen', NULL, 'add', '/imagerie/examens/create', 'examens_imagerie', '49', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('52', 'rapports_radiologiques', 'Rapports', NULL, 'description', '/imagerie/rapports', 'rapports_radiologiques', '45', '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('53', 'rapports_radiologiques.list', 'Liste', NULL, 'list', '/imagerie/rapports/list', 'rapports_radiologiques', '52', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('54', 'rapports_radiologiques.create', 'Nouveau rapport', NULL, 'add', '/imagerie/rapports/create', 'rapports_radiologiques', '52', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('55', 'chirurgie', 'Chirurgie', NULL, 'local_hospital', '/chirurgie', 'chirurgie', NULL, '11', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('56', 'demandes_interventions', 'Demandes', NULL, 'request_page', '/chirurgie/demandes', 'demandes_interventions', '55', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('57', 'demandes_interventions.list', 'Liste', NULL, 'list', '/chirurgie/demandes/list', 'demandes_interventions', '56', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('58', 'demandes_interventions.create', 'Nouvelle demande', NULL, 'add', '/chirurgie/demandes/create', 'demandes_interventions', '56', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('59', 'planning_operatoire', 'Planning', NULL, 'calendar_month', '/chirurgie/planning', 'planning_operatoire', '55', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('60', 'planning_operatoire.list', 'Liste', NULL, 'list', '/chirurgie/planning/list', 'planning_operatoire', '59', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('61', 'planning_operatoire.create', 'Nouveau planning', NULL, 'add', '/chirurgie/planning/create', 'planning_operatoire', '59', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('62', 'rapports_operatoires', 'Rapports', NULL, 'description', '/chirurgie/rapports', 'rapports_operatoires', '55', '3', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('63', 'rapports_operatoires.list', 'Liste', NULL, 'list', '/chirurgie/rapports/list', 'rapports_operatoires', '62', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('64', 'rapports_operatoires.create', 'Nouveau rapport', NULL, 'add', '/chirurgie/rapports/create', 'rapports_operatoires', '62', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('65', 'urgences', 'Urgences', NULL, 'emergency', '/urgences', 'urgences', NULL, '12', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('66', 'triages', 'Triages', NULL, 'priority_high', '/urgences/triages', 'triages', '65', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('67', 'triages.list', 'Liste', NULL, 'list', '/urgences/triages/list', 'triages', '66', '1', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('68', 'triages.create', 'Nouveau triage', NULL, 'add', '/urgences/triages/create', 'triages', '66', '2', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('69', 'facturation', 'Facturation', NULL, 'receipt', '/facturation', 'facturation', NULL, '13', '1', '2026-01-03 10:28:58');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('70', 'factures', 'Factures', NULL, 'description', '/facturation/factures', 'factures', '69', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('71', 'factures.list', 'Liste', NULL, 'list', '/facturation/factures/list', 'factures', '70', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('72', 'factures.create', 'Nouvelle facture', NULL, 'add', '/facturation/factures/create', 'factures', '70', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('73', 'paiements', 'Paiements', NULL, 'payment', '/facturation/paiements', 'paiements', '69', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('74', 'paiements.list', 'Liste', NULL, 'list', '/facturation/paiements/list', 'paiements', '73', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('75', 'paiements.create', 'Nouveau paiement', NULL, 'add', '/facturation/paiements/create', 'paiements', '73', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('76', 'reclamations_assurance', 'Réclamations', NULL, 'warning', '/facturation/reclamations', 'reclamations_assurance', '69', '3', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('77', 'rh', 'Ressources Humaines', NULL, 'group', '/rh', 'rh', NULL, '14', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('78', 'utilisateurs', 'Utilisateurs', NULL, 'person', '/rh/utilisateurs', 'utilisateurs', '77', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('79', 'utilisateurs.list', 'Liste', NULL, 'list', '/rh/utilisateurs/list', 'utilisateurs', '78', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('80', 'utilisateurs.create', 'Nouvel utilisateur', NULL, 'add', '/rh/utilisateurs/create', 'utilisateurs', '78', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('81', 'formations', 'Formations', NULL, 'school', '/rh/formations', 'formations', '77', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('82', 'formations.list', 'Liste', NULL, 'list', '/rh/formations/list', 'formations', '81', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('83', 'formations.create', 'Nouvelle formation', NULL, 'add', '/rh/formations/create', 'formations', '81', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('84', 'conges', 'Congés', NULL, 'beach_access', '/rh/conges', 'conges', '77', '3', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('85', 'conges.list', 'Liste', NULL, 'list', '/rh/conges/list', 'conges', '84', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('86', 'conges.create', 'Demander un congé', NULL, 'add', '/rh/conges/create', 'conges', '84', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('87', 'paie', 'Paie', NULL, 'attach_money', '/rh/paie', 'paie', '77', '4', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('88', 'paie.list', 'Bulletins', NULL, 'list', '/rh/paie/list', 'paie', '87', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('89', 'paie.create', 'Nouveau bulletin', NULL, 'add', '/rh/paie/create', 'paie', '87', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('90', 'administration', 'Administration', NULL, 'admin_panel_settings', '/administration', 'administration', NULL, '15', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('91', 'hopitaux', 'Hôpitaux', NULL, 'business', '/administration/hopitaux', 'hopitaux', '90', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('92', 'hopitaux.list', 'Liste', NULL, 'list', '/administration/hopitaux/list', 'hopitaux', '91', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('93', 'hopitaux.create', 'Nouvel hôpital', NULL, 'add', '/administration/hopitaux/create', 'hopitaux', '91', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('94', 'services', 'Services', NULL, 'domain', '/administration/services', 'services', '90', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('95', 'services.list', 'Liste', NULL, 'list', '/administration/services/list', 'services', '94', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('96', 'services.create', 'Nouveau service', NULL, 'add', '/administration/services/create', 'services', '94', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('97', 'lits', 'Lits et chambres', NULL, 'bed', '/administration/lits', 'lits', '90', '3', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('98', 'lits.list', 'Liste', NULL, 'list', '/administration/lits/list', 'lits', '97', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('99', 'lits.create', 'Nouveau lit', NULL, 'add', '/administration/lits/create', 'lits', '97', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('100', 'equipements', 'Équipements', NULL, 'devices', '/administration/equipements', 'equipements', '90', '4', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('101', 'equipements.list', 'Liste', NULL, 'list', '/administration/equipements/list', 'equipements', '100', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('102', 'equipements.create', 'Nouvel équipement', NULL, 'add', '/administration/equipements/create', 'equipements', '100', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('103', 'maintenance', 'Maintenance', NULL, 'build', '/administration/maintenance', 'maintenance', '90', '5', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('104', 'maintenance.list', 'Liste', NULL, 'list', '/administration/maintenance/list', 'maintenance', '103', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('105', 'maintenance.create', 'Nouvelle intervention', NULL, 'add', '/administration/maintenance/create', 'maintenance', '103', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('106', 'fournisseurs', 'Fournisseurs', NULL, 'local_shipping', '/administration/fournisseurs', 'fournisseurs', '90', '6', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('107', 'fournisseurs.list', 'Liste', NULL, 'list', '/administration/fournisseurs/list', 'fournisseurs', '106', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('108', 'fournisseurs.create', 'Nouveau fournisseur', NULL, 'add', '/administration/fournisseurs/create', 'fournisseurs', '106', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('109', 'bons_commande', 'Bons de commande', NULL, 'shopping_cart', '/administration/bons-commande', 'bons_commande', '90', '7', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('110', 'bons_commande.list', 'Liste', NULL, 'list', '/administration/bons-commande/list', 'bons_commande', '109', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('111', 'bons_commande.create', 'Nouveau bon', NULL, 'add', '/administration/bons-commande/create', 'bons_commande', '109', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('112', 'rapports', 'Rapports', NULL, 'bar_chart', '/rapports', 'rapports', NULL, '16', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('113', 'rapports.list', 'Mes rapports', NULL, 'list', '/rapports/list', 'rapports', '112', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('114', 'rapports.create', 'Créer un rapport', NULL, 'add', '/rapports/create', 'rapports', '112', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('115', 'indicateurs', 'Indicateurs', NULL, 'trending_up', '/rapports/indicateurs', 'indicateurs', '112', '3', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('116', 'plaintes', 'Plaintes & Incidents', NULL, 'warning', '/rapports/plaintes', 'plaintes', '112', '4', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('117', 'parametres', 'Paramètres', NULL, 'settings', '/parametres', 'parametres', NULL, '17', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('118', 'roles', 'Rôles & Permissions', NULL, 'security', '/parametres/roles', 'roles', '117', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('119', 'roles.list', 'Liste des rôles', NULL, 'list', '/parametres/roles/list', 'roles', '118', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('120', 'roles.create', 'Nouveau rôle', NULL, 'add', '/parametres/roles/create', 'roles', '118', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('121', 'logs', 'Logs & Audit', NULL, 'history', '/parametres/logs', 'logs', '117', '2', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('122', 'logs.audit', 'Audit', NULL, 'list', '/parametres/logs/audit', 'logs', '121', '1', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('123', 'sauvegardes', 'Sauvegardes', NULL, 'backup', '/parametres/sauvegardes', 'sauvegardes', '117', '3', '1', '2026-01-03 10:28:59');
INSERT INTO `menus` (`id`, `code`, `nom`, `description`, `icone`, `route`, `module`, `parent_id`, `ordre`, `actif`, `date_creation`) VALUES ('124', 'archives', 'Archives', NULL, 'archive', '/parametres/archives', 'archives', '117', '4', '1', '2026-01-03 10:28:59');

DROP TABLE IF EXISTS `messages_securises`;
CREATE TABLE `messages_securises` (
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
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `messages_securises_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_securises_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_securises_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `mesures_indicateurs`;
CREATE TABLE `mesures_indicateurs` (
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
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `mesures_indicateurs_ibfk_1` FOREIGN KEY (`indicateur_id`) REFERENCES `indicateurs_qualite` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mesures_indicateurs_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mesures_indicateurs_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `modes_paiement`;
CREATE TABLE `modes_paiement` (
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


DROP TABLE IF EXISTS `mouvements_stock`;
CREATE TABLE `mouvements_stock` (
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
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `mouvements_stock_ibfk_1` FOREIGN KEY (`stock_id`) REFERENCES `stocks_pharmacie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mouvements_stock_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `niveaux_triage`;
CREATE TABLE `niveaux_triage` (
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


DROP TABLE IF EXISTS `notes_infirmieres`;
CREATE TABLE `notes_infirmieres` (
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
  KEY `idx_date` (`date_note`),
  CONSTRAINT `notes_infirmieres_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notes_infirmieres_ibfk_2` FOREIGN KEY (`infirmier_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
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
  KEY `idx_date` (`date_notification`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `ordonnances_imagerie`;
CREATE TABLE `ordonnances_imagerie` (
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
  KEY `admission_id` (`admission_id`),
  CONSTRAINT `ordonnances_imagerie_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ordonnances_imagerie_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `ordonnances_imagerie_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `ordonnances_imagerie_ibfk_4` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordonnances_imagerie_ibfk_5` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `ordonnances_labo`;
CREATE TABLE `ordonnances_labo` (
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
  KEY `admission_id` (`admission_id`),
  CONSTRAINT `ordonnances_labo_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ordonnances_labo_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `ordonnances_labo_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `ordonnances_labo_ibfk_4` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ordonnances_labo_ibfk_5` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `paiements`;
CREATE TABLE `paiements` (
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
  KEY `mode_paiement_id` (`mode_paiement_id`),
  CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `paiements_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `paiements_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `paiements_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `paiements_ibfk_5` FOREIGN KEY (`mode_paiement_id`) REFERENCES `modes_paiement` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `panel_examens`;
CREATE TABLE `panel_examens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `panel_id` int NOT NULL,
  `examen_id` int NOT NULL,
  `ordre` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_panel_examen` (`panel_id`,`examen_id`),
  KEY `examen_id` (`examen_id`),
  CONSTRAINT `panel_examens_ibfk_1` FOREIGN KEY (`panel_id`) REFERENCES `panels_examens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `panel_examens_ibfk_2` FOREIGN KEY (`examen_id`) REFERENCES `types_examens_labo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `panels_examens`;
CREATE TABLE `panels_examens` (
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
  KEY `taux_tva_id` (`taux_tva_id`),
  CONSTRAINT `panels_examens_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `panels_examens_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `parametres_configuration`;
CREATE TABLE `parametres_configuration` (
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
  KEY `mode_paiement_defaut_id` (`mode_paiement_defaut_id`),
  CONSTRAINT `parametres_configuration_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parametres_configuration_ibfk_2` FOREIGN KEY (`devise_defaut_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `parametres_configuration_ibfk_3` FOREIGN KEY (`mode_paiement_defaut_id`) REFERENCES `modes_paiement` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `participations_formations`;
CREATE TABLE `participations_formations` (
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
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `participations_formations_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `participations_formations_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
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
  `antecedents_familiaux_pere` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Antécédents médicaux du père',
  `antecedents_familiaux_mere` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Antécédents médicaux de la mère',
  `antecedents_familiaux_enfants` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Antécédents médicaux des enfants',
  `antecedents_familiaux_epouse` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Antécédents médicaux du conjoint/conjointe',
  `antecedents_familiaux_autres` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Autres antécédents familiaux pertinents',
  `historique_vaccinations` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Historique complet des vaccinations (JSON format)',
  `date_derniere_vaccination` date DEFAULT NULL COMMENT 'Date de la dernière vaccination',
  `habitudes_vie` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Tabagisme, alcool, drogues, etc.',
  `facteurs_risque` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Facteurs de risque identifiés',
  `observations_generales` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Observations générales du dossier médical',
  `date_derniere_mise_a_jour_dossier` datetime DEFAULT NULL COMMENT 'Date de la dernière mise à jour du dossier médical',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_dossier` (`hopital_id`,`numero_dossier`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_nom_prenom` (`nom`,`prenom`),
  KEY `idx_numero_identite` (`numero_identite`),
  KEY `idx_date_naissance` (`date_naissance`),
  CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `patients` (`id`, `hopital_id`, `numero_dossier`, `nom`, `prenom`, `date_naissance`, `sexe`, `numero_identite`, `type_identite`, `adresse`, `ville`, `code_postal`, `telephone`, `email`, `contact_urgence_nom`, `contact_urgence_telephone`, `contact_urgence_lien`, `groupe_sanguin`, `allergies`, `antecedents_medicaux`, `antecedents_chirurgicaux`, `medicaments_actuels`, `statut_civil`, `profession`, `nationalite`, `langue_preference`, `photo_patient`, `actif`, `date_creation`, `date_modification`, `antecedents_familiaux_pere`, `antecedents_familiaux_mere`, `antecedents_familiaux_enfants`, `antecedents_familiaux_epouse`, `antecedents_familiaux_autres`, `historique_vaccinations`, `date_derniere_vaccination`, `habitudes_vie`, `facteurs_risque`, `observations_generales`, `date_derniere_mise_a_jour_dossier`) VALUES ('1', '1', 'PAT-2024-001', 'Dupont', 'Jean', '1980-05-15', 'M', '123456789', 'Passeport', '123 Rue de la Paix', 'Paris', '75001', '+33612345678', 'jean.dupont@email.com', 'Marie Dupont', '+33687654321', 'Épouse', 'O+', 'Pénicilline', 'Diabète type 2', 'Appendicectomie 2010', 'Metformine 500mg', 'Marié', 'Ingénieur', 'Française', 'Français', NULL, '1', '2026-01-06 17:21:29', '2026-01-07 05:09:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `patients` (`id`, `hopital_id`, `numero_dossier`, `nom`, `prenom`, `date_naissance`, `sexe`, `numero_identite`, `type_identite`, `adresse`, `ville`, `code_postal`, `telephone`, `email`, `contact_urgence_nom`, `contact_urgence_telephone`, `contact_urgence_lien`, `groupe_sanguin`, `allergies`, `antecedents_medicaux`, `antecedents_chirurgicaux`, `medicaments_actuels`, `statut_civil`, `profession`, `nationalite`, `langue_preference`, `photo_patient`, `actif`, `date_creation`, `date_modification`, `antecedents_familiaux_pere`, `antecedents_familiaux_mere`, `antecedents_familiaux_enfants`, `antecedents_familiaux_epouse`, `antecedents_familiaux_autres`, `historique_vaccinations`, `date_derniere_vaccination`, `habitudes_vie`, `facteurs_risque`, `observations_generales`, `date_derniere_mise_a_jour_dossier`) VALUES ('2', '1', 'PAT-2024-002', 'Bros', 'Man', '1980-05-15', 'M', '123456789', 'Passeport', '123 Rue de la Paix', 'Paris', '75001', '+33612345678', 'jean.dupont@email.com', 'Marie Dupont', '+33687654321', 'Épouse', 'O+', 'Pénicilline', 'Diabète type 2', 'Appendicectomie 2010', 'Metformine 500mg', 'Marié', 'Ingénieur', 'Française', 'Français', NULL, '1', '2026-01-06 20:30:54', '2026-01-08 16:27:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-08 16:27:39');
INSERT INTO `patients` (`id`, `hopital_id`, `numero_dossier`, `nom`, `prenom`, `date_naissance`, `sexe`, `numero_identite`, `type_identite`, `adresse`, `ville`, `code_postal`, `telephone`, `email`, `contact_urgence_nom`, `contact_urgence_telephone`, `contact_urgence_lien`, `groupe_sanguin`, `allergies`, `antecedents_medicaux`, `antecedents_chirurgicaux`, `medicaments_actuels`, `statut_civil`, `profession`, `nationalite`, `langue_preference`, `photo_patient`, `actif`, `date_creation`, `date_modification`, `antecedents_familiaux_pere`, `antecedents_familiaux_mere`, `antecedents_familiaux_enfants`, `antecedents_familiaux_epouse`, `antecedents_familiaux_autres`, `historique_vaccinations`, `date_derniere_vaccination`, `habitudes_vie`, `facteurs_risque`, `observations_generales`, `date_derniere_mise_a_jour_dossier`) VALUES ('3', '1', 'PAT-2026-000003', 'Bros braoss', 'Man', '2000-12-11', 'M', NULL, NULL, NULL, NULL, NULL, '0992373634', 'a@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '2026-01-09 14:09:08', '2026-01-09 14:09:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `patients` (`id`, `hopital_id`, `numero_dossier`, `nom`, `prenom`, `date_naissance`, `sexe`, `numero_identite`, `type_identite`, `adresse`, `ville`, `code_postal`, `telephone`, `email`, `contact_urgence_nom`, `contact_urgence_telephone`, `contact_urgence_lien`, `groupe_sanguin`, `allergies`, `antecedents_medicaux`, `antecedents_chirurgicaux`, `medicaments_actuels`, `statut_civil`, `profession`, `nationalite`, `langue_preference`, `photo_patient`, `actif`, `date_creation`, `date_modification`, `antecedents_familiaux_pere`, `antecedents_familiaux_mere`, `antecedents_familiaux_enfants`, `antecedents_familiaux_epouse`, `antecedents_familiaux_autres`, `historique_vaccinations`, `date_derniere_vaccination`, `habitudes_vie`, `facteurs_risque`, `observations_generales`, `date_derniere_mise_a_jour_dossier`) VALUES ('4', '1', 'PAT-2026-000004', 'sarah', 'belle', '2000-06-09', 'F', NULL, NULL, NULL, NULL, NULL, '0998998999', 'sarah@gmail.com', NULL, NULL, NULL, 'A-', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '2026-01-09 22:43:08', '2026-01-09 22:43:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
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

INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('1', 'patients.consulter', 'Consulter les patients', NULL, 'patients', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('2', 'patients.creer', 'Créer un patient', NULL, 'patients', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('3', 'patients.modifier', 'Modifier un patient', NULL, 'patients', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('4', 'patients.supprimer', 'Supprimer un patient', NULL, 'patients', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('5', 'patients.exporter', 'Exporter les données patients', NULL, 'patients', 'exporter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('6', 'dossiers_medicaux.consulter', 'Consulter les dossiers médicaux', NULL, 'dossiers_medicaux', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('7', 'dossiers_medicaux.creer', 'Créer un dossier médical', NULL, 'dossiers_medicaux', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('8', 'dossiers_medicaux.modifier', 'Modifier un dossier médical', NULL, 'dossiers_medicaux', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('9', 'dossiers_medicaux.supprimer', 'Supprimer un dossier médical', NULL, 'dossiers_medicaux', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('10', 'admissions.consulter', 'Consulter les admissions', NULL, 'admissions', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('11', 'admissions.creer', 'Créer une admission', NULL, 'admissions', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('12', 'admissions.modifier', 'Modifier une admission', NULL, 'admissions', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('13', 'admissions.supprimer', 'Supprimer une admission', NULL, 'admissions', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('14', 'transferts.consulter', 'Consulter les transferts', NULL, 'transferts', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('15', 'transferts.creer', 'Créer un transfert', NULL, 'transferts', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('16', 'transferts.modifier', 'Modifier un transfert', NULL, 'transferts', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('17', 'sorties.consulter', 'Consulter les sorties', NULL, 'sorties', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('18', 'sorties.creer', 'Créer une sortie', NULL, 'sorties', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('19', 'sorties.modifier', 'Modifier une sortie', NULL, 'sorties', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('20', 'consultations.consulter', 'Consulter les consultations', NULL, 'consultations', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('21', 'consultations.creer', 'Créer une consultation', NULL, 'consultations', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('22', 'consultations.modifier', 'Modifier une consultation', NULL, 'consultations', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('23', 'consultations.supprimer', 'Supprimer une consultation', NULL, 'consultations', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('24', 'rendez_vous.consulter', 'Consulter les rendez-vous', NULL, 'rendez_vous', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('25', 'rendez_vous.creer', 'Créer un rendez-vous', NULL, 'rendez_vous', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('26', 'rendez_vous.modifier', 'Modifier un rendez-vous', NULL, 'rendez_vous', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('27', 'rendez_vous.annuler', 'Annuler un rendez-vous', NULL, 'rendez_vous', 'annuler', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('28', 'rendez_vous.confirmer', 'Confirmer un rendez-vous', NULL, 'rendez_vous', 'confirmer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('29', 'creneaux.consulter', 'Consulter les créneaux', NULL, 'creneaux', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('30', 'creneaux.creer', 'Créer un créneau', NULL, 'creneaux', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('31', 'creneaux.modifier', 'Modifier un créneau', NULL, 'creneaux', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('32', 'creneaux.supprimer', 'Supprimer un créneau', NULL, 'creneaux', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('33', 'prescriptions.consulter', 'Consulter les prescriptions', NULL, 'prescriptions', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('34', 'prescriptions.creer', 'Créer une prescription', NULL, 'prescriptions', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('35', 'prescriptions.modifier', 'Modifier une prescription', NULL, 'prescriptions', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('36', 'prescriptions.valider', 'Valider une prescription', NULL, 'prescriptions', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('37', 'prescriptions.annuler', 'Annuler une prescription', NULL, 'prescriptions', 'annuler', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('38', 'medicaments.consulter', 'Consulter les médicaments', NULL, 'medicaments', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('39', 'medicaments.creer', 'Créer un médicament', NULL, 'medicaments', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('40', 'medicaments.modifier', 'Modifier un médicament', NULL, 'medicaments', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('41', 'medicaments.supprimer', 'Supprimer un médicament', NULL, 'medicaments', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('42', 'administrations.consulter', 'Consulter les administrations', NULL, 'administrations', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('43', 'administrations.creer', 'Enregistrer une administration', NULL, 'administrations', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('44', 'administrations.modifier', 'Modifier une administration', NULL, 'administrations', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('45', 'ordonnances_labo.consulter', 'Consulter les ordonnances labo', NULL, 'ordonnances_labo', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('46', 'ordonnances_labo.creer', 'Créer une ordonnance labo', NULL, 'ordonnances_labo', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('47', 'ordonnances_labo.modifier', 'Modifier une ordonnance labo', NULL, 'ordonnances_labo', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('48', 'ordonnances_labo.valider', 'Valider une ordonnance labo', NULL, 'ordonnances_labo', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('49', 'prelevements.consulter', 'Consulter les prélèvements', NULL, 'prelevements', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('50', 'prelevements.creer', 'Créer un prélèvement', NULL, 'prelevements', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('51', 'prelevements.modifier', 'Modifier un prélèvement', NULL, 'prelevements', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('52', 'prelevements.recevoir', 'Recevoir un prélèvement', NULL, 'prelevements', 'recevoir', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('53', 'resultats_labo.consulter', 'Consulter les résultats labo', NULL, 'resultats_labo', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('54', 'resultats_labo.creer', 'Créer un résultat labo', NULL, 'resultats_labo', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('55', 'resultats_labo.modifier', 'Modifier un résultat labo', NULL, 'resultats_labo', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('56', 'resultats_labo.valider', 'Valider un résultat labo', NULL, 'resultats_labo', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('57', 'ordonnances_imagerie.consulter', 'Consulter les ordonnances imagerie', NULL, 'ordonnances_imagerie', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('58', 'ordonnances_imagerie.creer', 'Créer une ordonnance imagerie', NULL, 'ordonnances_imagerie', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('59', 'ordonnances_imagerie.modifier', 'Modifier une ordonnance imagerie', NULL, 'ordonnances_imagerie', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('60', 'ordonnances_imagerie.valider', 'Valider une ordonnance imagerie', NULL, 'ordonnances_imagerie', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('61', 'examens_imagerie.consulter', 'Consulter les examens imagerie', NULL, 'examens_imagerie', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('62', 'examens_imagerie.creer', 'Créer un examen imagerie', NULL, 'examens_imagerie', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('63', 'examens_imagerie.modifier', 'Modifier un examen imagerie', NULL, 'examens_imagerie', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('64', 'rapports_radiologiques.consulter', 'Consulter les rapports radiologiques', NULL, 'rapports_radiologiques', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('65', 'rapports_radiologiques.creer', 'Créer un rapport radiologique', NULL, 'rapports_radiologiques', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('66', 'rapports_radiologiques.modifier', 'Modifier un rapport radiologique', NULL, 'rapports_radiologiques', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('67', 'rapports_radiologiques.valider', 'Valider un rapport radiologique', NULL, 'rapports_radiologiques', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('68', 'demandes_interventions.consulter', 'Consulter les demandes d\'intervention', NULL, 'demandes_interventions', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('69', 'demandes_interventions.creer', 'Créer une demande d\'intervention', NULL, 'demandes_interventions', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('70', 'demandes_interventions.modifier', 'Modifier une demande d\'intervention', NULL, 'demandes_interventions', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('71', 'demandes_interventions.valider', 'Valider une demande d\'intervention', NULL, 'demandes_interventions', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('72', 'planning_operatoire.consulter', 'Consulter le planning opératoire', NULL, 'planning_operatoire', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('73', 'planning_operatoire.creer', 'Créer un planning opératoire', NULL, 'planning_operatoire', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('74', 'planning_operatoire.modifier', 'Modifier un planning opératoire', NULL, 'planning_operatoire', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('75', 'planning_operatoire.annuler', 'Annuler un planning opératoire', NULL, 'planning_operatoire', 'annuler', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('76', 'rapports_operatoires.consulter', 'Consulter les rapports opératoires', NULL, 'rapports_operatoires', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('77', 'rapports_operatoires.creer', 'Créer un rapport opératoire', NULL, 'rapports_operatoires', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('78', 'rapports_operatoires.modifier', 'Modifier un rapport opératoire', NULL, 'rapports_operatoires', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('79', 'rapports_operatoires.valider', 'Valider un rapport opératoire', NULL, 'rapports_operatoires', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('80', 'triages.consulter', 'Consulter les triages', NULL, 'triages', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('81', 'triages.creer', 'Créer un triage', NULL, 'triages', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('82', 'triages.modifier', 'Modifier un triage', NULL, 'triages', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('83', 'stocks_pharmacie.consulter', 'Consulter les stocks pharmacie', NULL, 'stocks_pharmacie', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('84', 'stocks_pharmacie.creer', 'Créer un stock pharmacie', NULL, 'stocks_pharmacie', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('85', 'stocks_pharmacie.modifier', 'Modifier un stock pharmacie', NULL, 'stocks_pharmacie', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('86', 'distributions_pharmacie.consulter', 'Consulter les distributions pharmacie', NULL, 'distributions_pharmacie', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('87', 'distributions_pharmacie.creer', 'Créer une distribution pharmacie', NULL, 'distributions_pharmacie', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('88', 'distributions_pharmacie.modifier', 'Modifier une distribution pharmacie', NULL, 'distributions_pharmacie', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('89', 'factures.consulter', 'Consulter les factures', NULL, 'factures', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('90', 'factures.creer', 'Créer une facture', NULL, 'factures', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('91', 'factures.modifier', 'Modifier une facture', NULL, 'factures', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('92', 'factures.valider', 'Valider une facture', NULL, 'factures', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('93', 'factures.annuler', 'Annuler une facture', NULL, 'factures', 'annuler', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('94', 'paiements.consulter', 'Consulter les paiements', NULL, 'paiements', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('95', 'paiements.creer', 'Créer un paiement', NULL, 'paiements', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('96', 'paiements.modifier', 'Modifier un paiement', NULL, 'paiements', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('97', 'paiements.annuler', 'Annuler un paiement', NULL, 'paiements', 'annuler', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('98', 'reclamations_assurance.consulter', 'Consulter les réclamations assurance', NULL, 'reclamations_assurance', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('99', 'reclamations_assurance.creer', 'Créer une réclamation assurance', NULL, 'reclamations_assurance', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('100', 'reclamations_assurance.modifier', 'Modifier une réclamation assurance', NULL, 'reclamations_assurance', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('101', 'utilisateurs.consulter', 'Consulter les utilisateurs', NULL, 'utilisateurs', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('102', 'utilisateurs.creer', 'Créer un utilisateur', NULL, 'utilisateurs', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('103', 'utilisateurs.modifier', 'Modifier un utilisateur', NULL, 'utilisateurs', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('104', 'utilisateurs.supprimer', 'Supprimer un utilisateur', NULL, 'utilisateurs', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('105', 'utilisateurs.reinitialiser_mdp', 'Réinitialiser le mot de passe', NULL, 'utilisateurs', 'reinitialiser_mdp', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('106', 'formations.consulter', 'Consulter les formations', NULL, 'formations', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('107', 'formations.creer', 'Créer une formation', NULL, 'formations', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('108', 'formations.modifier', 'Modifier une formation', NULL, 'formations', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('109', 'formations.supprimer', 'Supprimer une formation', NULL, 'formations', 'supprimer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('110', 'conges.consulter', 'Consulter les congés', NULL, 'conges', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('111', 'conges.creer', 'Demander un congé', NULL, 'conges', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('112', 'conges.modifier', 'Modifier un congé', NULL, 'conges', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('113', 'conges.approuver', 'Approuver un congé', NULL, 'conges', 'approuver', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('114', 'conges.rejeter', 'Rejeter un congé', NULL, 'conges', 'rejeter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('115', 'paie.consulter', 'Consulter la paie', NULL, 'paie', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('116', 'paie.creer', 'Créer un bulletin de paie', NULL, 'paie', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('117', 'paie.modifier', 'Modifier un bulletin de paie', NULL, 'paie', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('118', 'hopitaux.consulter', 'Consulter les hôpitaux', NULL, 'hopitaux', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('119', 'hopitaux.creer', 'Créer un hôpital', NULL, 'hopitaux', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('120', 'hopitaux.modifier', 'Modifier un hôpital', NULL, 'hopitaux', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('121', 'services.consulter', 'Consulter les services', NULL, 'services', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('122', 'services.creer', 'Créer un service', NULL, 'services', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('123', 'services.modifier', 'Modifier un service', NULL, 'services', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('124', 'lits.consulter', 'Consulter les lits', NULL, 'lits', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('125', 'lits.creer', 'Créer un lit', NULL, 'lits', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('126', 'lits.modifier', 'Modifier un lit', NULL, 'lits', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('127', 'fournisseurs.consulter', 'Consulter les fournisseurs', NULL, 'fournisseurs', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('128', 'fournisseurs.creer', 'Créer un fournisseur', NULL, 'fournisseurs', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('129', 'fournisseurs.modifier', 'Modifier un fournisseur', NULL, 'fournisseurs', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('130', 'bons_commande.consulter', 'Consulter les bons de commande', NULL, 'bons_commande', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('131', 'bons_commande.creer', 'Créer un bon de commande', NULL, 'bons_commande', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('132', 'bons_commande.modifier', 'Modifier un bon de commande', NULL, 'bons_commande', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('133', 'bons_commande.valider', 'Valider un bon de commande', NULL, 'bons_commande', 'valider', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('134', 'equipements.consulter', 'Consulter les équipements', NULL, 'equipements', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('135', 'equipements.creer', 'Créer un équipement', NULL, 'equipements', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('136', 'equipements.modifier', 'Modifier un équipement', NULL, 'equipements', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('137', 'maintenance.consulter', 'Consulter la maintenance', NULL, 'maintenance', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('138', 'maintenance.creer', 'Créer une maintenance', NULL, 'maintenance', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('139', 'maintenance.modifier', 'Modifier une maintenance', NULL, 'maintenance', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('140', 'plaintes.consulter', 'Consulter les plaintes', NULL, 'plaintes', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('141', 'plaintes.creer', 'Créer une plainte', NULL, 'plaintes', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('142', 'plaintes.modifier', 'Modifier une plainte', NULL, 'plaintes', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('143', 'plaintes.resoudre', 'Résoudre une plainte', NULL, 'plaintes', 'resoudre', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('144', 'indicateurs.consulter', 'Consulter les indicateurs', NULL, 'indicateurs', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('145', 'indicateurs.creer', 'Créer un indicateur', NULL, 'indicateurs', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('146', 'indicateurs.modifier', 'Modifier un indicateur', NULL, 'indicateurs', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('147', 'rapports.consulter', 'Consulter les rapports', NULL, 'rapports', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('148', 'rapports.creer', 'Créer un rapport', NULL, 'rapports', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('149', 'rapports.modifier', 'Modifier un rapport', NULL, 'rapports', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('150', 'rapports.exporter', 'Exporter un rapport', NULL, 'rapports', 'exporter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('151', 'logs.consulter', 'Consulter les logs', NULL, 'logs', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('152', 'parametres.consulter', 'Consulter les paramètres', NULL, 'parametres', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('153', 'parametres.modifier', 'Modifier les paramètres', NULL, 'parametres', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('154', 'roles.consulter', 'Consulter les rôles', NULL, 'roles', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('155', 'roles.creer', 'Créer un rôle', NULL, 'roles', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('156', 'roles.modifier', 'Modifier un rôle', NULL, 'roles', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('157', 'permissions.consulter', 'Consulter les permissions', NULL, 'permissions', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('158', 'permissions.modifier', 'Modifier les permissions', NULL, 'permissions', 'modifier', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('159', 'sauvegardes.consulter', 'Consulter les sauvegardes', NULL, 'sauvegardes', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('160', 'sauvegardes.creer', 'Créer une sauvegarde', NULL, 'sauvegardes', 'creer', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('161', 'archives.consulter', 'Consulter les archives', NULL, 'archives', 'consulter', '2026-01-03 08:56:40');
INSERT INTO `permissions` (`id`, `code`, `nom`, `description`, `module`, `action`, `date_creation`) VALUES ('162', 'archives.creer', 'Créer une archive', NULL, 'archives', 'creer', '2026-01-03 08:56:40');

DROP TABLE IF EXISTS `plaintes_incidents`;
CREATE TABLE `plaintes_incidents` (
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
  KEY `responsable_investigation_id` (`responsable_investigation_id`),
  CONSTRAINT `plaintes_incidents_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plaintes_incidents_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plaintes_incidents_ibfk_3` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plaintes_incidents_ibfk_4` FOREIGN KEY (`responsable_investigation_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `planning_operatoire`;
CREATE TABLE `planning_operatoire` (
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
  KEY `chirurgien_id` (`chirurgien_id`),
  CONSTRAINT `planning_operatoire_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `planning_operatoire_ibfk_2` FOREIGN KEY (`salle_operation_id`) REFERENCES `salles_operations` (`id`),
  CONSTRAINT `planning_operatoire_ibfk_3` FOREIGN KEY (`demande_intervention_id`) REFERENCES `demandes_interventions` (`id`),
  CONSTRAINT `planning_operatoire_ibfk_4` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `planning_operatoire_ibfk_5` FOREIGN KEY (`chirurgien_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `poles_activite`;
CREATE TABLE `poles_activite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `responsable_id` int DEFAULT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `type_pole` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_pole_id` int DEFAULT NULL,
  `budget_annuel` decimal(12,2) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_responsable` (`responsable_id`),
  KEY `idx_type_pole_id` (`type_pole_id`),
  CONSTRAINT `FK_POLES_HOPITAL` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `FK_POLES_RESPONSABLE` FOREIGN KEY (`responsable_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `FK_POLES_TYPE_POLE` FOREIGN KEY (`type_pole_id`) REFERENCES `types_poles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `poles_activite` (`id`, `hopital_id`, `responsable_id`, `code`, `nom`, `description`, `type_pole`, `type_pole_id`, `budget_annuel`, `actif`, `date_creation`) VALUES ('1', '1', NULL, 'CARDIO', 'Pole cardiologie', NULL, 'Urgences et Soins Critiques', NULL, NULL, '1', '2026-01-11 00:36:02');

DROP TABLE IF EXISTS `prelevements`;
CREATE TABLE `prelevements` (
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
  KEY `infirmier_id` (`infirmier_id`),
  CONSTRAINT `prelevements_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnances_labo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prelevements_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prelevements_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `prelevements_ibfk_4` FOREIGN KEY (`infirmier_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE `prescriptions` (
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
  KEY `admission_id` (`admission_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_ibfk_4` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `prescriptions_ibfk_5` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `profils_utilisateurs`;
CREATE TABLE `profils_utilisateurs` (
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('1', 'admin', 'Administrateur', 'Profil administrateur système', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('2', 'directeur', 'Directeur', 'Profil directeur d\'hôpital', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('3', 'medecin', 'Médecin', 'Profil médecin', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('4', 'infirmier', 'Infirmier', 'Profil infirmier', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('5', 'pharmacien', 'Pharmacien', 'Profil pharmacien', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('6', 'laborantin', 'Laborantin', 'Profil laborantin', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('7', 'radiologue', 'Radiologue', 'Profil radiologue', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('8', 'comptable', 'Comptable', 'Profil comptable', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('9', 'rh', 'Responsable RH', 'Profil responsable RH', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('10', 'maintenance', 'Technicien Maintenance', 'Profil technicien maintenance', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('11', 'receptionniste', 'Réceptionniste', 'Profil réceptionniste', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');
INSERT INTO `profils_utilisateurs` (`id`, `code`, `nom`, `description`, `type_profil`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('12', 'patient', 'Patient', 'Profil patient', NULL, NULL, NULL, '1', '2026-01-03 08:59:47');

DROP TABLE IF EXISTS `projets_recherche`;
CREATE TABLE `projets_recherche` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `projets_recherche_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projets_recherche_ibfk_2` FOREIGN KEY (`chercheur_principal_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `projets_recherche_ibfk_3` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `rapports_operatoires`;
CREATE TABLE `rapports_operatoires` (
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
  KEY `chirurgien_id` (`chirurgien_id`),
  CONSTRAINT `rapports_operatoires_ibfk_1` FOREIGN KEY (`planning_id`) REFERENCES `planning_operatoire` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapports_operatoires_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapports_operatoires_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `rapports_operatoires_ibfk_4` FOREIGN KEY (`chirurgien_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `rapports_personnalises`;
CREATE TABLE `rapports_personnalises` (
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
  KEY `idx_utilisateur` (`utilisateur_id`),
  CONSTRAINT `rapports_personnalises_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapports_personnalises_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `rapports_radiologiques`;
CREATE TABLE `rapports_radiologiques` (
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
  KEY `radiologue_id` (`radiologue_id`),
  CONSTRAINT `rapports_radiologiques_ibfk_1` FOREIGN KEY (`examen_id`) REFERENCES `examens_imagerie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapports_radiologiques_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapports_radiologiques_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `rapports_radiologiques_ibfk_4` FOREIGN KEY (`radiologue_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `reclamations_assurance`;
CREATE TABLE `reclamations_assurance` (
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
  KEY `devise_id` (`devise_id`),
  CONSTRAINT `reclamations_assurance_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reclamations_assurance_ibfk_2` FOREIGN KEY (`convention_id`) REFERENCES `conventions_assurance` (`id`),
  CONSTRAINT `reclamations_assurance_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `reclamations_assurance_ibfk_4` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `rendez_vous`;
CREATE TABLE `rendez_vous` (
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
  KEY `hopital_id` (`hopital_id`),
  CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`creneau_id`) REFERENCES `creneaux_consultation` (`id`),
  CONSTRAINT `rendez_vous_ibfk_3` FOREIGN KEY (`medecin_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `rendez_vous_ibfk_4` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `rendez_vous_ibfk_5` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `resultats_labo`;
CREATE TABLE `resultats_labo` (
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
  KEY `validateur_id` (`validateur_id`),
  CONSTRAINT `resultats_labo_ibfk_1` FOREIGN KEY (`prelevement_id`) REFERENCES `prelevements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resultats_labo_ibfk_2` FOREIGN KEY (`examen_id`) REFERENCES `types_examens_labo` (`id`),
  CONSTRAINT `resultats_labo_ibfk_3` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `resultats_labo_ibfk_4` FOREIGN KEY (`validateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
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

INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('1', 'ROLE_ADMIN', 'Administrateur', 'Accès complet au système', '100', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('2', 'ROLE_DIRECTEUR', 'Directeur', 'Gestion générale de l\'hôpital', '90', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('3', 'ROLE_MEDECIN', 'Médecin', 'Gestion des patients et consultations', '70', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('4', 'ROLE_INFIRMIER', 'Infirmier', 'Soins et suivi des patients', '60', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('5', 'ROLE_PHARMACIEN', 'Pharmacien', 'Gestion de la pharmacie', '65', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('6', 'ROLE_LABORANTIN', 'Laborantin', 'Gestion du laboratoire', '55', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('7', 'ROLE_RADIOLOGUE', 'Radiologue', 'Gestion de l\'imagerie', '65', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('8', 'ROLE_COMPTABLE', 'Comptable', 'Gestion financière', '60', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('9', 'ROLE_RH', 'Responsable RH', 'Gestion des ressources humaines', '65', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('10', 'ROLE_MAINTENANCE', 'Technicien Maintenance', 'Maintenance des équipements', '50', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('11', 'ROLE_RECEPTIONNISTE', 'Réceptionniste', 'Accueil et rendez-vous', '40', '1', '2026-01-03 08:53:09');
INSERT INTO `roles` (`id`, `code`, `nom`, `description`, `niveau_acces`, `actif`, `date_creation`) VALUES ('12', 'ROLE_PATIENT', 'Patient', 'Accès au portail patient', '10', '1', '2026-01-03 08:53:09');

DROP TABLE IF EXISTS `salles_operations`;
CREATE TABLE `salles_operations` (
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
  KEY `service_id` (`service_id`),
  CONSTRAINT `salles_operations_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salles_operations_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `sauvegardes`;
CREATE TABLE `sauvegardes` (
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
  `backup_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_backup` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMPLETE',
  `date_debut` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_fin` datetime DEFAULT NULL,
  `localisation_secondaire` longtext COLLATE utf8mb4_unicode_ci,
  `checksum_sha256` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cle_chiffrement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `compression` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_fichiers` int DEFAULT NULL,
  `nombre_tables` int DEFAULT NULL,
  `date_expiration` datetime DEFAULT NULL,
  `message_erreur` longtext COLLATE utf8mb4_unicode_ci,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hopital_sauvegarde` (`hopital_id`,`numero_sauvegarde`),
  UNIQUE KEY `backup_id` (`backup_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `idx_date_debut` (`date_debut`),
  KEY `idx_type_backup` (`type_backup`),
  KEY `idx_statut` (`statut`),
  CONSTRAINT `sauvegardes_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sauvegardes_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('1', '1', 'BKP-554c639304124b57-1768628662', '2026-01-17 07:44:22', NULL, NULL, '/backups/2026-01-17/', 'PENDING', NULL, '1', '2026-01-17 07:44:22', 'BKP-554c639304124b57-1768628662', 'COMPLETE', '2026-01-17 07:44:22', NULL, 's3://backups/2026-01-17/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('2', '1', 'BKP-8fdcc2d032cd6c16-1768628740', '2026-01-17 07:45:40', NULL, NULL, '/backups/2026-01-17/', 'PENDING', NULL, '1', '2026-01-17 07:45:40', 'BKP-8fdcc2d032cd6c16-1768628740', 'INCREMENTAL', '2026-01-17 07:45:40', NULL, 's3://backups/2026-01-17/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('3', '1', 'BKP-bf590aff23201b05-1768629151', '2026-01-17 07:52:31', NULL, '1073741824', '/backups/2026-01-17/', 'SUCCESS', '3600', '1', '2026-01-17 07:52:31', 'BKP-bf590aff23201b05-1768629151', 'COMPLETE', '2026-01-17 07:52:31', '2026-01-17 06:19:19', 's3://backups/2026-01-17/', 'abc123def456x4lt5g', NULL, NULL, '5000', '45', NULL, NULL, NULL);
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('4', '1', 'BKP-86d8f5d5e5803da8-1768630365', '2026-01-17 08:12:45', NULL, '1073741824', '/backups/2026-01-17/', 'SUCCESS', '3600', '1', '2026-01-17 08:12:45', 'BKP-86d8f5d5e5803da8-1768630365', 'COMPLETE', '2026-01-17 08:12:45', '2026-01-17 06:17:52', 's3://backups/2026-01-17/', 'abc123def456q0bg8o', NULL, NULL, '5000', '45', NULL, NULL, NULL);
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('5', '1', 'BKP-b02fa8138e1fbf7f-1768631120', '2026-01-17 08:25:20', NULL, '7208960', '/backups/2026-01-17/', 'RESTORING', '2', '1', '2026-01-17 08:25:20', 'BKP-b02fa8138e1fbf7f-1768631120', 'COMPLETE', '2026-01-17 08:25:20', NULL, 's3://backups/2026-01-17/', '6175a2950c6d6a7844b4c324071c36afc5883d26ba4100a16f4d644410a0eee5', NULL, 'GZIP', '486', '98', NULL, NULL, NULL);
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('6', '1', 'BKP-2295c78fa0a25c34-1768724517', '2026-01-18 10:21:57', NULL, '7323648', '/backups/2026-01-18/', 'SUCCESS', '0', '1', '2026-01-18 10:21:57', 'BKP-2295c78fa0a25c34-1768724517', 'COMPLETE', '2026-01-18 10:21:57', NULL, 's3://backups/2026-01-18/', '0b48e4ae9f9a67c7a15498fe97c352fdc21a83752f6b8d5fdc0ab90f077a25ab', NULL, 'GZIP', '490', '99', NULL, NULL, NULL);
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('7', '1', 'BKP-e456ff700cb412ec-1768724657', '2026-01-18 10:24:17', NULL, '7323648', '/backups/2026-01-18/', 'SUCCESS', '0', '1', '2026-01-18 10:24:17', 'BKP-e456ff700cb412ec-1768724657', 'COMPLETE', '2026-01-18 10:24:17', NULL, 's3://backups/2026-01-18/', '7ec3ff3a4271eda3e152c04e33fe546ca6366770be0749fc81190f8cc4a91411', NULL, 'GZIP', '491', '99', NULL, NULL, NULL);
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('8', '1', 'BKP-e68d56ca8f63e92e-1768724853', '2026-01-18 10:27:33', NULL, '7323648', '/backups/2026-01-18/', 'RESTORED', '9', '1', '2026-01-18 10:27:33', 'BKP-e68d56ca8f63e92e-1768724853', 'COMPLETE', '2026-01-18 10:27:33', '2026-01-18 10:44:39', 's3://backups/2026-01-18/', '1525cbe557ad66ecc839e5e38335e071e7277a4d5ff9d52ea700e26fc5fc74d3', NULL, 'GZIP', '492', '99', NULL, NULL, '{\"restoreCount\":1,\"lastRestoreAt\":\"2026-01-18T10:44:39+02:00\",\"lastRestoreBy\":1}');
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('9', '1', 'BKP-d8589b9f6e28edb9-1768725982', '2026-01-18 10:46:22', NULL, '7323648', '/backups/2026-01-18/', 'RESTORED', '24', '1', '2026-01-18 10:46:22', 'BKP-d8589b9f6e28edb9-1768725982', 'COMPLETE', '2026-01-18 10:46:22', '2026-01-18 10:47:40', 's3://backups/2026-01-18/', 'e83c069824f84d85fb84b82f012864fb9e7be5855b5ee9d01b6244b59511f7ed', NULL, 'GZIP', '493', '99', NULL, NULL, '{\"restoreCount\":1,\"lastRestoreAt\":\"2026-01-18T10:47:40+02:00\",\"lastRestoreBy\":1}');
INSERT INTO `sauvegardes` (`id`, `hopital_id`, `numero_sauvegarde`, `date_sauvegarde`, `type_sauvegarde`, `taille_sauvegarde`, `localisation_sauvegarde`, `statut`, `duree_sauvegarde`, `utilisateur_id`, `date_creation`, `backup_id`, `type_backup`, `date_debut`, `date_fin`, `localisation_secondaire`, `checksum_sha256`, `cle_chiffrement`, `compression`, `nombre_fichiers`, `nombre_tables`, `date_expiration`, `message_erreur`, `notes`) VALUES ('10', '1', 'BKP-32405aa921c70c9f-1768729026', '2026-01-18 11:37:06', NULL, '7323648', 'D:\\Amos\\projet\\rehoboth\\src\\backups\\2026-01-18\\\\BKP-32405aa921c70c9f-1768729026.sql', 'SUCCESS', '25', '1', '2026-01-18 11:37:06', 'BKP-32405aa921c70c9f-1768729026', 'COMPLETE', '2026-01-18 11:37:06', NULL, 's3://backups/2026-01-18/', 'dfff7e55e26086dfc636c70124efa5c93a37e12d5e0f7d458cf73df767b9f880', NULL, 'GZIP', '494', '99', NULL, NULL, NULL);

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hopital_id` int NOT NULL,
  `pole_id` int DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type_service` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Urgences, Chirurgie, Médecine, Pédiatrie, etc.',
  `type_service_id` int DEFAULT NULL,
  `chef_service_id` int DEFAULT NULL,
  `nombre_lits` int DEFAULT NULL,
  `localisation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_service` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `couleur_service` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `budget_annuel` decimal(12,2) DEFAULT NULL,
  `nombre_personnel` int DEFAULT NULL,
  `horaires_ouverture` longtext COLLATE utf8mb4_unicode_ci,
  `niveau_accreditation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code_hopital` (`hopital_id`,`code`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_type` (`type_service`),
  KEY `idx_pole` (`pole_id`),
  KEY `idx_type_service_id` (`type_service_id`),
  CONSTRAINT `FK_SERVICES_POLE` FOREIGN KEY (`pole_id`) REFERENCES `poles_activite` (`id`),
  CONSTRAINT `FK_SERVICES_TYPE_SERVICE` FOREIGN KEY (`type_service_id`) REFERENCES `types_services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`id`, `hopital_id`, `pole_id`, `code`, `nom`, `description`, `type_service`, `type_service_id`, `chef_service_id`, `nombre_lits`, `localisation`, `telephone`, `email`, `logo_service`, `couleur_service`, `actif`, `date_creation`, `budget_annuel`, `nombre_personnel`, `horaires_ouverture`, `niveau_accreditation`) VALUES ('1', '1', NULL, 'CARD', 'Cardiologie', '', 'Spécialité médicale', NULL, NULL, '11', '', '', '', NULL, NULL, '1', '2026-01-10 10:50:12', NULL, NULL, NULL, NULL);
INSERT INTO `services` (`id`, `hopital_id`, `pole_id`, `code`, `nom`, `description`, `type_service`, `type_service_id`, `chef_service_id`, `nombre_lits`, `localisation`, `telephone`, `email`, `logo_service`, `couleur_service`, `actif`, `date_creation`, `budget_annuel`, `nombre_personnel`, `horaires_ouverture`, `niveau_accreditation`) VALUES ('2', '1', '1', 'CAR', 'Cardiologie', NULL, 'CA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '2026-01-11 01:21:10', NULL, NULL, NULL, NULL);

DROP TABLE IF EXISTS `sessions_utilisateurs`;
CREATE TABLE `sessions_utilisateurs` (
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
  KEY `hopital_id` (`hopital_id`),
  CONSTRAINT `sessions_utilisateurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sessions_utilisateurs_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `specialites`;
CREATE TABLE `specialites` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `specialites` (`id`, `code`, `nom`, `description`, `code_snomed`, `icone`, `couleur`, `actif`, `date_creation`) VALUES ('1', 'CARD', 'Cardiologue', 'Spécialité en cardiologie', NULL, NULL, NULL, '1', '2026-01-11 10:22:49');

DROP TABLE IF EXISTS `stocks_pharmacie`;
CREATE TABLE `stocks_pharmacie` (
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
  KEY `fournisseur_id` (`fournisseur_id`),
  CONSTRAINT `stocks_pharmacie_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stocks_pharmacie_ibfk_2` FOREIGN KEY (`medicament_id`) REFERENCES `medicaments` (`id`),
  CONSTRAINT `stocks_pharmacie_ibfk_3` FOREIGN KEY (`fournisseur_id`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `suivis_post_hospitalisation`;
CREATE TABLE `suivis_post_hospitalisation` (
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
  KEY `utilisateur_suivi_id` (`utilisateur_suivi_id`),
  CONSTRAINT `suivis_post_hospitalisation_ibfk_1` FOREIGN KEY (`feuille_sortie_id`) REFERENCES `feuilles_sortie` (`id`) ON DELETE CASCADE,
  CONSTRAINT `suivis_post_hospitalisation_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `suivis_post_hospitalisation_ibfk_3` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `suivis_post_hospitalisation_ibfk_4` FOREIGN KEY (`utilisateur_suivi_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `tarifs_lits`;
CREATE TABLE `tarifs_lits` (
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
  KEY `taux_tva_id` (`taux_tva_id`),
  CONSTRAINT `tarifs_lits_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tarifs_lits_ibfk_2` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tarifs_lits_ibfk_3` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `taux_tva`;
CREATE TABLE `taux_tva` (
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


DROP TABLE IF EXISTS `transferts_patients`;
CREATE TABLE `transferts_patients` (
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
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `transferts_patients_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transferts_patients_ibfk_2` FOREIGN KEY (`service_origine_id`) REFERENCES `services` (`id`),
  CONSTRAINT `transferts_patients_ibfk_3` FOREIGN KEY (`service_destination_id`) REFERENCES `services` (`id`),
  CONSTRAINT `transferts_patients_ibfk_4` FOREIGN KEY (`lit_origine_id`) REFERENCES `lits` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transferts_patients_ibfk_5` FOREIGN KEY (`lit_destination_id`) REFERENCES `lits` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transferts_patients_ibfk_6` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `triages`;
CREATE TABLE `triages` (
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
  KEY `niveau_triage_id` (`niveau_triage_id`),
  CONSTRAINT `triages_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `triages_ibfk_2` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`),
  CONSTRAINT `triages_ibfk_3` FOREIGN KEY (`service_urgences_id`) REFERENCES `services` (`id`),
  CONSTRAINT `triages_ibfk_4` FOREIGN KEY (`infirmier_triage_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `triages_ibfk_5` FOREIGN KEY (`niveau_triage_id`) REFERENCES `niveaux_triage` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `types_examens_labo`;
CREATE TABLE `types_examens_labo` (
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
  KEY `taux_tva_id` (`taux_tva_id`),
  CONSTRAINT `types_examens_labo_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `types_examens_labo_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `types_imagerie`;
CREATE TABLE `types_imagerie` (
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
  KEY `taux_tva_id` (`taux_tva_id`),
  CONSTRAINT `types_imagerie_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `types_imagerie_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `types_interventions`;
CREATE TABLE `types_interventions` (
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
  KEY `taux_tva_id` (`taux_tva_id`),
  CONSTRAINT `types_interventions_ibfk_1` FOREIGN KEY (`devise_id`) REFERENCES `devises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `types_interventions_ibfk_2` FOREIGN KEY (`taux_tva_id`) REFERENCES `taux_tva` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `types_poles`;
CREATE TABLE `types_poles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `types_poles` (`id`, `code`, `nom`, `description`, `actif`, `date_creation`) VALUES ('1', 'POL_URG', 'Urgence', NULL, '1', '2026-01-11 01:32:13');

DROP TABLE IF EXISTS `types_services`;
CREATE TABLE `types_services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `categorie` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `types_services` (`id`, `code`, `nom`, `description`, `categorie`, `actif`, `date_creation`) VALUES ('1', 'CA', 'Cardiologie', NULL, NULL, '1', '2026-01-11 01:20:38');

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE `utilisateurs` (
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
  `secret_2fa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pin_2fa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse_physique` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_livraison` date DEFAULT NULL,
  `validite` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`),
  KEY `idx_hopital` (`hopital_id`),
  KEY `idx_email` (`email`),
  KEY `idx_login` (`login`),
  KEY `idx_role` (`role_id`),
  KEY `idx_profil` (`profil_id`),
  KEY `idx_actif` (`actif`),
  KEY `specialite_id` (`specialite_id`),
  CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`hopital_id`) REFERENCES `hopitaux` (`id`) ON DELETE CASCADE,
  CONSTRAINT `utilisateurs_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `utilisateurs_ibfk_3` FOREIGN KEY (`profil_id`) REFERENCES `profils_utilisateurs` (`id`),
  CONSTRAINT `utilisateurs_ibfk_4` FOREIGN KEY (`specialite_id`) REFERENCES `specialites` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('1', '1', '1', 'Dupont', 'Jean', 'admin@rehoboth.com', '', 'admin', '$2y$10$LP4/kQFz3h1B3VrQ6k8EDe5fsEQcLGk36rs1K4TqkMHtzdQLBWMju', '1', NULL, NULL, NULL, '2026-01-03', NULL, NULL, '', '', '', '', '2026-01-11', '', '', NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', '2026-01-18 11:36:49', '2026-01-03 09:02:41', '2026-01-18 11:36:49', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('2', '1', '2', 'Martin', 'Pierre', 'directeur@rehoboth.com', NULL, 'directeur', '$2y$10$LP4/kQFz3h1B3VrQ6k8EDe5fsEQcLGk36rs1K4TqkMHtzdQLBWMju', '2', '1', NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', NULL, '2026-01-03 09:02:41', '2026-01-11 10:23:16', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('3', '1', '3', 'Bernard', 'Marie', 'amosamosamos2003@gmail.com', '099237283', 'medecin', '$2y$13$NcBwqnMy0xFBGOfS/YdWKOfdI5EVt20rlm2xR3ZijN2zMJBEZqcCa', '3', '1', '348EH84', '49824HHEEH', '2026-01-03', '/uploads/profils/photo_6963bcbbce21d.png', NULL, 'Medecin spécialiste cardiologue', 'Panzi', 'Bukavu', 'RI92894', '2001-06-11', 'M', 'Congolaise', 'OZF294ZOF', 'PASSPORT', '093480249', 'Amos', '1', '0', '1', '2026-01-11 02:18:54', '1', '1', '2026-01-11 02:16:39', '2026-01-03 09:02:41', '2026-01-12 06:58:54', 'UFZSH6QXISQ74GR4S45565ED47TR6M67', '$2y$12$Wq5CSvF9jfDEM/2NsTVvC.Ssk0tk8Y8ViiSXePwyDMEr2GTaggLwu', NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('4', '1', '4', 'Durand', 'Sophie', 'infirmier@rehoboth.com', NULL, 'infirmier', '$2y$10$LP4/kQFz3h1B3VrQ6k8EDe5fsEQcLGk36rs1K4TqkMHtzdQLBWMju', '4', NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '1', NULL, '2026-01-03 09:02:41', '2026-01-08 12:50:03', 'AKVNKCSL7U7WXZSHMXWAWECJKMVMJT5T', '$2y$12$9WXHSWFFuRCvkkNBWDYvYeiyZKM/jtTKaUkYJ/mhbKblg/UoI7dxW', NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('5', '1', '5', 'Lefevre', 'Thomas', 'pharmacien@rehoboth.com', NULL, 'pharmacien', '$2y$10$LP4/kQFz3h1B3VrQ6k8EDe5fsEQcLGk36rs1K4TqkMHtzdQLBWMju', '5', NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', NULL, '2026-01-03 09:02:41', '2026-01-07 16:15:15', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('6', '1', '6', 'Moreau', 'Luc', 'laborantin@rehoboth.com', NULL, 'laborantin', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', '6', NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('7', '1', '7', 'Girard', 'Anne', 'radiologue@rehoboth.com', NULL, 'radiologue', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', '7', NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('8', '1', '8', 'Petit', 'Jacques', 'comptable@rehoboth.com', NULL, 'comptable', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', '8', NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('9', '1', '9', 'Rousseau', 'Isabelle', 'rh@rehoboth.com', NULL, 'rh', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', '9', NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('10', '1', '10', 'Vincent', 'Marc', 'maintenance@rehoboth.com', NULL, 'maintenance', '$2y$13$8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8qJ8.8', '10', NULL, NULL, NULL, '2026-01-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '0', '0', NULL, '0', '0', NULL, '2026-01-03 09:02:41', '2026-01-03 09:02:41', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('11', '1', '11', 'Fourniers', 'Nathalie', 'receptionniste@rehoboth.com', '0990090', 'receptionniste', '$2y$13$ObjLcIYm0nQTZyWndnjNhe1bkvq9XlzqIviYLuF.B5bP4ZyZKQo3y', '11', NULL, 'EOR2249', 'IOEF220', '2026-01-03', '/uploads/profils/photo_6967b5f5c9e4c.png', NULL, 'Réceptionniste de l\'entré principale ', 'Panzi', 'Bukavu', '0988ZASSA', '2005-11-12', 'F', 'Congolaise', 'IJE0929328', 'PASSPORT', '0998798', 'amos', '1', '1', '0', '2026-01-08 16:05:39', '1', '0', '2026-01-08 16:06:30', '2026-01-03 09:02:45', '2026-01-18 10:47:23', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('12', '1', '1', 'amos', 'amoss', 'amos@gmail.com', '', 'amos', '$2y$13$92zHP8l5hQc76Wrmaa0q1uBUIhl4iyKQcCDTMBSljandCSMqOzPxC', '1', NULL, '', '', '2026-01-11', '/uploads/profils/photo_6963a54c9d144.png', NULL, '', '', '', '', '2026-01-11', '', '', '', '', '', '', '1', '0', '0', NULL, NULL, NULL, '2026-01-18 11:08:02', '2026-01-11 10:10:46', '2026-01-18 11:08:02', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('13', '1', '2', 'bro', 'man', 'man@gmail.com', '', 'man', '$2y$13$efxb0uNDvgr9eUwIUsivb.8sWmwWMYvlowwxpzhyQvzF3VbyyeXGm', '2', NULL, '', '', '2026-01-11', '/uploads/profils/photo_6963a0ad49495.png', NULL, '', '', '', '', '2026-01-11', '', '', '', '', '', '', '1', '1', NULL, NULL, NULL, '0', '2026-01-12 10:37:48', '2026-01-11 14:47:09', '2026-01-18 10:46:55', NULL, NULL, NULL, NULL, NULL);
INSERT INTO `utilisateurs` (`id`, `hopital_id`, `profil_id`, `nom`, `prenom`, `email`, `telephone`, `login`, `mot_de_passe`, `role_id`, `specialite_id`, `numero_licence`, `numero_ordre`, `date_embauche`, `photo_profil`, `signature_numerique`, `bio`, `adresse`, `ville`, `code_postal`, `date_naissance`, `sexe`, `nationalite`, `numero_identite`, `type_identite`, `telephone_urgence`, `contact_urgence_nom`, `actif`, `compte_verrouille`, `nombre_tentatives_connexion`, `date_dernier_changement_mdp`, `mdp_temporaire`, `authentification_2fa`, `derniere_connexion`, `date_creation`, `date_modification`, `secret_2fa`, `pin_2fa`, `adresse_physique`, `date_livraison`, `validite`) VALUES ('14', '1', '5', 'jean', 'iragi', 'j@gmail.com', '', 'jean', '$2y$13$2y0FHmfOQx1/G67AvQQ3T.8IziuohFtq80wP0y4UIODSThffYwQK6', '5', NULL, '', '', '2026-01-11', '/uploads/profils/photo_6963a0ec4a994.png', NULL, '', 'Panzi', 'Bukavu', '', '2026-01-11', '', '', '', '', '', '', '1', '1', NULL, NULL, NULL, NULL, NULL, '2026-01-11 15:08:59', '2026-01-18 10:41:40', NULL, NULL, NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
