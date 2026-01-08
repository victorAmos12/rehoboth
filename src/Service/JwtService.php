<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\InvalidArgumentException;

/**
 * Service JWT pour la gestion des tokens d'authentification
 * 
 * Ce service utilise la librairie Firebase/PHP-JWT pour:
 * - Générer des tokens JWT sécurisés
 * - Valider et décoder les tokens
 * - Gérer l'expiration des tokens
 * 
 * Structure du token JWT:
 * - Header: Contient le type (JWT) et l'algorithme (HS256)
 * - Payload: Contient les données utilisateur et les claims
 * - Signature: Garantit l'intégrité du token
 * 
 * Format: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MSwiaWF0IjoxNjc4OTAxMjAwfQ.signature
 */
class JwtService
{
    /**
     * Clé secrète pour signer les tokens
     * ⚠️ À stocker dans les variables d'environnement en production
     */
    private string $secretKey;

    /**
     * Algorithme de signature utilisé
     * HS256 = HMAC avec SHA-256 (symétrique)
     */
    private string $algorithm = 'HS256';

    /**
     * Durée de vie du token en secondes
     * Par défaut: 15 minutes = 900 secondes
     * ⚠️ Token court pour la sécurité - utilisé pour les requêtes API
     */
    private int $expirationTime = 900;

    /**
     * Durée de vie du refresh token en secondes
     * Par défaut: 7 jours = 604800 secondes
     * ⚠️ Token long - utilisé pour renouveler l'accès sans se reconnecter
     */
    private int $refreshTokenExpirationTime = 604800;

    /**
     * Durée d'inactivité avant expiration du token en secondes
     * Par défaut: 15 minutes = 900 secondes
     * ⚠️ Si l'utilisateur n'a pas d'activité pendant ce temps, le token expire
     */
    private int $inactivityTimeout = 900;

    /**
     * Constructeur du service JWT
     * 
     * @param string $secretKey Clé secrète pour signer les tokens (base64 encodée ou brute)
     * @param int $expirationTime Durée de vie du token en secondes (optionnel, défaut: 15 min)
     * @param int $inactivityTimeout Durée d'inactivité avant expiration (optionnel, défaut: 15 min)
     */
    public function __construct(string $secretKey, int $expirationTime = 900, int $inactivityTimeout = 900)
    {
        if ($secretKey === '') {
            throw new \RuntimeException('JWT secret vide');
        }
        
        // Décoder la clé secrète si elle est en base64
        // La clé stockée dans .env est généralement base64 encodée pour plus de sécurité
        $decodedKey = base64_decode($secretKey, true);
        
        // Si la clé n'est pas valide base64, utiliser la clé telle quelle
        if ($decodedKey === false) {
            $this->secretKey = $secretKey;
        } else {
            // Utiliser la clé décodée
            $this->secretKey = $decodedKey;
        }
        
        $this->expirationTime = $expirationTime;
        $this->inactivityTimeout = $inactivityTimeout;
    }

