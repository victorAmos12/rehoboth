# Documentation des Endpoints API - Services, Chambres, Lits, Équipements et Maintenances

## Vue d'ensemble

Cette documentation couvre les endpoints API pour la gestion des services hospitaliers, des chambres, des lits, des équipements et des interventions de maintenance.

---

## 1. SERVICES HOSPITALIERS

### Base URL: `/api/services`

#### 1.1 Lister tous les services
**GET** `/api/services`

**Paramètres de requête:**
- `page` (int, optionnel): Numéro de page (défaut: 1)
- `limit` (int, optionnel): Nombre d'éléments par page (défaut: 20)
- `hopital_id` (int, optionnel): Filtrer par hôpital
- `type_service` (string, optionnel): Filtrer par type de service
- `actif` (boolean, optionnel): Filtrer par statut actif

**Exemple de requête:**
```bash
curl -X GET "http://localhost:8000/api/services?page=1&limit=20&hopital_id=1&actif=true"
```

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "CARD",
      "nom": "Cardiologie",
      "description": "Service de cardiologie",
      "type_service": "Spécialité médicale",
      "chef_service_id": 5,
      "nombre_lits": 20,
      "localisation": "Étage 3",
      "telephone": "+33123456789",
      "email": "cardio@hopital.fr",
      "logo_service": "https://...",
      "couleur_service": "#FF0000",
      "actif": true,
      "date_creation": "2024-01-15 10:30:00",
      "hopital_id": 1
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 45,
    "pages": 3
  }
}
```

#### 1.2 Récupérer un service par ID
**GET** `/api/services/{id}`

#### 1.3 Créer un nouveau service
**POST** `/api/services`

**Champs requis:**
- `code` (string): Code unique du service
- `nom` (string): Nom du service
- `hopital_id` (int): ID de l'hôpital

**Champs optionnels:**
- `description` (string)
- `type_service` (string)
- `chef_service_id` (int)
- `nombre_lits` (int)
- `localisation` (string)
- `telephone` (string)
- `email` (string)
- `logo_service` (string)
- `couleur_service` (string)
- `actif` (boolean)

#### 1.4 Modifier un service
**PUT** `/api/services/{id}`

#### 1.5 Supprimer un service
**DELETE** `/api/services/{id}`

#### 1.6 Lister les services d'un hôpital
**GET** `/api/services/hopital/{hopitalId}`

#### 1.7 Lister les services par type
**GET** `/api/services/type/{typeService}`

---

## 2. CHAMBRES HOSPITALIÈRES

### Base URL: `/api/chambres`

Les chambres sont les unités de base qui contiennent les lits. Un lit ne peut pas exister sans chambre.

#### 2.1 Lister toutes les chambres
**GET** `/api/chambres`

**Paramètres de requête:**
- `page` (int, optionnel)
- `limit` (int, optionnel)
- `service_id` (int, optionnel)
- `hopital_id` (int, optionnel)
- `type_chambre` (string, optionnel)
- `statut` (string, optionnel)

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "numero_chambre": "101",
      "etage": 1,
      "nombre_lits": 2,
      "type_chambre": "Double",
      "statut": "disponible",
      "description": "Chambre double standard",
      "localisation": "Aile A",
      "climatisee": true,
      "sanitaires_prives": true,
      "television": true,
      "telephone": true,
      "date_creation": "2024-01-01 08:00:00",
      "service_id": 1,
      "hopital_id": 1,
      "nombre_lits_occupes": 1
    }
  ],
  "pagination": { ... }
}
```

#### 2.2 Récupérer une chambre par ID
**GET** `/api/chambres/{id}`

#### 2.3 Créer une nouvelle chambre
**POST** `/api/chambres`

**Champs requis:**
- `numero_chambre` (string): Numéro unique de la chambre
- `service_id` (int): ID du service
- `hopital_id` (int): ID de l'hôpital

**Champs optionnels:**
- `etage` (int): Numéro d'étage
- `nombre_lits` (int): Nombre de lits dans la chambre
- `type_chambre` (string): Type de chambre (Simple, Double, Triple, etc.)
- `statut` (string): Statut (défaut: "disponible")
- `description` (string)
- `localisation` (string)
- `climatisee` (boolean)
- `sanitaires_prives` (boolean)
- `television` (boolean)
- `telephone` (boolean)

**Exemple de requête:**
```bash
curl -X POST "http://localhost:8000/api/chambres" \
  -H "Content-Type: application/json" \
  -d '{
    "numero_chambre": "101",
    "etage": 1,
    "nombre_lits": 2,
    "type_chambre": "Double",
    "service_id": 1,
    "hopital_id": 1,
    "statut": "disponible",
    "climatisee": true,
    "sanitaires_prives": true,
    "television": true,
    "telephone": true
  }'
```

