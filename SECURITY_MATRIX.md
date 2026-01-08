# Configuration de sécurité - Contrôle d'accès par rôle

## 📋 Matrice de contrôle d'accès

### UTILISATEURS

| Action | ADMIN | RH | MEDECIN | INFIRMIER | PHARMACIEN | RECEPTIONNISTE |
|--------|-------|----|---------|-----------|-----------|----|
| **Lister** | ✅ | ✅ | ✅ (lecture) | ❌ | ❌ | ❌ |
| **Voir détails** | ✅ | ✅ | ✅ (lecture) | ❌ | ❌ | ❌ |
| **Créer** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Modifier** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Supprimer** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Reset password** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Verrouiller compte** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Gérer 2FA** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### PATIENTS

| Action | ADMIN | RH | MEDECIN | INFIRMIER | PHARMACIEN | RECEPTIONNISTE |
|--------|-------|----|---------|-----------|-----------|----|
| **Lister** | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| **Voir détails** | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| **Créer** | ✅ | ❌ | ❌ | ❌ | ❌ | ��� |
| **Modifier** | ✅ | ❌ | ❌ | ✅ | ❌ | ✅ |
| **Supprimer** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

### CONSULTATIONS

| Action | ADMIN | RH | MEDECIN | INFIRMIER | PHARMACIEN | RECEPTIONNISTE |
|--------|-------|----|---------|-----------|-----------|----|
| **Lister** | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Voir détails** | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **Créer** | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Modifier** | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **Supprimer** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

### DOSSIERS MÉDICAUX

| Action | ADMIN | RH | MEDECIN | INFIRMIER | PHARMACIEN | RECEPTIONNISTE |
|--------|-------|----|---------|-----------|-----------|----|
| **Lister** | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Voir détails** | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ |
| **Créer** | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **Modifier** | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| **Supprimer** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

### AFFECTATIONS

| Action | ADMIN | RH | MEDECIN | INFIRMIER | PHARMACIEN | RECEPTIONNISTE |
|--------|-------|----|---------|-----------|-----------|----|
| **Lister** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Voir détails** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Créer** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Modifier** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Supprimer** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

### MENUS & PERMISSIONS

| Action | ADMIN | RH | MEDECIN | INFIRMIER | PHARMACIEN | RECEPTIONNISTE |
|--------|-------|----|---------|-----------|-----------|----|
| **Lister** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Créer** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Modifier** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Supprimer** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 🔐 Implémentation dans les contrôleurs

### Exemple 1: Endpoint de création (POST)

```php
#[Route('', name: 'create', methods: ['POST'])]
public function create(Request $request, PermissionService $permissionService): JsonResponse
{
    try {
        // Vérifier que l'utilisateur est ADMIN ou RH
        $permissionService->requireAnyRole(['ADMIN', 'RH']);
        
        // ... reste du code
    } catch (AccessDeniedException $e) {
        return $this->json([
            'success' => false,
            'error' => 'Accès refusé: ' . $e->getMessage(),
        ], 403);
    }
}
```

### Exemple 2: Endpoint de modification (PUT)

```php
#[Route('/{id}', name: 'update', methods: ['PUT'])]
public function update(int $id, Request $request, PermissionService $permissionService): JsonResponse
{
    try {
        // Vérifier que l'utilisateur est ADMIN ou RH
        $permissionService->requireAnyRole(['ADMIN', 'RH']);
        
        // ... reste du code
    } catch (AccessDeniedException $e) {
        return $this->json([
            'success' => false,
            'error' => 'Accès refusé: ' . $e->getMessage(),
        ], 403);
    }
}
```

### Exemple 3: Endpoint de lecture (GET)

```php
#[Route('', name: 'list', methods: ['GET'])]
public function list(Request $request, PermissionService $permissionService): JsonResponse
{
    try {
        // Vérifier que l'utilisateur a au moins un rôle autorisé
        $permissionService->requireAnyRole(['ADMIN', 'RH', 'MEDECIN', 'INFIRMIER', 'PHARMACIEN', 'RECEPTIONNISTE']);
        
        // ... reste du code
    } catch (AccessDeniedException $e) {
        return $this->json([
            'success' => false,
            'error' => 'Accès refusé: ' . $e->getMessage(),
        ], 403);
    }
}
```

---

## 🎯 Endpoints à sécuriser

### UTILISATEURS (UtilisateursControllers)

