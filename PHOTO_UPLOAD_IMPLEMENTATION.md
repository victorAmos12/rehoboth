# Photo Upload Implementation Guide

## Backend Implementation ✅

### New Endpoint
**PUT** `/api/utilisateurs/{id}/profile`

Handles FormData multipart requests for updating user profiles with file uploads.

### Supported Features
- Upload `photo_profil` (profile photo)
- Upload `signature_numerique` (digital signature)
- Update any user field via FormData
- Automatic cleanup of old files
- File validation:
  - Allowed MIME types: `image/jpeg`, `image/png`, `image/gif`, `image/webp`
  - Max file size: 5 MB
- Files stored in: `public/uploads/profils/`
- Filename format: `{prefix}{uniqueId}.{extension}`

### Success Response (200)
```json
{
  "success": true,
  "message": "Profil utilisateur mis à jour avec succès",
  "data": {
    "id": 1,
    "nom": "Dupont",
    "prenom": "Jean",
    "email": "jean@example.com",
    "photoProfil": "/uploads/profils/photo_67a1b2c3d4e5f.jpg",
    "signatureNumerique": "/uploads/profils/signature_67a1b2c3d4e5f.jpg",
    ...
  }
}
```

### Error Responses

**404 - User Not Found**
```json
{
  "success": false,
  "error": "Utilisateur non trouvé"
}
```

**409 - Email Already Used**
```json
{
  "success": false,
  "error": "Cet email est déjà utilisé"
}
```

**400 - Validation Error**
```json
{
  "success": false,
  "error": "Erreur de validation",
  "details": ["Email must be unique", ...]
}
```

**500 - Server Error**
```json
{
  "success": false,
  "error": "Erreur lors de la mise à jour du profil: ..."
}
```

---

## Angular Service Implementation

### Method 1: Update Profile with File Upload (Recommended for Files)

```typescript
// In your UtilisateursService
updateProfileWithFile(id: number, formData: FormData): Observable<UtilisateurResponse> {
  return this.http.put<UtilisateurResponse>(
    `${this.apiUrl}/${id}/profile`,
    formData,
    {
      headers: this.getHeaders()
      // Do NOT set Content-Type - let the browser set it to multipart/form-data
    }
  );
}
```

### Method 2: Prepare FormData in Component

```typescript
// In your user profile edit component
onPhotoSelected(event: Event, userId: number): void {
  const input = event.target as HTMLInputElement;
  const files = input.files;
  
  if (!files || files.length === 0) return;

  const formData = new FormData();
  
  // Add file
  formData.append('photo_profil', files[0]);
  
  // Add other fields (optional)
  formData.append('nom', this.form.get('nom').value);
  formData.append('prenom', this.form.get('prenom').value);
  formData.append('email', this.form.get('email').value);
  formData.append('telephone', this.form.get('telephone').value);
  
  // Call the service
  this.utilisateursService.updateProfileWithFile(userId, formData).subscribe(
    (response) => {
      console.log('Profile updated:', response);
      // Update local state
      this.utilisateur = response.data;
      this.showSuccessMessage('Photo mise à jour avec succès');
    },
    (error) => {
      console.error('Update failed:', error);
      this.showErrorMessage(error.error?.error || 'Erreur lors de la mise à jour');
    }
  );
}
```

### Method 3: HTML Template

```html
<!-- File input for photo -->
<input 
  #photoInput
  type="file" 
  accept="image/*"
  (change)="onPhotoSelected($event, userId)"
  style="display: none"
/>

<!-- Trigger button -->
<button (click)="photoInput.click()">
  Change Photo
</button>

<!-- Display current photo -->
<img 
  *ngIf="utilisateur.photoProfil" 
  [src]="utilisateur.photoProfil" 
  alt="Profile Photo"
  width="100"
/>
```

---

## Migration Path

### Old Method (JSON-only)
```typescript
// Before: Only worked for JSON data, not files
update(id: number, payload: any): Observable<UtilisateurResponse> {
  return this.http.put<UtilisateurResponse>(`${this.apiUrl}/${id}`, payload);
}
```

### New Method (FormData + JSON hybrid)
```typescript
// After: Handles FormData with files + regular form fields
updateProfileWithFile(id: number, formData: FormData): Observable<UtilisateurResponse> {
  return this.http.put<UtilisateurResponse>(
    `${this.apiUrl}/${id}/profile`,
    formData,
    { headers: this.getHeaders() }
  );
}
```