    /**
     * Génère un token JWT pour un utilisateur
     * 
     * Payload du token:
     * - id: ID de l'utilisateur
     * - email: Email de l'utilisateur
     * - login: Login de l'utilisateur
     * - roleId: ID du rôle
     * - roleName: Nom du rôle
     * - profilId: ID du profil
     * - profilName: Nom du profil
     * - iat: Timestamp d'émission (issued at)
     * - exp: Timestamp d'expiration (expiration time)
     * - nbf: Timestamp avant lequel le token n'est pas valide (not before)
     * - jti: Identifiant unique du token (JWT ID)
     * - last_activity: Timestamp de la dernière activité (pour inactivité)
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $login Login de l'utilisateur
     * @param int $roleId ID du rôle
     * @param string $roleName Nom du rôle
     * @param int $profilId ID du profil
     * @param string $profilName Nom du profil
     * @return string Token JWT encodé
     * 
     * @example
     * $token = $jwtService->generateToken(
     *     userId: 1,
     *     email: 'admin@rehoboth.com',
     *     login: 'admin',
     *     roleId: 1,
     *     roleName: 'Administrateur',
     *     profilId: 1,
     *     profilName: 'Admin'
     * );
     */
    public function generateToken(
        int $userId,
        string $email,
        string $login,
        int $roleId,
        string $roleName,
        int $profilId,
        string $profilName
    ): string {
        // Timestamp actuel
        $now = time();

        // Payload du token
        $payload = [
            // Claims standards (RFC 7519)
            'iat' => $now,                                    // Issued at
            'exp' => $now + $this->expirationTime,           // Expiration time
            'nbf' => $now,                                    // Not before
            'jti' => bin2hex(random_bytes(16)),              // JWT ID (identifiant unique)

            // Claims personnalisés
            'id' => $userId,                                  // ID utilisateur
            'email' => $email,                                // Email
            'login' => $login,                                // Login
            'roleId' => $roleId,                              // ID du rôle
            'roleName' => $roleName,                          // Nom du rôle
            'profilId' => $profilId,                          // ID du profil
            'profilName' => $profilName,                      // Nom du profil
            'last_activity' => $now,                          // Timestamp de la dernière activité
        ];

        // Encoder et signer le token
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Génère un refresh token pour renouveler l'accès
     * 
     * Le refresh token a une durée de vie plus longue que le token d'accès
     * et est utilisé pour obtenir un nouveau token sans se reconnecter
     * 
     * @param int $userId ID de l'utilisateur
     * @return string Refresh token JWT encodé
     */
    public function generateRefreshToken(int $userId): string
    {
        $now = time();

        $payload = [
            'iat' => $now,
            'exp' => $now + $this->refreshTokenExpirationTime,
            'nbf' => $now,
            'jti' => bin2hex(random_bytes(16)),
            'id' => $userId,
            'type' => 'refresh',  // Type de token pour différencier
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Valide et décode un token JWT
     * 
     * Vérifie:
     * - La signature du token
     * - L'expiration du token
     * - L'inactivité du token (15 min par défaut)
     * - La validité du format
     * 
     * @param string $token Token JWT à valider
     * @param bool $checkInactivity Si true, vérifie l'inactivité (défaut: true)
     * @return object|null Payload du token si valide, null sinon
     * 
     * @throws ExpiredException Si le token a expiré ou inactif
     * @throws SignatureInvalidException Si la signature est invalide
     * @throws BeforeValidException Si le token n'est pas encore valide
     * @throws InvalidArgumentException Si le token est mal formé
     * 
     * @example
     * try {
     *     $payload = $jwtService->validateToken($token);
     *     $userId = $payload->id;
     *     $userRole = $payload->roleName;
     * } catch (ExpiredException $e) {
     *     // Token expiré ou inactif
     * } catch (SignatureInvalidException $e) {
     *     // Signature invalide
     * }
     */
    public function validateToken(string $token, bool $checkInactivity = true): ?object
    {
        try {
            // Décoder et valider le token
            // La clé est encapsulée dans un objet Key
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

            // Vérifier l'inactivité si demandé
            if ($checkInactivity && isset($decoded->last_activity)) {
                $now = time();
                $lastActivity = $decoded->last_activity;
                $inactivityDuration = $now - $lastActivity;

                // Si l'inactivité dépasse le timeout, le token est expiré
                if ($inactivityDuration > $this->inactivityTimeout) {
                    throw new ExpiredException('Token expiré par inactivité (15 minutes)', 0);
                }
            }

            return $decoded;
        } catch (ExpiredException $e) {
            // Token expiré
            throw new ExpiredException($e->getMessage() ?: 'Token expiré', 0, $e);
        } catch (SignatureInvalidException $e) {
            // Signature invalide
            throw new SignatureInvalidException('Signature du token invalide', 0, $e);
        } catch (BeforeValidException $e) {
            // Token pas encore valide
            throw new BeforeValidException('Token pas encore valide', 0, $e);
        } catch (InvalidArgumentException $e) {
            // Token mal formé
            throw new InvalidArgumentException('Token mal formé', 0, $e);
        } catch (\Exception $e) {
            // Erreur générale
            throw new \Exception('Erreur lors de la validation du token: ' . $e->getMessage());
        }
    }

    /**
     * Renouvelle le timestamp d'activité du token
     * 
     * Cette méthode met à jour le timestamp de dernière activité dans le token
     * pour éviter l'expiration par inactivité
     * 
     * @param string $token Token JWT actuel
     * @return string Nouveau token avec last_activity mis à jour
     * 
     * @throws \Exception Si le token est invalide
     */
    public function refreshTokenActivity(string $token): string
    {
        try {
            // Valider le token sans vérifier l'inactivité
            $payload = $this->validateToken($token, false);

            // Mettre à jour le timestamp d'activité
            $payload->last_activity = time();

            // Régénérer le token avec le nouveau timestamp
            return JWT::encode((array)$payload, $this->secretKey, $this->algorithm);
        } catch (\Exception $e) {
            throw new \Exception('Impossible de renouveler le token: ' . $e->getMessage());
        }
    }

    /**
     * Extrait le token du header Authorization
     * 
     * Format attendu: "Bearer {token}"
     * 
     * @param string $authHeader Valeur du header Authorization
     * @return string|null Token extrait ou null si format invalide
     * 
     * @example
     * $token = $jwtService->extractTokenFromHeader('Bearer eyJhbGc...');
     * // Retourne: 'eyJhbGc...'
     */
    public function extractTokenFromHeader(string $authHeader): ?string
    {
        // Vérifier que le header commence par "Bearer "
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        // Extraire le token après "Bearer "
        return substr($authHeader, 7);
    }

    /**
     * Vérifie si un token a expiré
     * 
     * @param string $token Token JWT
     * @return bool true si expiré, false sinon
     */
    public function isTokenExpired(string $token): bool
    {
        try {
            $payload = $this->validateToken($token);
            return false;
        } catch (ExpiredException $e) {
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Obtient le temps restant avant expiration du token (en secondes)
     * 
     * @param string $token Token JWT
     * @return int|null Secondes restantes ou null si token invalide
     */
    public function getTimeToExpiration(string $token): ?int
    {
        try {
            $payload = $this->validateToken($token, false);
            $now = time();
            $timeToExpiration = $payload->exp - $now;

            return $timeToExpiration > 0 ? $timeToExpiration : 0;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtient le temps restant avant expiration par inactivité (en secondes)
     * 
     * @param string $token Token JWT
     * @return int|null Secondes restantes avant inactivité ou null si token invalide
     */
    public function getTimeToInactivityExpiration(string $token): ?int
    {
        try {
            $payload = $this->validateToken($token, false);
            
            if (!isset($payload->last_activity)) {
                return null;
            }

            $now = time();
            $lastActivity = $payload->last_activity;
            $inactivityDuration = $now - $lastActivity;
            $timeToInactivityExpiration = $this->inactivityTimeout - $inactivityDuration;

            return $timeToInactivityExpiration > 0 ? $timeToInactivityExpiration : 0;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtient les informations du token sans valider la signature
     * 
     * ⚠️ À utiliser avec prudence - ne valide pas la signature!
     * Utile pour obtenir des infos avant validation complète
     * 
     * @param string $token Token JWT
     * @return object|null Payload du token ou null si invalide
     */
    public function getTokenPayloadWithoutValidation(string $token): ?object
    {
        try {
            // Diviser le token en 3 parties: header.payload.signature
            $parts = explode('.', $token);

            if (count($parts) !== 3) {
                return null;
            }

            // Décoder le payload (partie 2)
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')));

            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Définit la durée de vie du token
     * 
     * @param int $seconds Durée en secondes
     */
    public function setExpirationTime(int $seconds): void
    {
        $this->expirationTime = $seconds;
    }

    /**
     * Obtient la durée de vie du token
     * 
     * @return int Durée en secondes
     */
    public function getExpirationTime(): int
    {
        return $this->expirationTime;
    }

    /**
     * Définit le timeout d'inactivité
     * 
     * @param int $seconds Durée en secondes
     */
    public function setInactivityTimeout(int $seconds): void
    {
        $this->inactivityTimeout = $seconds;
    }

    /**
     * Obtient le timeout d'inactivité
     * 
     * @return int Durée en secondes
     */
    public function getInactivityTimeout(): int
    {
        return $this->inactivityTimeout;
    }
}
