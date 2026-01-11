# Documentation Complète - Gestion Avancée des Services Hospitaliers

## Vue d'ensemble

Cette documentation couvre la gestion complète et avancée des services hospitaliers, incluant :
- **Gestion organisationnelle** : Pôles d'activité, regroupement de services
- **Gestion opérationnelle** : Horaires, accréditation, localisation
- **Gestion des ressources** : Budget, personnel, équipements
- **Gestion administrative** : Responsables, coordination, rapports

---

## 1. PÔLES D'ACTIVITÉ

### Base URL: `/api/poles`

Les pôles regroupent plusieurs services par type de pathologies ou fonctions pour optimiser la coordination et les ressources.

#### 1.1 Lister tous les pôles
**GET** `/api/poles`

**Paramètres de requête:**
- `page` (int, optionnel): Numéro de page (défaut: 1)
- `limit` (int, optionnel): Nombre d'éléments par page (défaut: 20)
- `hopital_id` (int, optionnel): Filtrer par hôpital
- `actif` (boolean, optionnel): Filtrer par statut actif

**Exemple de requête:**
```bash
curl -X GET "http://localhost:8000/api/poles?hopital_id=1&actif=true"
```

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "POLE_CARDIO",
      "nom": "Pôle Cardiologie",
      "description": "Regroupement des services cardiologiques",
      "type_pole": "Spécialité",
      "budget_annuel": "500000.00",
      "actif": true,
      "date_creation": "2024-01-15 10:30:00",
      "hopital_id": 1,
      "responsable_id": 5,
      "nombre_services": 2
    }
  ],
  "pagination": { ... }
}
```

#### 1.2 Récupérer un pôle par ID
**GET** `/api/poles/{id}`

#### 1.3 Créer un nouveau pôle
**POST** `/api/poles`

**Champs requis:**
- `code` (string): Code unique du pôle
- `nom` (string): Nom du pôle
- `hopital_id` (int): ID de l'hôpital

**Champs optionnels:**
- `description` (string)
- `type_pole` (string): Type de pôle (Spécialité, Diagnostic, Support, etc.)
- `budget_annuel` (decimal): Budget annuel du pôle
- `responsable_id` (int): ID du responsable du pôle
- `actif` (boolean): Statut actif (défaut: true)

**Exemple de requête:**
```bash
curl -X POST "http://localhost:8000/api/poles" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "POLE_CARDIO",
    "nom": "Pôle Cardiologie",
    "hopital_id": 1,
    "type_pole": "Spécialité",
    "budget_annuel": "500000.00",
    "responsable_id": 5,
    "actif": true
  }'
```

#### 1.4 Modifier un pôle
**PUT** `/api/poles/{id}`

#### 1.5 Supprimer un pôle
**DELETE** `/api/poles/{id}`

#### 1.6 Lister les pôles d'un hôpital
**GET** `/api/poles/hopital/{hopitalId}`

#### 1.7 Assigner un responsable à un pôle
**POST** `/api/poles/{id}/assigner-responsable`

**Body JSON:**
```json
{
  "responsable_id": 5
}
```

#### 1.8 Lister les services d'un pôle
**GET** `/api/poles/{id}/services`

**Réponse (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "CARD",
      "nom": "Cardiologie",
      "type_service": "Spécialité médicale",
      "nombre_lits": 20,
      "nombre_personnel": 15,
      "budget_annuel": "250000.00"
    }
  ],
  "pagination": { ... }
}
```

#### 1.9 Obtenir les statistiques d'un pôle
**GET** `/api/poles/{id}/statistiques`

**Réponse (200):**
```json
{
  "success": true,
  "data": {
    "pole_id": 1,
    "pole_nom": "Pôle Cardiologie",
    "total_services": 2,
    "total_lits": 40,
    "total_personnel": 30,
    "total_budget": 500000.00,
    "budget_moyen_par_service": 250000.00,
    "personnel_moyen_par_service": 15
  }
}
```

---

## 2. SERVICES HOSPITALIERS (AMÉLIORÉ)

### Base URL: `/api/services`

#### 2.1 Lister tous les services
**GET** `/api/services`

**Paramètres de requête:**
- `page` (int, optionnel)
- `limit` (int, optionnel)
- `hopital_id` (int, optionnel)
- `type_service` (string, optionnel)
- `pole_id` (int, optionnel): Filtrer par pôle
- `actif` (boolean, optionnel)

#### 2.2 Récupérer un service par ID
**GET** `/api/services/{id}`

#### 2.3 Récupérer les détails complets d'un service
**GET** `/api/services/{id}/details`

**Réponse (200):**
```json
{
  "success": true,
  "data": {
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
    "hopital_id": 1,
    "pole_id": 1,
    "budget_annuel": "250000.00",
    "nombre_personnel": 15,
    "horaires_ouverture": "Lun-Ven: 08:00-18:00, Sam: 09:00-13:00",
    "niveau_accreditation": "ISO 9001",
    "pole_nom": "Pôle Cardiologie",
    "pole_code": "POLE_CARDIO"
  }
}
```

#### 2.4 Créer un nouveau service
**POST** `/api/services`

**Champs requis:**
- `code` (string): Code unique
- `nom` (string): Nom du service
- `hopital_id` (int): ID de l'hôpital

**Champs optionnels (Gestion):**
- `budget_annuel` (decimal): Budget annuel du service
- `nombre_personnel` (int): Nombre de personnels
- `horaires_ouverture` (string): Horaires d'ouverture
- `niveau_accreditation` (string): Niveau d'accréditation (ISO, JCI, etc.)
- `pole_id` (int): ID du pôle d'activité

