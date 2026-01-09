<?php

namespace App\Controller\Api\GoogleAuth;

use App\Entity\Personnel\Utilisateurs;
use App\Service\GoogleAuthService;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur d'authentification Google OAuth2
 * 
 * Gère:
 * - Validation des tokens Google
 * - Vérification de l'existence de l'utilisateur dans la BDD
 * - Génération de tokens JWT pour les utilisateurs autorisés
 * - Redirection vers login si l'utilisateur n'existe pas
 * 
 * Tous les endpoints retournent du JSON
 */
#[Route('/api/auth/google', name: 'api_auth_google_')]
class GoogleAuthController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     * 
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param GoogleAuthService $googleAuthService Service Google Auth
     * @param JwtService $jwtService Service JWT pour générer les tokens
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GoogleAuthService $googleAuthService,
        private JwtService $jwtService,
    ) {
    }

    /**
     * Endpoint de connexion Google avec token ID
     * POST /api/auth/google/login
     * 
     * Body JSON:
     * {
     *   "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjEifQ..."
     * }
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "message": "Connexion Google réussie",
     *   "user": {
     *     "id": 1,
     *     "email": "user@example.com",
     *     "login": "user",
     *     "nom": "Dupont",
     *     "prenom": "Jean",
     *     "telephone": "+33612345678",
     *     "role": "Médecin",
     *     "profil": "Médecin",
     *     "specialite": "Cardiologie",
     *     "hopital": "Rehoboth Hospital",
     *     "photo": null
     *   },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     * }
     * 
     * Response (Erreur - 401):
     * {
     *   "success": false,
     *   "error": "Connexion impossible. Cet utilisateur n'existe pas dans notre système.",
     *   "redirect": "/login"
     * }
     * 
     * Response (Erreur - 400):
     * {
     *   "success": false,
     *   "error": "Le champ 'id_token' est requis"
     * }
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            // Log de la requête
            error_log("Google Login Request - Origin: " . $request->headers->get('Origin'));
            error_log("Google Login Request - Content: " . $request->getContent());

            // Récupérer et valider les données JSON
            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                return $this->json([
                    'success' => false,
                    'error' => 'Données JSON invalides',
                ], 400);
            }

            // Vérifier que le token ID est fourni
            if (!isset($data['id_token'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "id_token" est requis',
                ], 400);
            }

            $idToken = trim($data['id_token']);

            // Valider le token ID Google
            if (empty($idToken)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le token ID ne peut pas être vide',
                ], 400);
            }

            // Log du token
            error_log("Google Login - Token ID length: " . strlen($idToken));

            // Valider le token ID avec Google
            $googleUserData = $this->googleAuthService->validateIdToken($idToken);

            if (!$googleUserData) {
                error_log("Google Login - Token validation failed");
                return $this->json([
                    'success' => false,
                    'error' => 'Token Google invalide ou expiré',
                ], 401);
            }

            error_log("Google Login - Token validated successfully. Email: " . ($googleUserData['email'] ?? 'N/A'));

            // Vérifier que l'email est fourni
            if (!isset($googleUserData['email']) || empty($googleUserData['email'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Email non disponible depuis Google',
                ], 400);
            }

            $email = strtolower(trim($googleUserData['email']));

            error_log("Google Login - Searching for user with email: " . $email);

            // Chercher l'utilisateur dans la BDD par email (normalisé)
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)
                ->findOneBy(['email' => $email]);

            error_log("Google Login - User found: " . ($utilisateur ? "YES (ID: " . $utilisateur->getId() . ")" : "NO"));

            // Si l'utilisateur n'existe pas, rediriger vers login
            if (!$utilisateur) {
                error_log("Google Login - User not found in database. Email searched: " . $email);
                return $this->json([
                    'success' => false,
                    'error' => 'Connexion impossible. Cet utilisateur n\'existe pas dans notre système.',
                    'redirect' => '/login',
                ], 401);
            }

            // Vérifier que le compte n'est pas verrouillé
            if ($utilisateur->isCompteVerrouille()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Compte verrouillé. Contactez l\'administrateur.',
                    'redirect' => '/login',
                ], 403);
            }

            // Vérifier que l'utilisateur est actif
            if (!$utilisateur->isActif()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Compte désactivé. Contactez l\'administrateur.',
                    'redirect' => '/login',
                ], 403);
            }

            // Réinitialiser les tentatives échouées
            $utilisateur->setNombreTentativesConnexion(0);
            $utilisateur->setCompteVerrouille(false);

            // Mettre à jour la dernière connexion
            $utilisateur->setDerniereConnexion(new \DateTimeImmutable());
            $this->entityManager->flush();

            // Générer un token JWT
            $token = $this->generateToken($utilisateur);

            // Retourner les données de l'utilisateur
            return $this->json([
                'success' => true,
                'message' => 'Connexion Google réussie',
                'user' => [
                    'id' => $utilisateur->getId(),
                    'email' => $utilisateur->getEmail(),
                    'login' => $utilisateur->getLogin(),
                    'nom' => $utilisateur->getNom(),
                    'prenom' => $utilisateur->getPrenom(),
                    'telephone' => $utilisateur->getTelephone(),
                    'role' => $utilisateur->getRoleId()?->getNom(),
                    'profil' => $utilisateur->getProfilId()?->getNom(),
                    'specialite' => $utilisateur->getSpecialiteId()?->getNom(),
                    'hopital' => $utilisateur->getHopitalId()?->getNom(),
                    'photo' => $utilisateur->getPhotoProfil(),
                ],
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            error_log("Google Login Error: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint de connexion Google avec token d'accès
     * POST /api/auth/google/login-access-token
     * 
     * Body JSON:
     * {
     *   "access_token": "ya29.a0AfH6SMBx..."
     * }
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "message": "Connexion Google réussie",
     *   "user": { ... },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     * }
     * 
     * Response (Erreur - 401):
     * {
     *   "success": false,
     *   "error": "Connexion impossible. Cet utilisateur n'existe pas dans notre système.",
     *   "redirect": "/login"
     * }
     */
    #[Route('/login-access-token', name: 'login_access_token', methods: ['POST'])]
    public function loginWithAccessToken(Request $request): JsonResponse
    {
        try {
            // Récupérer et valider les données JSON
            $data = json_decode($request->getContent(), true);

            // Vérifier que le token d'accès est fourni
            if (!isset($data['access_token'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "access_token" est requis',
                ], 400);
            }

            $accessToken = trim($data['access_token']);

            // Valider le token d'accès Google
            if (empty($accessToken)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le token d\'accès ne peut pas être vide',
                ], 400);
            }

            // Récupérer les informations utilisateur depuis Google
            $googleUserData = $this->googleAuthService->getUserInfo($accessToken);

            if (!$googleUserData) {
                return $this->json([
                    'success' => false,
                    'error' => 'Token Google invalide ou expiré',
                ], 401);
            }

            // Vérifier que l'email est fourni
            if (!isset($googleUserData['email']) || empty($googleUserData['email'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Email non disponible depuis Google',
                ], 400);
            }

            $email = strtolower(trim($googleUserData['email']));

            error_log("Google Login (Access Token) - Searching for user with email: " . $email);

            // Chercher l'utilisateur dans la BDD par email (normalisé)
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)
                ->findOneBy(['email' => $email]);

            error_log("Google Login (Access Token) - User found: " . ($utilisateur ? "YES (ID: " . $utilisateur->getId() . ")" : "NO"));

            // Si l'utilisateur n'existe pas, rediriger vers login
            if (!$utilisateur) {
                error_log("Google Login (Access Token) - User not found in database. Email searched: " . $email);
                return $this->json([
                    'success' => false,
                    'error' => 'Connexion impossible. Cet utilisateur n\'existe pas dans notre système.',
                    'redirect' => '/login',
                ], 401);
            }

            // Vérifier que le compte n'est pas verrouillé
            if ($utilisateur->isCompteVerrouille()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Compte verrouillé. Contactez l\'administrateur.',
                    'redirect' => '/login',
                ], 403);
            }

            // Vérifier que l'utilisateur est actif
            if (!$utilisateur->isActif()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Compte désactivé. Contactez l\'administrateur.',
                    'redirect' => '/login',
                ], 403);
            }

            // Réinitialiser les tentatives échouées
            $utilisateur->setNombreTentativesConnexion(0);
            $utilisateur->setCompteVerrouille(false);

            // Mettre à jour la dernière connexion
            $utilisateur->setDerniereConnexion(new \DateTimeImmutable());
            $this->entityManager->flush();

            // Générer un token JWT
            $token = $this->generateToken($utilisateur);

            // Retourner les données de l'utilisateur
            return $this->json([
                'success' => true,
                'message' => 'Connexion Google réussie',
                'user' => [
                    'id' => $utilisateur->getId(),
                    'email' => $utilisateur->getEmail(),
                    'login' => $utilisateur->getLogin(),
                    'nom' => $utilisateur->getNom(),
                    'prenom' => $utilisateur->getPrenom(),
                    'telephone' => $utilisateur->getTelephone(),
                    'role' => $utilisateur->getRoleId()?->getNom(),
                    'profil' => $utilisateur->getProfilId()?->getNom(),
                    'specialite' => $utilisateur->getSpecialiteId()?->getNom(),
                    'hopital' => $utilisateur->getHopitalId()?->getNom(),
                    'photo' => $utilisateur->getPhotoProfil(),
                ],
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            error_log("Google Login with Access Token Error: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint pour obtenir l'URL d'authentification Google
     * GET /api/auth/google/auth-url
     * 
     * Query Parameters:
     * - redirect_uri: URI de redirection après authentification (optionnel)
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "auth_url": "https://accounts.google.com/o/oauth2/auth?..."
     * }
     * 
     * Response (Erreur - 400):
     * {
     *   "success": false,
     *   "error": "Erreur lors de la génération de l'URL d'authentification"
     * }
     */
    #[Route('/auth-url', name: 'auth_url', methods: ['GET'])]
    public function getAuthUrl(Request $request): JsonResponse
    {
        try {
            // Récupérer l'URI de redirection depuis les paramètres de requête
            $redirectUri = $request->query->get('redirect_uri', 'http://localhost:4200/dashboard');

            // Générer l'URL d'authentification Google
            $authUrl = $this->googleAuthService->getAuthUrl($redirectUri);

            return $this->json([
                'success' => true,
                'auth_url' => $authUrl,
            ], 200);

        } catch (\Exception $e) {
            error_log("Google Auth URL Error: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Erreur lors de la génération de l\'URL d\'authentification',
            ], 500);
        }
    }

    /**
     * Endpoint pour échanger un code d'autorisation contre un token
     * POST /api/auth/google/exchange-code
     * 
     * Body JSON:
     * {
     *   "code": "4/0AX4XfWh...",
     *   "redirect_uri": "http://localhost/dashboard"
     * }
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "message": "Code échangé avec succès",
     *   "tokens": {
     *     "access_token": "ya29.a0AfH6SMBx...",
     *     "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjEifQ...",
     *     "expires_in": 3599,
     *     "token_type": "Bearer"
     *   }
     * }
     * 
     * Response (Erreur - 400):
     * {
     *   "success": false,
     *   "error": "Le champ 'code' est requis"
     * }
     */
    #[Route('/exchange-code', name: 'exchange_code', methods: ['POST'])]
    public function exchangeCode(Request $request): JsonResponse
    {
        try {
            // Récupérer et valider les données JSON
            $data = json_decode($request->getContent(), true);

            // Vérifier que le code est fourni
            if (!isset($data['code'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "code" est requis',
                ], 400);
            }

            $code = trim($data['code']);
            $redirectUri = $data['redirect_uri'] ?? 'http://localhost:4200/dashboard';

            // Valider le code
            if (empty($code)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le code ne peut pas être vide',
                ], 400);
            }

            // Échanger le code contre un token
            $tokens = $this->googleAuthService->exchangeCodeForToken($code);

            if (!$tokens) {
                return $this->json([
                    'success' => false,
                    'error' => 'Impossible d\'échanger le code contre un token',
                ], 401);
            }

            return $this->json([
                'success' => true,
                'message' => 'Code échangé avec succès',
                'tokens' => $tokens,
            ], 200);

        } catch (\Exception $e) {
            error_log("Google Exchange Code Error: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Génère un token JWT sécurisé pour un utilisateur
     * 
     * @param Utilisateurs $utilisateur Utilisateur authentifié
     * @return string Token JWT encodé
     */
    private function generateToken(Utilisateurs $utilisateur): string
    {
        return $this->jwtService->generateToken(
            userId: $utilisateur->getId(),
            email: $utilisateur->getEmail(),
            login: $utilisateur->getLogin(),
            roleId: $utilisateur->getRoleId()->getId(),
            roleName: $utilisateur->getRoleId()->getNom(),
            profilId: $utilisateur->getProfilId()->getId(),
            profilName: $utilisateur->getProfilId()->getNom()
        );
    }
}
