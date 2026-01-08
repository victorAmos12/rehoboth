# Guide de correction - Menus manquants pour les médecins

## 🔴 Problème identifié

Quand vous vous connectez comme médecin, aucun menu n'apparaît. Cela est dû au fait que **les menus ne sont pas associés au rôle "MEDECIN"** dans la base de données.

## 🔍 Diagnostic

Le système utilise une relation **ManyToMany** entre les menus et les rôles:
- Table `menu_roles` lie les menus aux rôles
- Chaque menu doit être explicitement associé à un rôle pour être visible

### Vérifier le problème

```bash
GET /api/administrations/diagnostics
```

Cela vous montrera:
- Quels rôles n'ont pas de menus
- Quels menus ne sont associés à aucun rôle
- Les problèmes détectés

## ✅ Solutions

### Solution 1: Initialiser les données de base (RECOMMANDÉ)

Cette solution crée automatiquement les rôles, menus et permissions de base, et les associe correctement.

```bash
POST /api/administrations/init/setup-basic-data
```

**Réponse:**
```json
{
  "success": true,
  "message": "Données de base initialisées avec succès",
  "created": {
    "roles": ["ADMIN", "MEDECIN", "INFIRMIER", "RECEPTIONNISTE", "PHARMACIEN"],
    "menus": ["DASHBOARD", "PATIENTS", "CONSULTATIONS", "UTILISATEURS", "PARAMETRES"],
    "permissions": ["VIEW_PATIENTS", "CREATE_PATIENT", "EDIT_PATIENT", "DELETE_PATIENT", ...]
  }
}
```

### Solution 2: Assigner tous les menus à un rôle spécifique

Si vous avez déjà des menus et des rôles, vous pouvez les associer manuellement:

```bash
POST /api/administrations/fix/assign-all-menus-to-role
Content-Type: application/json

{
  "role_id": 2
}
```

Remplacez `2` par l'ID du rôle MEDECIN.

### Solution 3: Assigner les menus individuellement

Pour plus de contrôle, vous pouvez assigner les menus un par un:

```bash
POST /api/administrations/menus/{menu_id}/add-role
Content-Type: application/json

{
  "role_id": 2
}
```

### Solution 4: Créer les menus manuellement

Si vous n'avez pas de menus, créez-les d'abord:

```bash
POST /api/administrations/menus
Content-Type: application/json

{
  "code": "DASHBOARD",
  "nom": "Tableau de bord",
  "route": "/dashboard",
  "icone": "fas fa-chart-line",
  "ordre": 1,
  "actif": true,
  "role_ids": [2]
}
```

## 📋 Étapes recommandées pour corriger

### Étape 1: Vérifier le diagnostic
```bash
GET /api/administrations/diagnostics
```

### Étape 2: Identifier les rôles sans menus
```bash
GET /api/administrations/roles-without-menus
```

### Étape 3: Initialiser les données de base
```bash
POST /api/administrations/init/setup-basic-data
```

### Étape 4: Vérifier que ça marche
```bash
GET /api/administrations/roles/2/menus
```

(Remplacez `2` par l'ID du rôle MEDECIN)

## 🔗 Endpoints utiles

### Gestion des menus
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/administrations/menus` | GET | Lister tous les menus |
| `/api/administrations/menus` | POST | Créer un menu |
| `/api/administrations/menus/{id}` | GET | Détails d'un menu |
| `/api/administrations/menus/{id}` | PUT | Modifier un menu |
| `/api/administrations/menus/{id}/add-role` | POST | Ajouter un rôle à un menu |
| `/api/administrations/menus/{id}/remove-role` | POST | Retirer un rôle d'un menu |

### Gestion des permissions
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/administrations/permissions` | GET | Lister toutes les permissions |
| `/api/administrations/permissions` | POST | Créer une permission |
| `/api/administrations/permissions/{id}` | GET | Détails d'une permission |
| `/api/administrations/permissions/{id}` | PUT | Modifier une permission |
| `/api/administrations/permissions/{id}/add-role` | POST | Ajouter un rôle à une permission |
| `/api/administrations/permissions/{id}/remove-role` | POST | Retirer un rôle d'une permission |

### Récupération par rôle
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/administrations/roles/{roleId}/menus` | GET | Menus d'un rôle |
| `/api/administrations/roles/{roleId}/permissions` | GET | Permissions d'un rôle |

### Diagnostic et correction
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/administrations/diagnostics` | GET | Diagnostic complet |
| `/api/administrations/roles-without-menus` | GET | Rôles sans menus |
| `/api/administrations/init/setup-basic-data` | POST | Initialiser les données |
| `/api/administrations/fix/assign-all-menus-to-role` | POST | Assigner tous les menus |
| `/api/administrations/fix/assign-all-permissions-to-role` | POST | Assigner toutes les permissions |

## 📊 Structure des données

### Relation Menus ↔ Rôles
```
Menus (ManyToMany) Roles
├── Table: menu_roles
├── Colonnes: menu_id, role_id
└── Permet: Un menu visible par plusieurs rôles
```

### Relation Permissions ↔ Rôles
```
Permissions (ManyToMany) Roles
├── Table: role_permissions
├── Colonnes: permission_id, role_id
└── Permet: Une permission accordée à plusieurs rôles
```

## 🎯 Exemple complet de correction

### 1. Vérifier le diagnostic
```bash
curl -X GET http://localhost:8000/api/administrations/diagnostics \
  -H "Authorization: Bearer <token>"
```

### 2. Initialiser les données
```bash
curl -X POST http://localhost:8000/api/administrations/init/setup-basic-data \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json"
```

### 3. Vérifier les menus du médecin
```bash
curl -X GET http://localhost:8000/api/administrations/roles/2/menus \
  -H "Authorization: Bearer <token>"
```

### 4. Tester la connexion
Connectez-vous comme médecin et vérifiez que les menus apparaissent.

## 🔧 Troubleshooting

### Les menus n'apparaissent toujours pas

1. **Vérifier le token JWT**
   - Assurez-vous que le token est valide
   - Vérifiez que l'utilisateur a un rôle assigné

2. **Vérifier l'ID du rôle**
   - Récupérez l'ID du rôle MEDECIN
   - Vérifiez que les menus sont associés à ce rôle

3. **Vérifier les menus actifs**
   - Les menus doivent avoir `actif = true`
   - Vérifiez dans la base de données

4. **Vérifier les associations**
   ```bash
   GET /api/administrations/diagnostics
   ```

### Les menus apparaissent mais pas les enfants

- Vérifiez que les menus enfants ont le bon `parent_id`
- Vérifiez que les menus enfants sont associés au même rôle

### Erreur lors de l'initialisation

- Vérifiez que les rôles n'existent pas déjà
- Vérifiez que les menus n'existent pas déjà
- Vérifiez les permissions de la base de données

## 📝 Notes importantes

1. **Soft delete**: Les menus supprimés sont marqués comme inactifs, pas supprimés physiquement
2. **Ordre des menus**: L'ordre est géré par le champ `ordre`
3. **Hiérarchie**: Les menus enfants utilisent `parent_id`
4. **Permissions**: Les permissions sont indépendantes des menus
5. **Authentification**: Tous les endpoints nécessitent un token JWT valide

## 🚀 Prochaines étapes

Une fois les menus corrigés:

1. Vérifier que le frontend reçoit les menus correctement
2. Implémenter le contrôle d'accès basé sur les permissions
3. Ajouter des menus spécifiques par rôle
4. Configurer les permissions granulaires

---

**Besoin d'aide?** Consultez la documentation complète ou contactez l'équipe de développement.
