-- Patch SQL (SANS DROP/CREATE) : correction des parent_id des sous-menus
-- Objectif: faire pointer parent_id vers l'ID du menu parent (et non vers l'ordre)
-- Ce script est idempotent (ré-exécutable) et ne supprime aucune table.

START TRANSACTION;

-- Parents attendus:
-- 1  dashboard
-- 2  patients
-- 6  dossiers_medicaux
-- 9  admissions
-- 14 consultations
-- 17 rendez_vous
-- 22 prescriptions
-- 28 pharmacie
-- 35 laboratoire
-- 45 imagerie
-- 55 chirurgie
-- 65 urgences
-- 69 facturation
-- 77 rh
-- 90 administration
-- 112 rapports
-- 117 parametres

-- 1) Patients (parent = 2)
UPDATE menus SET parent_id = 2 WHERE id IN (3,4,5);

-- 2) Dossiers médicaux (parent = 6)
UPDATE menus SET parent_id = 6 WHERE id IN (7,8);

-- 3) Admissions (parent = 9)
UPDATE menus SET parent_id = 9 WHERE id IN (10,11,12,13);

-- 4) Consultations (parent = 14)
UPDATE menus SET parent_id = 14 WHERE id IN (15,16);

-- 5) Rendez-vous (parent = 17)
UPDATE menus SET parent_id = 17 WHERE id IN (18,19,20,21);

-- 6) Prescriptions (parent = 22)
UPDATE menus SET parent_id = 22 WHERE id IN (23,24,25,26,27);

-- 7) Pharmacie (parent = 28)
-- Niveaux: 29 & 32 sont des sous-menus directs de Pharmacie
UPDATE menus SET parent_id = 28 WHERE id IN (29,32);
-- Sous-niveaux: enfants de Stocks (29)
UPDATE menus SET parent_id = 29 WHERE id IN (30,31);
-- Sous-niveaux: enfants de Distributions (32)
UPDATE menus SET parent_id = 32 WHERE id IN (33,34);

-- 8) Laboratoire (parent = 35)
-- Niveaux: 36,39,42 sont des sous-menus directs
UPDATE menus SET parent_id = 35 WHERE id IN (36,39,42);
-- Enfants de Ordonnances (36)
UPDATE menus SET parent_id = 36 WHERE id IN (37,38);
-- Enfants de Prélèvements (39)
UPDATE menus SET parent_id = 39 WHERE id IN (40,41);
-- Enfants de Résultats (42)
UPDATE menus SET parent_id = 42 WHERE id IN (43,44);

-- 9) Imagerie (parent = 45)
-- Niveaux: 46,49,52 sont des sous-menus directs
UPDATE menus SET parent_id = 45 WHERE id IN (46,49,52);
-- Enfants de Ordonnances (46)
UPDATE menus SET parent_id = 46 WHERE id IN (47,48);
-- Enfants de Examens (49)
UPDATE menus SET parent_id = 49 WHERE id IN (50,51);
-- Enfants de Rapports (52)
UPDATE menus SET parent_id = 52 WHERE id IN (53,54);

-- 10) Chirurgie (parent = 55)
-- Niveaux: 56,59,62
UPDATE menus SET parent_id = 55 WHERE id IN (56,59,62);
-- Enfants de Demandes (56)
UPDATE menus SET parent_id = 56 WHERE id IN (57,58);
-- Enfants de Planning (59)
UPDATE menus SET parent_id = 59 WHERE id IN (60,61);
-- Enfants de Rapports (62)
UPDATE menus SET parent_id = 62 WHERE id IN (63,64);

-- 11) Urgences (parent = 65)
UPDATE menus SET parent_id = 65 WHERE id IN (66);
UPDATE menus SET parent_id = 66 WHERE id IN (67,68);

-- 12) Facturation (parent = 69)
UPDATE menus SET parent_id = 69 WHERE id IN (70,73,76);
UPDATE menus SET parent_id = 70 WHERE id IN (71,72);
UPDATE menus SET parent_id = 73 WHERE id IN (74,75);

-- 13) RH (parent = 77)
UPDATE menus SET parent_id = 77 WHERE id IN (78,81,84,87);
UPDATE menus SET parent_id = 78 WHERE id IN (79,80);
UPDATE menus SET parent_id = 81 WHERE id IN (82,83);
UPDATE menus SET parent_id = 84 WHERE id IN (85,86);
UPDATE menus SET parent_id = 87 WHERE id IN (88,89);

-- 14) Administration (parent = 90)
UPDATE menus SET parent_id = 90 WHERE id IN (91,94,97,100,103,106,109);
UPDATE menus SET parent_id = 91 WHERE id IN (92,93);
UPDATE menus SET parent_id = 94 WHERE id IN (95,96);
UPDATE menus SET parent_id = 97 WHERE id IN (98,99);
UPDATE menus SET parent_id = 100 WHERE id IN (101,102);
UPDATE menus SET parent_id = 103 WHERE id IN (104,105);
UPDATE menus SET parent_id = 106 WHERE id IN (107,108);
UPDATE menus SET parent_id = 109 WHERE id IN (110,111);

-- 15) Rapports (parent = 112)
UPDATE menus SET parent_id = 112 WHERE id IN (113,114,115,116);

-- 16) Paramètres (parent = 117)
UPDATE menus SET parent_id = 117 WHERE id IN (118,121,123,124);
UPDATE menus SET parent_id = 118 WHERE id IN (119,120);
UPDATE menus SET parent_id = 121 WHERE id IN (122);

COMMIT;

-- Vérification rapide (à exécuter après):
-- SELECT id, code, parent_id FROM menus WHERE parent_id IS NOT NULL ORDER BY parent_id, ordre, id;
