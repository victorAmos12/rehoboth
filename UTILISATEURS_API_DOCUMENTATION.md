# Documentation API - Gestion des Utilisateurs et Ressources Humaines

## Vue d'ensemble

Cette documentation couvre tous les endpoints API pour la gestion complète des utilisateurs, affectations, rôles, profils et spécialités dans le système Rehoboth.

---

## 1. UTILISATEURS - UtilisateursControllers

### Endpoints principaux

#### 1.1 Lister les utilisateurs
```
GET /api/utilisateurs
```

**Paramètres de requête:**
- `page` (int, défaut: 1) - Numéro de page
- `limit` (int, défaut: 20, max: 100) - Nombre d'éléments par page
- `search` (string) - Recherche par nom, prénom, email, login
- `hopital_id` (int) - Filtrer par hôpital
- `role_id` (int) - Filtrer par rôle
- `profil_id` (int) - Filtrer par profil
- `specialite_id` (int) - Filtrer par spécialité
- `actif` (boolean) - Filtrer par statut actif/inactif
- `sort` (string, défaut: dateCreation) - Champ de tri
- `order` (string, défaut: DESC) - Ordre de tri (ASC/DESC)

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nom": "Dupont",
      "prenom": "Jean",
      "email": "jean.dupont@hospital.com",
      "login": "jdupont",
      "telephone": "+33612345678",
      "actif": true,
      "hopital": {
        "id": 1,
        "nom": "Hôpital Central"
      },
      "role": {
        "id": 1,
        "code": "MEDECIN",
        "nom": "Médecin",
        "niveau_acces": 5
      },
      "profil": {
        "id": 1,
        "code": "CARDIO",
        "nom": "Cardiologue"
      },
      "specialite": {
        "id": 1,
        "nom": "Cardiologie"
      },
      "dateCreation": "2024-01-15T10:30:00+00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8
  }
}
```

#### 1.2 Récupérer un utilisateur
```
GET /api/utilisateurs/{id}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "identite": { ... },
    "informations_personnelles": { ... },
    "informations_professionnelles": { ... },
    "informations_administratives": { ... },
    "securite": { ... },
    "historique": { ... }
  }
}
```

#### 1.3 Créer un utilisateur
```
POST /api/utilisateurs
```

**Body (requis):**
```json
{
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean.dupont@hospital.com",
  "login": "jdupont",
  "motDePasse": "SecurePassword123!",
  "hopital_id": 1,
  "role_id": 1,
  "profil_id": 1
}
```

**Body (optionnel):**
```json
{
  "telephone": "+33612345678",
  "numeroLicence": "LIC123456",
  "numeroOrdre": "ORD789012",
  "dateEmbauche": "2024-01-01",
  "photoProfil": "https://...",
  "signatureNumerique": "https://...",
  "bio": "Médecin généraliste avec 10 ans d'expérience",
  "adresse": "123 Rue de la Paix",
  "ville": "Paris",
  "codePostal": "75001",
  "dateNaissance": "1980-05-15",
  "sexe": "M",
  "nationalite": "Française",
  "numeroIdentite": "123456789",
  "typeIdentite": "CNI",
  "telephoneUrgence": "+33612345679",
  "contactUrgenceNom": "Marie Dupont",
  "specialite_id": 1,
  "authentification_2fa": true
}
```

**Réponse:** `201 Created`

#### 1.4 Mettre à jour un utilisateur
```
PUT /api/utilisateurs/{id}
```

**Body:** Tous les champs optionnels (voir création)

**Réponse:** `200 OK`

#### 1.5 Supprimer un utilisateur (soft delete)
```
DELETE /api/utilisateurs/{id}
```

**Réponse:** `200 OK`

#### 1.6 Changer le mot de passe
```
POST /api/utilisateurs/{id}/change-password
```

**Body:**
```json
{
  "ancien_mot_de_passe": "OldPassword123!",
  "nouveau_mot_de_passe": "NewPassword456!"
}
```

#### 1.7 Réinitialiser le mot de passe (admin)
```
POST /api/utilisateurs/{id}/reset-password
```

**Body:**
```json
{
  "nouveau_mot_de_passe": "NewPassword456!"
}
```

#### 1.8 Verrouiller/Déverrouiller un compte
```
POST /api/utilisateurs/{id}/lock
```

**Body:**
```json
{
  "verrouille": true
}
```

#### 1.9 Activer/Désactiver la 2FA
```
POST /api/utilisateurs/{id}/2fa
```

**Body:**
```json
{
  "actif": true
}
```

#### 1.10 Mettre à jour la dernière connexion
```
POST /api/utilisateurs/{id}/last-login
```

#### 1.11 Recherche avancée
```
POST /api/utilisateurs/search
```

**Body:**
```json
{
  "page": 1,
  "limit": 20,
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean.dupont@hospital.com",
  "login": "jdupont",
  "hopital_id": 1,
  "role_id": 1,
  "profil_id": 1,
  "actif": true,
  "compte_verrouille": false
}
```

#### 1.12 Utilisateurs par hôpital
```
GET /api/utilisateurs/hopital/{hopitalId}?page=1&limit=20
```

#### 1.13 Utilisateurs par rôle
```
GET /api/utilisateurs/role/{roleId}?page=1&limit=20
```

#### 1.14 Statistiques utilisateurs
```
GET /api/utilisateurs/stats
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "total": 150,
    "actifs": 145,
    "inactifs": 5,
    "comptes_verrouilles": 2,
    "avec_2fa": 120,
    "par_role": {
      "Médecin": 50,
      "Infirmier": 60,
      "Admin": 10
    },
    "par_profil": { ... },
    "par_hopital": { ... }
  }
}
```

#### 1.15 Export CSV
```
GET /api/utilisateurs/export/csv?hopital_id=1&role_id=1&actif=true
```

**Réponse:** Fichier CSV

---

## 2. AFFECTATIONS - AffectationsUtilisateursController

### Endpoints principaux

#### 2.1 Lister les affectations
```
GET /api/affectations
```

**Paramètres de requête:**
- `page` (int, défaut: 1)
- `limit` (int, défaut: 20, max: 100)
- `utilisateur_id` (int) - Filtrer par utilisateur
- `service_id` (int) - Filtrer par service
- `actif` (boolean) - Filtrer par statut
- `sort` (string, défaut: dateDebut)
- `order` (string, défaut: DESC)

#### 2.2 Récupérer une affectation
```
GET /api/affectations/{id}
```

#### 2.3 Créer une affectation
```
POST /api/affectations
```

**Body (requis):**
```json
{
  "utilisateur_id": 1,
  "service_id": 1,
  "date_debut": "2024-01-01"
}
```

**Body (optionnel):**
```json
{
  "date_fin": "2024-12-31",
  "pourcentage_temps": 100,
  "actif": true
}
```

#### 2.4 Mettre à jour une affectation
```
PUT /api/affectations/{id}
```

#### 2.5 Supprimer une affectation
```
DELETE /api/affectations/{id}
```

#### 2.6 Affectations d'un utilisateur
```
GET /api/affectations/utilisateur/{utilisateurId}?page=1&limit=20&actif=true
```

#### 2.7 Affectations d'un service
```
GET /api/affectations/service/{serviceId}?page=1&limit=20
```

#### 2.8 Affectations actuelles
```
GET /api/affectations/actuelles?page=1&limit=20
```

Retourne les affectations actives sans date de fin ou avec date de fin future.

#### 2.9 Statistiques affectations
```
GET /api/affectations/stats
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "total": 200,
    "actives": 180,
    "inactives": 20,
    "actuelles": 175,
    "pourcentage_temps_moyen": 85.5,
    "par_service": {
      "Cardiologie": 30,
      "Urgences": 45,
      "Chirurgie": 25
    }
  }
}
```

---

## 3. RÔLES ET PROFILS - RolesProfilsController

### 3.1 RÔLES

#### 3.1.1 Lister les rôles
```
GET /api/roles-profils/roles?page=1&limit=20&search=&actif=true
```

#### 3.1.2 Récupérer un rôle
```
GET /api/roles-profils/roles/{id}
```

#### 3.1.3 Créer un rôle
```
POST /api/roles-profils/roles
```

**Body (requis):**
```json
{
  "code": "MEDECIN",
  "nom": "Médecin"
}
```

**Body (optionnel):**
```json
{
  "description": "Rôle pour les médecins",
  "niveau_acces": 5,
  "actif": true
}
```

#### 3.1.4 Mettre à jour un rôle
```
PUT /api/roles-profils/roles/{id}
```

#### 3.1.5 Supprimer un rôle
```
DELETE /api/roles-profils/roles/{id}
```

### 3.2 PROFILS

#### 3.2.1 Lister les profils
```
GET /api/roles-profils/profils?page=1&limit=20&search=&actif=true
```

#### 3.2.2 Récupérer un profil
```
GET /api/roles-profils/profils/{id}
```

#### 3.2.3 Créer un profil
```
POST /api/roles-profils/profils
```

**Body (requis):**
```json
{
  "code": "CARDIO",
  "nom": "Cardiologue"
}
```

**Body (optionnel):**
```json
{
  "description": "Profil pour les cardiologues",
  "type_profil": "MEDICAL",
  "icone": "https://...",
  "couleur": "#FF0000",
  "actif": true
}
```

#### 3.2.4 Mettre à jour un profil
```
PUT /api/roles-profils/profils/{id}
```

#### 3.2.5 Supprimer un profil
```
DELETE /api/roles-profils/profils/{id}
```

### 3.3 Statistiques
```
GET /api/roles-profils/stats
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "roles": {
      "total": 10,
      "actifs": 9,
      "inactifs": 1
    },
    "profils": {
      "total": 15,
      "actifs": 14,
      "inactifs": 1
    }
  }
}
```

---

## 4. SPÉCIALITÉS - SpecialitesController

### Endpoints principaux

#### 4.1 Lister les spécialités
```
GET /api/specialites?page=1&limit=20&search=&actif=true&sort=nom&order=ASC
```

#### 4.2 Récupérer une spécialité
```
GET /api/specialites/{id}
```

#### 4.3 Créer une spécialité
```
POST /api/specialites
```

**Body (requis):**
```json
{
  "code": "CARDIO",
  "nom": "Cardiologie"
}
```

**Body (optionnel):**
```json
{
  "description": "Spécialité de cardiologie",
  "code_snomed": "394579002",
  "icone": "https://...",
  "couleur": "#FF0000",
  "actif": true
}
```

#### 4.4 Mettre à jour une spécialité
```
PUT /api/specialites/{id}
```

#### 4.5 Supprimer une spécialité
```
DELETE /api/specialites/{id}
```

#### 4.6 Recherche avancée
```
POST /api/specialites/search
```

**Body:**
```json
{
  "page": 1,
  "limit": 20,
  "nom": "Cardiologie",
  "code": "CARDIO",
  "code_snomed": "394579002",
  "actif": true
}
```

#### 4.7 Statistiques
```
GET /api/specialites/stats
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "total": 50,
    "actives": 48,
    "inactives": 2
  }
}
```

---

## Codes d'erreur HTTP

| Code | Signification |
|------|---------------|
| 200 | OK - Requête réussie |
| 201 | Created - Ressource créée |
| 400 | Bad Request - Données invalides |
| 401 | Unauthorized - Non authentifié |
| 404 | Not Found - Ressource non trouvée |
| 409 | Conflict - Conflit (ex: doublon) |
| 500 | Internal Server Error - Erreur serveur |

---

## Format des réponses

### Succès
```json
{
  "success": true,
  "message": "Action réussie",
  "data": { ... }
}
```

### Erreur
```json
{
  "success": false,
  "error": "Description de l'erreur",
  "details": [ ... ]
}
```

---

## Authentification

Tous les endpoints nécessitent une authentification JWT valide via le header:
```
Authorization: Bearer <token>
```

---

## Pagination

Les réponses paginées incluent:
```json
{
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8
  }
}
```

---

## Filtres de recherche

### Opérateurs supportés
- `LIKE` - Recherche partielle (défaut)
- `=` - Égalité exacte
- `>`, `<`, `>=`, `<=` - Comparaison numérique
- `IN` - Appartenance à une liste

### Exemples
```
GET /api/utilisateurs?search=dupont&actif=true&hopital_id=1
POST /api/utilisateurs/search avec body JSON
```

---

## Tri et ordre

### Champs triables par défaut
- `dateCreation` (défaut)
- `dateModification`
- `nom`
- `prenom`
- `email`
- `login`

### Ordre
- `ASC` - Ascendant
- `DESC` - Descendant (défaut)

---

## Soft Delete

Les suppressions utilisent le soft delete (marquage comme inactif) pour préserver l'historique:
```
DELETE /api/utilisateurs/{id}
```

Pour une suppression physique, contactez l'administrateur.

---

## Sécurité

### Hachage des mots de passe
Les mots de passe sont automatiquement hachés avec bcrypt lors de la création/modification.

### Authentification 2FA
Peut être activée par utilisateur pour renforcer la sécurité.

### Verrouillage de compte
Les comptes peuvent être verrouillés après plusieurs tentatives de connexion échouées.

---

## Exemples d'utilisation

### Créer un utilisateur complet
```bash
curl -X POST http://localhost:8000/api/utilisateurs \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@hospital.com",
    "login": "jdupont",
    "motDePasse": "SecurePassword123!",
    "hopital_id": 1,
    "role_id": 1,
    "profil_id": 1,
    "specialite_id": 1,
    "telephone": "+33612345678",
    "dateEmbauche": "2024-01-01"
  }'
```

### Affecter un utilisateur à un service
```bash
curl -X POST http://localhost:8000/api/affectations \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "utilisateur_id": 1,
    "service_id": 1,
    "date_debut": "2024-01-01",
    "pourcentage_temps": 100
  }'
```

### Rechercher les utilisateurs actifs
```bash
curl -X GET "http://localhost:8000/api/utilisateurs?actif=true&limit=50" \
  -H "Authorization: Bearer <token>"
```

---

## Notes importantes

1. **Validation des données**: Tous les champs requis doivent être fournis
2. **Unicité**: Les champs `login`, `email`, `code` doivent être uniques
3. **Dates**: Format ISO 8601 (YYYY-MM-DD ou YYYY-MM-DDTHH:mm:ss)
4. **Pourcentages**: Entre 0 et 100
5. **Niveaux d'accès**: Entre 0 et 10
6. **Couleurs**: Format hexadécimal (#RRGGBB)

---

## Support

Pour toute question ou problème, contactez l'équipe de développement.
