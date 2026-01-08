# Guide d'utilisation - Endpoint d'authentification dynamique

## 🎯 Concept

L'endpoint `/api/auth/me` retourne **TOUTES** les données nécessaires au frontend en un seul appel :

- Informations utilisateur
- Rôle et permissions (depuis la BD)
- Menus accessibles
- Capacités par module (calculées dynamiquement)

**Aucun code statique** - tout vient de la base de données !

---

## 📡 Endpoint principal

### GET /api/auth/me

**Headers:**
```
Authorization: Bearer <token_jwt>
```

**Réponse:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean.dupont@hospital.com",
    "login": "jdupont",
    "telephone": "+33612345678",
    "photoProfil": "https://...",
    "actif": true,
    "hopital": {
      "id": 1,
      "nom": "Hôpital Central"
    }
  },
  "role": {
    "id": 2,
    "code": "MEDECIN",
    "nom": "Médecin",
    "niveauAcces": 5
  },
  "permissions": [
    "PATIENTS_READ",
    "CONSULTATIONS_READ",
    "CONSULTATIONS_CREATE",
    "CONSULTATIONS_UPDATE",
    "UTILISATEURS_READ"
  ],
  "menus": [
    {
      "id": 1,
      "code": "DASHBOARD",
      "nom": "Tableau de bord",
      "icone": "fas fa-chart-line",
      "route": "/dashboard",
      "module": "dashboard",
      "ordre": 1,
      "children": []
    },
    {
      "id": 2,
      "code": "PATIENTS",
      "nom": "Patients",
      "icone": "fas fa-users",
      "route": "/patients",
      "module": "patients",
      "ordre": 2,
      "children": [
        {
          "id": 3,
          "code": "PATIENTS_LIST",
          "nom": "Liste des patients",
          "route": "/patients/list",
          "ordre": 1,
          "children": []
        }
      ]
    }
  ],
  "capabilities": {
    "patients": {
      "read": true,
      "create": false,
      "update": false,
      "delete": false
    },
    "consultations": {
      "read": true,
      "create": true,
      "update": true,
      "delete": false
    },
    "utilisateurs": {
      "read": true,
      "create": false,
      "update": false,
      "delete": false,
      "reset_password": false,
      "lock": false,
      "toggle_2fa": false
    },
    "dossiers_medicaux": {
      "read": true,
      "create": false,
      "update": false,
      "delete": false
    }
  }
}
```

---

## 🚀 Utilisation au login

### 1. Appel après authentification

```typescript
// Après login réussi
const response = await fetch('/api/auth/me', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();

// Stocker les données
localStorage.setItem('user', JSON.stringify(data.user));
localStorage.setItem('role', JSON.stringify(data.role));
localStorage.setItem('permissions', JSON.stringify(data.permissions));
localStorage.setItem('menus', JSON.stringify(data.menus));
localStorage.setItem('capabilities', JSON.stringify(data.capabilities));
```

### 2. Utiliser les capacités au frontend

```typescript
// Afficher un bouton seulement si l'utilisateur peut créer
const capabilities = JSON.parse(localStorage.getItem('capabilities'));

if (capabilities.patients.create) {
  // Afficher le bouton "Créer patient"
}

if (capabilities.utilisateurs.reset_password) {
  // Afficher le bouton "Réinitialiser mot de passe"
}
```

### 3. Afficher les menus

```typescript
const menus = JSON.parse(localStorage.getItem('menus'));

// Afficher l'arborescence des menus
menus.forEach(menu => {
  console.log(menu.nom); // "Tableau de bord", "Patients", etc.
  
  menu.children.forEach(child => {
    console.log(`  - ${child.nom}`);
  });
});
```

---

## 🔧 Ajouter un nouveau service/module

### Étape 1: Créer les permissions en BD

```bash
POST /api/administrations/permissions
Content-Type: application/json

{
  "code": "FACTURES_READ",
  "nom": "Voir les factures",
  "module": "factures",
  "action": "read",
  "role_ids": [1, 2]
}

{
  "code": "FACTURES_CREATE",
  "nom": "Créer une facture",
  "module": "factures",
  "action": "create",
  "role_ids": [1]
}

{
  "code": "FACTURES_UPDATE",
  "nom": "Modifier une facture",
  "module": "factures",
  "action": "update",
  "role_ids": [1]
}

{
  "code": "FACTURES_DELETE",
  "nom": "Supprimer une facture",
  "module": "factures",
  "action": "delete",
  "role_ids": [1]
}
```

### Étape 2: Assigner les permissions aux rôles

```bash
POST /api/administrations/permissions/1/add-role
{
  "role_id": 2
}
```

### Étape 3: Créer le menu (optionnel)

```bash
POST /api/administrations/menus
{
  "code": "FACTURES",
  "nom": "Factures",
  "route": "/factures",
  "icone": "fas fa-receipt",
  "module": "factures",
  "ordre": 5,
  "role_ids": [1, 2]
}
```

### Étape 4: Créer l'API du service

```php
#[Route('/api/factures', name: 'api_factures_')]
class FacturesController extends AbstractController
{
    public function __construct(
        private PermissionService $permissionService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            // Vérifier la permission
            $this->permissionService->requirePermission('FACTURES_READ');
            
            // ... reste du code
        } catch (AccessDeniedException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Accès refusé',
            ], 403);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        try {
            // Vérifier la permission
            $this->permissionService->requirePermission('FACTURES_CREATE');
            
            // ... reste du code
        } catch (AccessDeniedException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Accès refusé',
            ], 403);
        }
    }
}
```

### Étape 5: Appeler /api/auth/me

L'endpoint retournera automatiquement les capacités pour le nouveau module :

```json
{
  "capabilities": {
    "factures": {
      "read": true,
      "create": false,
      "update": false,
      "delete": false
    }
  }
}
```

---

## 📋 Flux complet

### 1. Login
```
POST /api/login
→ Retourne token JWT
```

### 2. Récupérer les données utilisateur
```
GET /api/auth/me
→ Retourne user, role, permissions, menus, capabilities
```

### 3. Stocker les données
```typescript
localStorage.setItem('user', JSON.stringify(data.user));
localStorage.setItem('capabilities', JSON.stringify(data.capabilities));
localStorage.setItem('menus', JSON.stringify(data.menus));
```

### 4. Afficher l'interface
```typescript
// Afficher les menus
renderMenus(data.menus);

