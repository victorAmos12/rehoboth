# Génération de Cartes de Services - Documentation

## Vue d'ensemble

Ce système permet de générer des cartes de services professionnelles au format PDF, image ou HTML. Les cartes sont générées avec respect des permissions utilisateur et des niveaux de détail différenciés selon les rôles.

## Architecture

### Services

#### 1. **ServiceCardGeneratorService**
Service principal pour la génération des cartes.

**Méthodes:**
- `generateServiceCardPdf()` - Génère une carte au format PDF
- `generateServiceCardImage()` - Génère une carte au format image (PNG/JPG)
- `generateServiceCardHtml()` - Génère une carte au format HTML (aperçu)
- `generateMultipleServiceCardsPdf()` - Génère plusieurs cartes en un seul PDF

#### 2. **ServiceCardPermissionService**
Gère les permissions et les niveaux de détail.

**Niveaux de détail:**
- `DETAIL_NONE (0)` - Pas d'accès
- `DETAIL_BASIC (1)` - Informations basiques (description, localisation, téléphone)
- `DETAIL_INTERMEDIATE (2)` - Informations intermédiaires (+ nombre de lits, email, chef)
- `DETAIL_FULL (3)` - Toutes les informations (+ dates, IDs)

**Rôles spéciaux:**
- **Admin** - Accès complet à tous les services
- **Chef de Service** - Accès complet à son service
- **Manager/Directeur** - Accès intermédiaire
- **Personnel Médical** - Accès basique
- **Autres** - Accès basique

**Méthodes principales:**
- `canViewServiceCard()` - Vérifie si l'utilisateur peut voir une carte
- `getDetailLevel()` - Détermine le niveau de détail
- `getAccessibleServices()` - Récupère tous les services accessibles
- `getAffectedServices()` - Récupère les services affectés à l'utilisateur

## Endpoints API

### 1. Générer une carte PDF
```
GET /api/utilisateurs/{userId}/service-cards/{serviceId}/pdf
```

**Réponse:** Fichier PDF téléchargeable

**Exemple:**
```bash
curl -X GET "http://localhost:8000/api/utilisateurs/1/service-cards/5/pdf" \
  -H "Authorization: Bearer TOKEN"
```

### 2. Générer une carte Image
```
GET /api/utilisateurs/{userId}/service-cards/{serviceId}/image?format=png
```

**Paramètres:**
- `format` - 'png' (défaut) ou 'jpg'

**Réponse:** Fichier image téléchargeable

**Exemple:**
```bash
curl -X GET "http://localhost:8000/api/utilisateurs/1/service-cards/5/image?format=jpg" \
  -H "Authorization: Bearer TOKEN"
```

### 3. Aperçu HTML
```
GET /api/utilisateurs/{userId}/service-cards/{serviceId}/preview
```

**Réponse:** HTML de la carte (pour affichage dans le navigateur)

**Exemple:**
```bash
curl -X GET "http://localhost:8000/api/utilisateurs/1/service-cards/5/preview" \
  -H "Authorization: Bearer TOKEN"
```

### 4. Générer plusieurs cartes PDF
```
POST /api/utilisateurs/{userId}/service-cards/pdf-multiple
```

**Body (optionnel):**
```json
{
  "service_ids": [1, 2, 3]
}
```

Si `service_ids` n'est pas fourni, toutes les cartes accessibles seront générées.

**Réponse:** Fichier PDF multi-pages téléchargeable

**Exemple:**
```bash
curl -X POST "http://localhost:8000/api/utilisateurs/1/service-cards/pdf-multiple" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"service_ids": [1, 2, 3]}'
```

### 5. Récupérer les services accessibles
```
GET /api/utilisateurs/{userId}/accessible-services
```

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "CARD",
      "nom": "Cardiologie",
      "typeService": "Médecine",
      "hopital": "Rehoboth Hospital",
      "couleur": "#2980B9",
      "actif": true,
      "detailLevel": 3,
      "detailLevelName": "Complet"
    }
  ],
  "total": 5
}
```

### 6. Récupérer les services affectés
```
GET /api/utilisateurs/{userId}/affected-services
```

**Réponse:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "CARD",
      "nom": "Cardiologie",
      "typeService": "Médecine",
      "hopital": "Rehoboth Hospital",
      "couleur": "#2980B9",
      "actif": true
    }
  ],
  "total": 2
}
```

## Gestion des Permissions

### Vérification d'accès

L'accès à une carte de service est déterminé par:

1. **Statut de l'utilisateur** - L'utilisateur doit être actif
2. **Statut du service** - Le service doit être actif (sauf pour les admins)
3. **Hôpital** - L'utilisateur doit être du même hôpital (sauf pour les admins)
4. **Affectation** - L'utilisateur doit être affecté au service OU avoir une permission spéciale
5. **Rôle** - Certains rôles ont accès à tous les services

