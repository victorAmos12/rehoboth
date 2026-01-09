<?php

namespace App\Service;

use Google_Client;
use Google_Exception;

/**
 * Service pour gérer l'authentification Google OAuth2
 * 
 * Ce service utilise la librairie Google API Client pour:
 * - Valider les tokens ID Google
 * - Récupérer les informations utilisateur depuis Google
 * - Gérer les erreurs d'authentification
 */
class GoogleAuthService
{
    /**
     * Client Google OAuth2
     */
    private Google_Client $googleClient;

    /**
     * Constructeur du service Google Auth
     * 
     * @param string $clientSecretPath Chemin vers le fichier client_secret.json
     * @throws Google_Exception Si le fichier n'existe pas ou est invalide
     */
    public function __construct(string $clientSecretPath)
    {
        if (!file_exists($clientSecretPath)) {
            throw new \RuntimeException("Fichier client_secret.json non trouvé: {$clientSecretPath}");
        }

        $this->googleClient = new Google_Client();
        $this->googleClient->setAuthConfig($clientSecretPath);
        $this->googleClient->setScopes(['email', 'profile']);
    }

    /**
     * Valide un token ID Google et retourne les informations utilisateur
     * 
     * @param string $idToken Token ID reçu du client Google
     * @return array|null Données utilisateur si valide, null sinon
     *                    Contient: id, email, name, picture, given_name, family_name
     * 
     * @example
     * $userData = $googleAuthService->validateIdToken($idToken);
     * if ($userData) {
     *     $email = $userData['email'];
     *     $name = $userData['name'];
     * }
     */
    public function validateIdToken(string $idToken): ?array
    {
        try {
            error_log("Google Auth - Validating ID Token");
            
            // Valider le token ID
            $payload = $this->googleClient->verifyIdToken($idToken);

            if ($payload) {
                error_log("Google Auth - ID Token validated successfully");
                error_log("Google Auth - Token payload: " . json_encode([
                    'sub' => $payload['sub'] ?? null,
                    'email' => $payload['email'] ?? null,
                    'email_verified' => $payload['email_verified'] ?? false,
                ]));

                return [
                    'id' => $payload['sub'] ?? null,
                    'email' => $payload['email'] ?? null,
                    'name' => $payload['name'] ?? null,
                    'picture' => $payload['picture'] ?? null,
                    'given_name' => $payload['given_name'] ?? null,
                    'family_name' => $payload['family_name'] ?? null,
                    'email_verified' => $payload['email_verified'] ?? false,
                ];
            }

            error_log("Google Auth - ID Token validation returned null");
            return null;
        } catch (\Exception $e) {
            error_log("Google ID Token Validation Error: " . $e->getMessage());
            error_log("Google ID Token Validation Error Stack: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Valide un token d'accès Google
     * 
     * @param string $accessToken Token d'accès reçu du client Google
     * @return array|null Données utilisateur si valide, null sinon
     * 
     * @example
     * $userData = $googleAuthService->validateAccessToken($accessToken);
     */
    public function validateAccessToken(string $accessToken): ?array
    {
        try {
            // Utiliser l'API tokeninfo de Google pour valider le token
            $url = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' . urlencode($accessToken);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            // Vérifier que le token est valide et n'a pas expiré
            if (isset($data['error'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            error_log("Google Access Token Validation Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les informations utilisateur depuis Google
     * 
     * @param string $accessToken Token d'accès Google
     * @return array|null Données utilisateur si succès, null sinon
     * 
     * @example
     * $userData = $googleAuthService->getUserInfo($accessToken);
     * if ($userData) {
     *     $email = $userData['email'];
     *     $name = $userData['name'];
     * }
     */
    public function getUserInfo(string $accessToken): ?array
    {
        try {
            $this->googleClient->setAccessToken(['access_token' => $accessToken]);

            // Utiliser le service Google+ pour récupérer les infos utilisateur
            $url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . urlencode($accessToken);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);

            if (isset($data['error'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            error_log("Google User Info Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtient l'URL de redirection pour l'authentification Google
     * 
     * @param string $redirectUri URI de redirection après authentification
     * @return string URL d'authentification Google
     * 
     * @example
     * $authUrl = $googleAuthService->getAuthUrl('http://localhost/callback');
     * // Rediriger l'utilisateur vers $authUrl
     */
    public function getAuthUrl(string $redirectUri): string
    {
        $this->googleClient->setRedirectUri($redirectUri);
        return $this->googleClient->createAuthUrl();
    }

    /**
     * Échange un code d'autorisation contre un token d'accès
     * 
     * @param string $code Code d'autorisation reçu de Google
     * @return array|null Token d'accès et informations si succès, null sinon
     * 
     * @example
     * $tokens = $googleAuthService->exchangeCodeForToken($code);
     * if ($tokens) {
     *     $accessToken = $tokens['access_token'];
     *     $idToken = $tokens['id_token'] ?? null;
     * }
     */
    public function exchangeCodeForToken(string $code): ?array
    {
        try {
            $tokens = $this->googleClient->fetchAccessTokenWithAuthCode($code);

            if (isset($tokens['error'])) {
                error_log("Google Token Exchange Error: " . $tokens['error']);
                return null;
            }

            return $tokens;
        } catch (\Exception $e) {
            error_log("Google Token Exchange Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifie si un email est autorisé (optionnel)
     * 
     * Peut être utilisé pour restreindre l'accès à certains domaines
     * 
     * @param string $email Email à vérifier
     * @param array $allowedDomains Domaines autorisés (ex: ['example.com'])
     * @return bool true si autorisé, false sinon
     * 
     * @example
     * $isAllowed = $googleAuthService->isEmailAllowed('user@example.com', ['example.com']);
     */
    public function isEmailAllowed(string $email, array $allowedDomains = []): bool
    {
        if (empty($allowedDomains)) {
            return true;
        }

        $emailDomain = substr(strrchr($email, "@"), 1);
        return in_array($emailDomain, $allowedDomains);
    }
}