// Afficher les boutons selon les capacités
if (data.capabilities.patients.create) {
  showCreateButton();
}
```

### 5. Appeler les APIs
```typescript
// Chaque appel API inclut le token
fetch('/api/patients', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

---

## 🔐 Sécurité

### Frontend
- Affiche/cache les boutons selon `capabilities`
- Affiche les menus selon `menus`

### Backend
- Vérifie les permissions sur chaque endpoint
- Retourne 403 si non autorisé
- Les données sensibles ne sont jamais exposées

### Exemple d'erreur
```json
{
  "success": false,
  "error": "Accès refusé. Permission requise: FACTURES_CREATE"
}
```

---

## 💡 Avantages de cette approche

✅ **Dynamique** - Aucun code statique, tout vient de la BD
✅ **Scalable** - Ajouter un nouveau service = ajouter des permissions en BD
✅ **Sécurisé** - Vérification côté backend + frontend
✅ **Centralisé** - Un seul endpoint pour toutes les données
✅ **Performant** - Une seule requête au login
✅ **Flexible** - Modifier les permissions sans redéployer

---

## 📝 Checklist pour ajouter un nouveau service

- [ ] Créer les permissions en BD (READ, CREATE, UPDATE, DELETE)
- [ ] Assigner les permissions aux rôles
- [ ] Créer le menu (optionnel)
- [ ] Créer le contrôleur API
- [ ] Ajouter les vérifications de permissions
- [ ] Tester avec `/api/auth/me`
- [ ] Vérifier que les capacités apparaissent
- [ ] Implémenter au frontend

---

**C'est tout !** L'endpoint `/api/auth/me` s'adapte automatiquement à tous les nouveaux services.