**Exemple de requête complète:**
```bash
curl -X POST "http://localhost:8000/api/services" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "CARD",
    "nom": "Cardiologie",
    "hopital_id": 1,
    "type_service": "Spécialité médicale",
    "description": "Service de cardiologie",
    "chef_service_id": 5,
    "nombre_lits": 20,
    "localisation": "Étage 3",
    "telephone": "+33123456789",
    "email": "cardio@hopital.fr",
    "couleur_service": "#FF0000",
    "budget_annuel": "250000.00",
    "nombre_personnel": 15,
    "horaires_ouverture": "Lun-Ven: 08:00-18:00, Sam: 09:00-13:00",
    "niveau_accreditation": "ISO 9001",
    "pole_id": 1,
    "actif": true
  }'
```

#### 2.5 Modifier un service
**PUT** `/api/services/{id}`

#### 2.6 Supprimer un service
**DELETE** `/api/services/{id}`

#### 2.7 Lister les services d'un hôpital
**GET** `/api/services/hopital/{hopitalId}`

#### 2.8 Lister les services par type
**GET** `/api/services/type/{typeService}`

#### 2.9 Lister les services d'un pôle
**GET** `/api/services/pole/{poleId}`

#### 2.10 Assigner un service à un pôle
**POST** `/api/services/{id}/assigner-pole`

**Body JSON:**
```json
{
  "pole_id": 1
}
```

---

## 3. FLUX DE GESTION COMPLET

### Étape 1: Créer les pôles d'activité
```bash
POST /api/poles
{
  "code": "POLE_CARDIO",
  "nom": "Pôle Cardiologie",
  "hopital_id": 1,
  "type_pole": "Spécialité",
  "budget_annuel": "500000.00",
  "responsable_id": 5
}
```

### Étape 2: Créer les services et les assigner aux pôles
```bash
POST /api/services
{
  "code": "CARD",
  "nom": "Cardiologie",
  "hopital_id": 1,
  "pole_id": 1,
  "budget_annuel": "250000.00",
  "nombre_personnel": 15,
  "nombre_lits": 20
}
```

### Étape 3: Créer les chambres dans les services
```bash
POST /api/chambres
{
  "numero_chambre": "101",
  "service_id": 1,
  "hopital_id": 1,
  "nombre_lits": 2,
  "type_chambre": "Double"
}
```

### Étape 4: Créer les lits dans les chambres
```bash
POST /api/lits
{
  "numero_lit": "A101",
  "chambre_id": 1,
  "type_lit": "Standard",
  "statut": "disponible"
}
```

### Étape 5: Créer les équipements pour les services
```bash
POST /api/equipements
{
  "code_equipement": "ECG001",
  "nom_equipement": "Électrocardiographe",
  "service_id": 1,
  "hopital_id": 1,
  "fournisseur_id": 5,
  "devise_id": 1,
  "budget_annuel": "15000.00"
}
```

---

## 4. GESTION OPÉRATIONNELLE

### Champs de gestion des services

| Champ | Type | Description |
|-------|------|-------------|
| `budget_annuel` | decimal | Budget annuel alloué au service |
| `nombre_personnel` | int | Nombre de personnels affectés |
| `horaires_ouverture` | string | Horaires d'ouverture du service |
| `niveau_accreditation` | string | Niveau d'accréditation (ISO, JCI, etc.) |
| `pole_id` | int | Pôle d'activité auquel appartient le service |

### Champs de gestion des pôles

| Champ | Type | Description |
|-------|------|-------------|
| `budget_annuel` | decimal | Budget annuel du pôle |
| `responsable_id` | int | Responsable du pôle |
| `type_pole` | string | Type de pôle (Spécialité, Diagnostic, Support) |

---

## 5. RAPPORTS ET STATISTIQUES

### Statistiques d'un pôle
```bash
GET /api/poles/{id}/statistiques
```

Retourne:
- Nombre total de services
- Nombre total de lits
- Nombre total de personnels
- Budget total
- Moyennes par service

### Statistiques d'un service
```bash
GET /api/services/{id}/details
```

Retourne tous les détails du service incluant:
- Budget et personnel
- Horaires et accréditation
- Pôle d'appartenance
- Nombre de lits et chambres

---

## 6. TYPES DE PÔLES RECOMMANDÉS

- **Pôle Urgences et Soins Critiques** : Urgences, Réanimation, ICU
- **Pôle Médecine Interne** : Médecine interne, Cardiologie, Pneumologie
- **Pôle Chirurgie** : Chirurgie générale, Orthopédie, ORL
- **Pôle Mère-Enfant** : Gynécologie, Obstétrique, Pédiatrie
- **Pôle Diagnostic** : Radiologie, Laboratoire, Anatomopathologie
- **Pôle Support** : Pharmacie, Maintenance, Logistique

---

## 7. NIVEAUX D'ACCRÉDITATION

- ISO 9001 : Système de management de la qualité
- ISO 14001 : Management environnemental
- JCI (Joint Commission International) : Accréditation internationale
- AACI : Accréditation canadienne
- NIAHO : Accréditation américaine

---

## Codes d'erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 201 | Créé avec succès |
| 400 | Requête invalide |
| 404 | Ressource non trouvée |
| 409 | Conflit (code déjà existant) |
| 500 | Erreur serveur |