- ✅ `GET /api/utilisateurs` - Lecture: ADMIN, RH, MEDECIN
- ✅ `GET /api/utilisateurs/{id}` - Lecture: ADMIN, RH, MEDECIN
- ❌ `POST /api/utilisateurs` - Création: ADMIN, RH
- ❌ `PUT /api/utilisateurs/{id}` - Modification: ADMIN, RH
- ❌ `DELETE /api/utilisateurs/{id}` - Suppression: ADMIN, RH
- ❌ `POST /api/utilisateurs/{id}/reset-password` - ADMIN, RH
- ❌ `POST /api/utilisateurs/{id}/lock` - ADMIN, RH
- ❌ `POST /api/utilisateurs/{id}/2fa` - ADMIN, RH

### PATIENTS (PatientController)

- ✅ `GET /api/patients` - Lecture: ADMIN, MEDECIN, INFIRMIER, PHARMACIEN, RECEPTIONNISTE
- ✅ `GET /api/patients/{id}` - Lecture: ADMIN, MEDECIN, INFIRMIER, PHARMACIEN, RECEPTIONNISTE
- ❌ `POST /api/patients` - Création: ADMIN, RECEPTIONNISTE
- ❌ `PUT /api/patients/{id}` - Modification: ADMIN, INFIRMIER, RECEPTIONNISTE
- ❌ `DELETE /api/patients/{id}` - Suppression: ADMIN

### CONSULTATIONS (ConsultationsController)

- ✅ `GET /api/consultations` - Lecture: ADMIN, MEDECIN, INFIRMIER
- ✅ `GET /api/consultations/{id}` - Lecture: ADMIN, MEDECIN, INFIRMIER
- ❌ `POST /api/consultations` - Création: ADMIN, MEDECIN
- ❌ `PUT /api/consultations/{id}` - Modification: ADMIN, MEDECIN
- ❌ `DELETE /api/consultations/{id}` - Suppression: ADMIN

### DOSSIERS MÉDICAUX (DossierMedicauxController)

- ✅ `GET /api/dossiers-medicaux` - Lecture: ADMIN, MEDECIN, INFIRMIER, PHARMACIEN
- ✅ `GET /api/dossiers-medicaux/{id}` - Lecture: ADMIN, MEDECIN, INFIRMIER, PHARMACIEN
- ❌ `POST /api/dossiers-medicaux` - Création: ADMIN, INFIRMIER
- ❌ `PUT /api/dossiers-medicaux/{id}` - Modification: ADMIN, INFIRMIER
- ❌ `DELETE /api/dossiers-medicaux/{id}` - Suppression: ADMIN

### AFFECTATIONS (AffectationsUtilisateursController)

- ✅ `GET /api/affectations` - Lecture: ADMIN, RH
- ✅ `GET /api/affectations/{id}` - Lecture: ADMIN, RH
- ❌ `POST /api/affectations` - Cr��ation: ADMIN, RH
- ❌ `PUT /api/affectations/{id}` - Modification: ADMIN, RH
- ❌ `DELETE /api/affectations/{id}` - Suppression: ADMIN, RH

### MENUS & PERMISSIONS (MenusPermissionsController)

- ✅ `GET /api/administrations/menus` - Lecture: ADMIN
- ❌ `POST /api/administrations/menus` - Création: ADMIN
- ❌ `PUT /api/administrations/menus/{id}` - Modification: ADMIN
- ❌ `DELETE /api/administrations/menus/{id}` - Suppression: ADMIN
- ✅ `GET /api/administrations/permissions` - Lecture: ADMIN
- ❌ `POST /api/administrations/permissions` - Création: ADMIN
- ❌ `PUT /api/administrations/permissions/{id}` - Modification: ADMIN
- ❌ `DELETE /api/administrations/permissions/{id}` - Suppression: ADMIN

---

## 📝 Codes d'erreur

| Code | Signification |
|------|---------------|
| 200 | OK - Requête réussie |
| 201 | Created - Ressource créée |
| 400 | Bad Request - Données invalides |
| 401 | Unauthorized - Non authentifié |
| 403 | Forbidden - Accès refusé (permission insuffisante) |
| 404 | Not Found - Ressource non trouvée |
| 409 | Conflict - Conflit (ex: doublon) |
| 500 | Internal Server Error - Erreur serveur |

---

## 🚀 Prochaines étapes

1. ✅ Créer le service PermissionService
2. ✅ Créer le contrôleur AuthInfoController
3. ⏳ Ajouter les vérifications dans tous les contrôleurs
4. ⏳ Tester chaque endpoint
5. ⏳ Documenter les erreurs 403

---

**Note:** Les vérifications doivent être ajoutées au début de chaque méthode sensible.