#### 2.4 Modifier une chambre
**PUT** `/api/chambres/{id}`

#### 2.5 Supprimer une chambre
**DELETE** `/api/chambres/{id}`

#### 2.6 Lister les chambres d'un service
**GET** `/api/chambres/service/{serviceId}`

#### 2.7 Lister les chambres d'un hôpital
**GET** `/api/chambres/hopital/{hopitalId}`

#### 2.8 Lister les chambres par type
**GET** `/api/chambres/type/{typeChambre}`

#### 2.9 Lister les chambres par statut
**GET** `/api/chambres/statut/{statut}`

---

## 3. LITS HOSPITALIERS

### Base URL: `/api/lits`

**Important:** Un lit DOIT toujours être associé à une chambre. La chambre est obligatoire lors de la création d'un lit.

#### 3.1 Lister tous les lits
**GET** `/api/lits`

**Paramètres de requête:**
- `page` (int, optionnel)
- `limit` (int, optionnel)
- `service_id` (int, optionnel)
- `hopital_id` (int, optionnel)
- `statut` (string, optionnel)

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "numero_lit": "A101",
      "type_lit": "Standard",
      "etage": 1,
      "statut": "disponible",
      "date_derniere_maintenance": "2024-01-10",
      "date_creation": "2024-01-01 08:00:00",
      "service_id": 1,
      "hopital_id": 1,
      "chambre_id": 1,
      "chambre_numero": "101"
    }
  ],
  "pagination": { ... }
}
```

#### 3.2 Récupérer un lit par ID
**GET** `/api/lits/{id}`

#### 3.3 Créer un nouveau lit
**POST** `/api/lits`

**Champs requis:**
- `numero_lit` (string): Numéro unique du lit
- `chambre_id` (int): ID de la chambre (OBLIGATOIRE)

**Champs optionnels:**
- `type_lit` (string): Type de lit (Standard, VIP, Soins intensifs, etc.)
- `etage` (int): Numéro d'étage
- `statut` (string): Statut (défaut: "disponible")
- `date_derniere_maintenance` (date): Date de la dernière maintenance

**Exemple de requête:**
```bash
curl -X POST "http://localhost:8000/api/lits" \
  -H "Content-Type: application/json" \
  -d '{
    "numero_lit": "A101",
    "type_lit": "Standard",
    "etage": 1,
    "chambre_id": 1,
    "statut": "disponible"
  }'