### Permissions spéciales

Les permissions peuvent être vérifiées via le `PermissionService`:
- `view_all_services` - Voir tous les services
- `view_service_{CODE}` - Voir un service spécifique (ex: `view_service_CARD`)

## Templates Twig

### Fichiers de templates

1. **pdf_card.html.twig** - Carte unique au format PDF
2. **image_card.html.twig** - Carte au format image
3. **html_card.html.twig** - Aperçu HTML interactif
4. **pdf_cards_multiple.html.twig** - Plusieurs cartes en PDF

### Personnalisation

Les templates utilisent les variables suivantes:
- `service` - Données du service
- `user` - Données de l'utilisateur
- `detailLevel` - Niveau de détail (0-3)
- `generatedAt` - Date de génération

## Gestion des Erreurs

### Codes d'erreur

- **404** - Utilisateur ou service non trouvé
- **403** - Accès refusé (permissions insuffisantes)
- **500** - Erreur serveur lors de la génération

### Exemples de réponse d'erreur

```json
{
  "success": false,
  "error": "Vous n'avez pas les permissions pour accéder à cette carte de service"
}
```

## Configuration KnpSnappyBundle

Le système utilise KnpSnappyBundle pour la génération PDF/Image.

**Configuration (config/packages/knp_snappy.yaml):**
```yaml
knp_snappy:
    pdf:
        enabled:    true
        binary:     '%env(WKHTMLTOPDF_PATH)%'
        options:    []
    image:
        enabled:    true
        binary:     '%env(WKHTMLTOIMAGE_PATH)%'
        options:    []
```

**Variables d'environnement (.env):**
```
WKHTMLTOPDF_PATH=/usr/local/bin/wkhtmltopdf
WKHTMLTOIMAGE_PATH=/usr/local/bin/wkhtmltoimage
```

## Cas d'usage

### 1. Générer une carte pour affichage
```javascript
// Récupérer l'aperçu HTML
fetch(`/api/utilisateurs/1/service-cards/5/preview`)
  .then(r => r.text())
  .then(html => {
    document.getElementById('card-container').innerHTML = html;
  });
```

### 2. Télécharger une carte PDF
```javascript
// Télécharger directement
window.location.href = `/api/utilisateurs/1/service-cards/5/pdf`;
```

### 3. Générer un rapport multi-services
```javascript
// Générer un PDF avec plusieurs services
fetch(`/api/utilisateurs/1/service-cards/pdf-multiple`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ service_ids: [1, 2, 3] })
})
.then(r => r.blob())
.then(blob => {
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'cartes_services.pdf';
  a.click();
});
```

### 4. Vérifier les permissions avant génération
```javascript
// Récupérer les services accessibles
fetch(`/api/utilisateurs/1/accessible-services`)
  .then(r => r.json())
  .then(data => {
    console.log(`${data.total} service(s) accessible(s)`);
    data.data.forEach(service => {
      console.log(`${service.nom} - Niveau: ${service.detailLevelName}`);
    });
  });
```

## Bonnes pratiques

1. **Vérifier les permissions** avant de générer une carte
2. **Utiliser les aperçus HTML** pour les affichages rapides
3. **Générer les PDFs** uniquement quand nécessaire (opération coûteuse)
4. **Mettre en cache** les cartes générées si possible
5. **Valider les IDs** avant de faire les requêtes
6. **Gérer les erreurs** correctement côté client

## Dépannage

### Erreur: "Vous n'avez pas les permissions"
- Vérifiez que l'utilisateur est actif
- Vérifiez que le service est actif
- Vérifiez que l'utilisateur est du même hôpital
- Vérifiez que l'utilisateur est affecté au service

### Erreur: "Service non trouvé"
- Vérifiez l'ID du service
- Vérifiez que le service existe en base de données

### Erreur: "Erreur lors de la génération"
- Vérifiez que wkhtmltopdf/wkhtmltoimage est installé
- Vérifiez les chemins dans .env
- Vérifiez les permissions de fichiers

## Performance

- Les cartes HTML sont générées rapidement
- Les cartes PDF/Image sont plus lentes (utilisation de wkhtmltopdf)
- Considérez la mise en cache pour les cartes fréquemment demandées
- Limitez le nombre de cartes par PDF (recommandé: max 50)

## Sécurité

- Toutes les cartes respectent les permissions utilisateur
- Les données sensibles sont filtrées selon le niveau de détail
- Les erreurs ne révèlent pas d'informations sensibles
- Les fichiers générés sont temporaires et nettoyés
