# 📚 Documentation API - Rehoboth HIS

## 🔗 URL de Base
```
http://localhost:8000
```

## 🔐 Authentification

### Endpoint de Connexion
```
POST /api/auth/login
```

**URL complète :**
```
http://localhost:8000/api/auth/login
```

**Headers requis :**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON) - Option 1 : Avec login**
```json
{
  "login": "admin",
  "password": "Admin@123456"
}
```

**Body (JSON) - Option 2 : Avec email**
```json
{
  "email": "admin@rehoboth.com",
  "password": "Admin@123456"
}
```

**Body (JSON) - Option 3 : Avec login ET email**
```json
{
  "login": "admin",
  "email": "admin@rehoboth.com",
  "password": "Admin@123456"
}
```

**Réponse (Succès - 200) :**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "user": {
    "id": 1,
    "email": "admin@rehoboth.com",
    "login": "admin",
    "nom": "Dupont",
    "prenom": "Jean",
    "telephone": "+33612345678",
    "role": "Administrateur",
    "profil": "Administrateur",
    "specialite": null,
    "hopital": "Rehoboth Hospital",
    "photo": null
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Réponse (Erreur - 401) :**
```json
{
  "success": false,
  "error": "Identifiants invalides"
}
```

---

## 🛠️ Utilisation dans le Frontend

### Avec JavaScript/Fetch

```javascript
// 1. Connexion
const loginResponse = await fetch('http://localhost:8000/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    login: 'admin',
    password: 'Admin@123456'
  })
});

const loginData = await loginResponse.json();

if (loginData.success) {
  // Stocker le token
  localStorage.setItem('token', loginData.token);
  console.log('Connecté en tant que:', loginData.user.nom);
} else {
  console.error('Erreur:', loginData.error);
}

// 2. Utiliser le token pour les requêtes suivantes
const token = localStorage.getItem('token');

const patientsResponse = await fetch('http://localhost:8000/api/patients', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
  }
});

const patientsData = await patientsResponse.json();
console.log('Patients:', patientsData);
```

### Avec Axios

```javascript
import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000';

// Créer une instance Axios
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Intercepteur pour ajouter le token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Connexion
async function login(login, password) {
  try {
    const response = await api.post('/api/auth/login', { login, password });
    localStorage.setItem('token', response.data.token);
    return response.data;
  } catch (error) {
    console.error('Erreur de connexion:', error.response.data);
  }
}

// Récupérer les patients
async function getPatients() {
  try {
    const response = await api.get('/api/patients');
    return response.data;
  } catch (error) {
    console.error('Erreur:', error.response.data);
  }
}

// Utilisation
await login('admin', 'Admin@123456');
const patients = await getPatients();
```

### Avec Angular

```typescript
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  login(login: string, password: string) {
    return this.http.post(`${this.apiUrl}/auth/login`, { login, password });
  }

  getPatients() {
    const token = localStorage.getItem('token');
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${token}`
    });
    return this.http.get(`${this.apiUrl}/patients`, { headers });
  }
}
```

### Avec React

```javascript
import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000';

// Service API
const apiService = {
  login: async (login, password) => {
    const response = await axios.post(`${API_BASE_URL}/api/auth/login`, {
      login,
      password
    });
    localStorage.setItem('token', response.data.token);
    return response.data;
  },

  getPatients: async () => {
    const token = localStorage.getItem('token');
    const response = await axios.get(`${API_BASE_URL}/api/patients`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  }
};

// Composant
function LoginPage() {
  const handleLogin = async (e) => {
    e.preventDefault();
    try {
      const data = await apiService.login('admin', 'Admin@123456');
      console.log('Connecté:', data.user);
    } catch (error) {
      console.error('Erreur:', error.response.data);
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <button type="submit">Se connecter</button>
    </form>
  );
}
```

### Avec Vue.js

```javascript
import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000';

export default {
  data() {
    return {
      user: null,
      token: null
    };
  },
  methods: {
    async login(login, password) {
      try {
        const response = await axios.post(`${API_BASE_URL}/api/auth/login`, {
          login,
          password
        });
        this.token = response.data.token;
        this.user = response.data.user;
        localStorage.setItem('token', this.token);
      } catch (error) {
        console.error('Erreur:', error.response.data);
      }
    },
    async getPatients() {
      try {
        const response = await axios.get(`${API_BASE_URL}/api/patients`, {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        });
        return response.data;
      } catch (error) {
        console.error('Erreur:', error.response.data);
      }
    }
  }
};
```

---

## 📋 Autres Endpoints

### Vérifier le Token
```
GET /api/auth/verify
Headers: Authorization: Bearer {token}
```

### Déconnexion
```
POST /api/auth/logout
Headers: Authorization: Bearer {token}
```

---

## ⚙️ Configuration CORS

Le backend est configuré pour accepter les requêtes CORS de :
- `http://localhost:*` (tous les ports)
- `https://localhost:*` (tous les ports)

**Headers CORS autorisés :**
- `Content-Type`
- `Authorization`
- `X-Requested-With`
- `Accept`

**Méthodes autorisées :**
- GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS

---

## 🔑 Identifiants de Test

| Login | Mot de passe | Rôle |
|-------|--------------|------|
| admin | Admin@123456 | Administrateur |
| directeur | Directeur@123456 | Directeur |
| medecin | Medecin@123456 | Médecin |
| infirmier | Infirmier@123456 | Infirmier |
| pharmacien | Pharmacien@123456 | Pharmacien |
| laborantin | Laborantin@123456 | Laborantin |
| radiologue | Radiologue@123456 | Radiologue |
| comptable | Comptable@123456 | Comptable |
| rh | RH@123456 | Responsable RH |
| maintenance | Maintenance@123456 | Technicien Maintenance |
| receptionniste | Receptionniste@123456 | Réceptionniste |

---

## 🚀 Démarrage du Backend

```bash
# Démarrer le serveur Symfony
php -S localhost:8000 -t public

# Ou avec Symfony CLI
symfony serve
```

Le backend sera accessible à : `http://localhost:8000`