```

**Réponse (201):**
```json
{
  "success": true,
  "message": "Lit créé avec succès",
  "data": { ... }
}
```

#### 3.4 Modifier un lit
**PUT** `/api/lits/{id}`

#### 3.5 Supprimer un lit
**DELETE** `/api/lits/{id}`

#### 3.6 Lister les lits d'un service
**GET** `/api/lits/service/{serviceId}`

#### 3.7 Lister les lits d'un hôpital
**GET** `/api/lits/hopital/{hopitalId}`

#### 3.8 Lister les lits par statut
**GET** `/api/lits/statut/{statut}`

---

## 4. ÉQUIPEMENTS HOSPITALIERS

### Base URL: `/api/equipements`

#### 4.1 Lister tous les équipements
**GET** `/api/equipements`

**Paramètres de requête:**
- `page` (int, optionnel)
- `limit` (int, optionnel)
- `service_id` (int, optionnel)
- `hopital_id` (int, optionnel)
- `type_equipement` (string, optionnel)
- `statut` (string, optionnel)

#### 4.2 Récupérer un équipement par ID
**GET** `/api/equipements/{id}`

#### 4.3 Créer un nouvel équipement
**POST** `/api/equipements`

**Champs requis:**
- `code_equipement` (string)
- `nom_equipement` (string)
- `service_id` (int)
- `hopital_id` (int)
- `fournisseur_id` (int)
- `devise_id` (int)

**Champs optionnels:**
- `type_equipement` (string)
- `marque` (string)
- `modele` (string)
- `numero_serie` (string)
- `date_acquisition` (date)
- `date_mise_en_service` (date)
- `prix_acquisition` (decimal)
- `duree_vie_utile` (int)
- `statut` (string)
- `localisation` (string)

#### 4.4 Modifier un équipement
**PUT** `/api/equipements/{id}`

#### 4.5 Supprimer un équipement
**DELETE** `/api/equipements/{id}`

#### 4.6 Lister les équipements d'un service
**GET** `/api/equipements/service/{serviceId}`

#### 4.7 Lister les équipements d'un hôpital
**GET** `/api/equipements/hopital/{hopitalId}`

#### 4.8 Lister les équipements par type
**GET** `/api/equipements/type/{typeEquipement}`

#### 4.9 Lister les équipements par statut
**GET** `/api/equipements/statut/{statut}`

---

## 5. INTERVENTIONS DE MAINTENANCE

### Base URL: `/api/maintenances`

#### 5.1 Lister toutes les interventions
**GET** `/api/maintenances`

**Paramètres de requête:**
- `page` (int, optionnel)
- `limit` (int, optionnel)
- `equipement_id` (int, optionnel)
- `hopital_id` (int, optionnel)
- `technicien_id` (int, optionnel)
- `statut` (string, optionnel)
- `type_intervention` (string, optionnel)

#### 5.2 Récupérer une intervention par ID
**GET** `/api/maintenances/{id}`

#### 5.3 Créer une nouvelle intervention
**POST** `/api/maintenances`

**Champs requis:**
- `numero_intervention` (string)
- `date_intervention` (date)
- `equipement_id` (int)
- `hopital_id` (int)
- `technicien_id` (int)
- `devise_id` (int)

**Champs optionnels:**
- `type_intervention` (string)
- `description_intervention` (text)
- `pieces_remplacees` (text)
- `duree_intervention` (int)
- `cout_intervention` (decimal)
- `statut` (string)

#### 5.4 Modifier une intervention
**PUT** `/api/maintenances/{id}`

#### 5.5 Supprimer une intervention
**DELETE** `/api/maintenances/{id}`

#### 5.6 Lister les interventions d'un équipement
**GET** `/api/maintenances/equipement/{equipementId}`

#### 5.7 Lister les interventions d'un hôpital
**GET** `/api/maintenances/hopital/{hopitalId}`

#### 5.8 Lister les interventions d'un technicien
**GET** `/api/maintenances/technicien/{technicienId}`

#### 5.9 Lister les interventions par statut
**GET** `/api/maintenances/statut/{statut}`

#### 5.10 Lister les interventions par type
**GET** `/api/maintenances/type/{typeIntervention}`

---

## Codes d'erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Requête invalide (champs manquants ou invalides) |
| 404 | Ressource non trouvée |
| 409 | Conflit (ex: code déjà existant) |
| 500 | Erreur serveur |

---

## Flux de création recommandé

Pour créer une structure complète, suivez cet ordre:

1. **Créer un service** (POST `/api/services`)
2. **Créer une chambre** (POST `/api/chambres`) - Associée au service
3. **Créer des lits** (POST `/api/lits`) - Associés à la chambre
4. **Créer des équipements** (POST `/api/equipements`) - Associés au service
5. **Créer des interventions de maintenance** (POST `/api/maintenances`) - Associées aux équipements

---

## Types de services recommandés

### Services cliniques
- Médecine interne
- Chirurgie générale
- Gynécologie-obstétrique
- Pédiatrie / Néonatologie
- Urgences
- Cardiologie
- Neurologie
- Pneumologie
- Oncologie
- Orthopédie
- Psychiatrie
- Endocrinologie
- Gériatrie

### Services para-cliniques
- Radiologie et imagerie médicale
- Laboratoire de biologie médicale
- Anatomopathologie
- Pharmacie hospitalière
- Rééducation / kinésithérapie

### Services de soins intensifs
- Unité de soins intensifs (ICU)
- Unités de soins post-opératoires
- Unité de réanimation

### Services administratifs
- Administration et accueil
- Dossier médical et archives
- Achat et stockage
- Maintenance et sécurité
- Comptabilité et finances

---

## Statuts des chambres

- `disponible`: Chambre disponible
- `occupée`: Chambre occupée
- `maintenance`: Chambre en maintenance
- `réservée`: Chambre réservée
- `hors_service`: Chambre hors service

---

## Statuts des lits

- `disponible`: Lit disponible pour admission
- `occupé`: Lit occupé par un patient
- `maintenance`: Lit en maintenance
- `réservé`: Lit réservé
- `hors_service`: Lit hors service

---

## Statuts des équipements

- `actif`: Équipement en fonctionnement
- `maintenance`: Équipement en maintenance
- `hors_service`: Équipement hors service
- `stocké`: Équipement en stock

---

## Statuts des interventions de maintenance

- `planifiée`: Intervention planifiée
- `en_cours`: Intervention en cours
- `terminée`: Intervention terminée
- `annulée`: Intervention annulée
- `reportée`: Intervention reportée

---

## Notes importantes

1. **Authentification**: Assurez-vous que vous êtes authentifié avant d'accéder aux endpoints.
2. **Pagination**: Par défaut, les listes sont paginées avec 20 éléments par page.
3. **Dates**: Les dates doivent être au format ISO 8601 (YYYY-MM-DD).
4. **Relation Chambre-Lit**: Un lit DOIT toujours être associé à une chambre. La chambre est obligatoire.
5. **Références**: Les IDs de service, hôpital, fournisseur, etc. doivent exister dans la base de données.
6. **Cascade**: Supprimer une chambre supprimera tous les lits associés.