### Recommendation
- Use `updateProfileWithFile()` when uploading files or doing bulk profile updates
- Keep `update()` for simple JSON updates (e.g., just changing email)
- Both methods update the same `utilisateurs` record

---

## Backend Code Reference

### File Upload Handler Location
File: `src/Controller/Api/RessourcesHumaines/UtilisateursControllers.php`

Key Methods:
- `updateProfile()` - Main handler for FormData multipart requests (line ~742)
- `saveUploadedFile()` - Utility to validate and save uploaded files

### Key Features Implemented
1. **Automatic directory creation** - Creates `public/uploads/profils/` if it doesn't exist
2. **File validation** - Checks MIME type and file size
3. **Old file cleanup** - Deletes previous photo/signature before saving new ones
4. **Unique filenames** - Uses timestamp + random suffix to prevent collisions
5. **Flexible field handling** - Supports both camelCase and snake_case field names
6. **Duplicate email prevention** - Validates email uniqueness before saving

---

## Testing

### cURL Example
```bash
curl -X PUT http://localhost:8000/api/utilisateurs/1/profile \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -F "photo_profil=@/path/to/photo.jpg" \
  -F "nom=Dupont" \
  -F "prenom=Jean" \
  -F "email=jean@example.com"
```

### Postman Steps
1. Create a `PUT` request to `http://localhost:8000/api/utilisateurs/{id}/profile`
2. Go to **Body** tab
3. Select **form-data**
4. Add files:
   - Key: `photo_profil`, Type: File, Value: Select your image
   - Key: `signature_numerique`, Type: File, Value: Select your signature image (optional)
5. Add text fields:
   - Key: `nom`, Value: User's last name
   - Key: `prenom`, Value: User's first name
   - etc.
6. Add **Authorization** header: `Bearer YOUR_JWT_TOKEN`
7. Click **Send**

---

## File Storage

### Directory Structure
```
public/
  uploads/
    profils/
      photo_67a1b2c3d4e5f.jpg
      signature_67a1b2c3d4e5f.png
      photo_67a1b2c3d4e5g.jpg
      ...
```

### Access URL
- Frontend URL: `/uploads/profils/photo_67a1b2c3d4e5f.jpg`
- Full URL: `http://localhost:8000/uploads/profils/photo_67a1b2c3d4e5f.jpg`
- Stored in DB: `/uploads/profils/photo_67a1b2c3d4e5f.jpg` (relative path)

---

## Configuration

### Max File Size
Currently set to **5 MB** in `saveUploadedFile()` method.

To change:
```php
// Line in saveUploadedFile() method
if ($file->getSize() > 10 * 1024 * 1024) { // 10 MB
    return null;
}
```

### Allowed MIME Types
Currently: `image/jpeg`, `image/png`, `image/gif`, `image/webp`

To add more types:
```php
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
```

### Upload Directory
Currently: `public/uploads/profils/`

To change in `updateProfile()` method:
```php
$uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/custom-path';
```

---

## Troubleshooting

### Issue: "404 Photo not found after upload"
**Solution**: Check that the file was actually saved to `public/uploads/profils/`

### Issue: "Permission denied" error
**Solution**: Ensure `public/uploads/profils/` directory is writable by PHP/web server:
```bash
chmod 755 public/uploads/profils/
```

### Issue: File upload returns null but no error message
**Solution**: Check file validation:
- Is MIME type in allowed list?
- Is file size < 5 MB?
- Run with debug mode to see actual error

### Issue: FormData sent as JSON instead of multipart
**Solution**: Ensure Angular service does NOT set `Content-Type` header:
```typescript
// ❌ Wrong - prevents multipart
headers: new HttpHeaders({ 'Content-Type': 'multipart/form-data' })

// ✅ Correct - let browser set it
// Don't set Content-Type at all, or use getHeaders() that doesn't include it
```

---

## Security Notes

1. **File Type Validation**: Only image MIME types accepted
2. **File Size Limit**: 5 MB max to prevent DoS
3. **Filename Sanitization**: Uses `uniqid()` + file extension, no user input in filename
4. **JWT Required**: All endpoints require valid JWT token in Authorization header
5. **Old File Cleanup**: Previous files automatically deleted to prevent disk bloat

---

## Next Steps

- [ ] Update Angular UtilisateursService with `updateProfileWithFile()` method
- [ ] Update user profile edit component to use file upload
- [ ] Add photo preview before upload
- [ ] Add file size/type validation on frontend
- [ ] Test end-to-end with Angular app
- [ ] Add CSS for upload UI
- [ ] Consider adding crop/resize functionality

