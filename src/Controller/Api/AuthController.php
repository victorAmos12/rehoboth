<?php

namespace App\Controller\Api;

use App\Entity\Personnel\Utilisateurs;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use OTPHP\TOTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur d'authentification API
 * 
 * Gère:
 * - Login avec génération de token JWT
 * - Vérification du token JWT
 * - Logout
 * 
 * Tous les endpoints retournent du JSON
 */
#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    /**
     * Constructeur avec injection de dépendances
     * 
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param UserPasswordHasherInterface $passwordHasher Hasher de mots de passe
     * @param JwtService $jwtService Service JWT pour générer/valider les tokens
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private JwtService $jwtService,
    ) {
    }

    /**
     * Endpoint de connexion
     * POST /api/auth/login
     * 
     * Body JSON (Option 1 - avec login):
     * {
     *   "login": "admin",
     *   "password": "Admin@123456"
     * }
     * 
     * Body JSON (Option 2 - avec email):
     * {
     *   "email": "admin@rehoboth.com",
     *   "password": "Admin@123456"
     * }
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "message": "Connexion réussie",
     *   "user": {
     *     "id": 1,
     *     "email": "admin@rehoboth.com",
     *     "login": "admin",
     *     "nom": "Dupont",
     *     "prenom": "Jean",
     *     "telephone": "+33612345678",
     *     "role": "Administrateur",
     *     "profil": "Administrateur",
     *     "specialite": null,
     *     "hopital": "Rehoboth Hospital",
     *     "photo": null
     *   },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     * }
     * 
     * Response (Erreur - 401):
     * {
     *   "success": false,
     *   "error": "Identifiants invalides"
     * }
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            // Récupérer et valider les données JSON
            $data = json_decode($request->getContent(), true);

            // Vérifier que le mot de passe est fourni
            if (!isset($data['password'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le champ "password" est requis',
                ], 400);
            }

            // Vérifier que login OU email est fourni
            if (!isset($data['login']) && !isset($data['email'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs "login" ou "email" sont requis',
                ], 400);
            }

            $password = $data['password'];
            $login = isset($data['login']) ? trim($data['login']) : null;
            $email = isset($data['email']) ? trim($data['email']) : null;

            // Valider les entrées
            if (empty($password)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le mot de passe ne peut pas être vide',
                ], 400);
            }

            if (empty($login) && empty($email)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le login ou l\'email ne peut pas être vide',
                ], 400);
            }

            // Chercher l'utilisateur par email ou login
            $queryBuilder = $this->entityManager->getRepository(Utilisateurs::class)
                ->createQueryBuilder('u');

            if ($login && $email) {
                // Si les deux sont fournis, chercher l'un ou l'autre
                $utilisateur = $queryBuilder
                    ->where('u.email = :email OR u.login = :login')
                    ->setParameter('email', $email)
                    ->setParameter('login', $login)
                    ->getQuery()
                    ->getOneOrNullResult();
            } elseif ($email) {
                // Chercher par email
                $utilisateur = $queryBuilder
                    ->where('u.email = :email')
                    ->setParameter('email', $email)
                    ->getQuery()
                    ->getOneOrNullResult();
            } else {
                // Chercher par login
                $utilisateur = $queryBuilder
                    ->where('u.login = :login')
                    ->setParameter('login', $login)
                    ->getQuery()
                    ->getOneOrNullResult();
            }

            // Vérifier que l'utilisateur existe
            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Identifiants invalides',
                ], 401);
            }

            // Vérifier que le compte n'est pas verrouillé
            if ($utilisateur->isCompteVerrouille()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Compte verrouillé. Contactez l\'administrateur.',
                ], 403);
            }

            // Vérifier que l'utilisateur est actif
            if (!$utilisateur->isActif()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Compte désactivé. Contactez l\'administrateur.',
                ], 403);
            }

            // Vérifier le mot de passe
            if (!$this->passwordHasher->isPasswordValid($utilisateur, $password)) {
                // Incrémenter les tentatives échouées
                $tentatives = ($utilisateur->getNombreTentativesConnexion() ?? 0) + 1;
                $utilisateur->setNombreTentativesConnexion($tentatives);
                
                // Verrouiller le compte après 5 tentatives
                if ($tentatives >= 5) {
                    $utilisateur->setCompteVerrouille(true);
                }
                
                $this->entityManager->flush();

                return $this->json([
                    'success' => false,
                    'error' => 'Identifiants invalides',
                ], 401);
            }

            // Réinitialiser les tentatives échouées
            $utilisateur->setNombreTentativesConnexion(0);
            $utilisateur->setCompteVerrouille(false);

            // Vérifier si la 2FA est activée
            if ($utilisateur->getAuthentification2fa()) {
                // Retourner une réponse indiquant que la 2FA est requise
                return $this->json([
                    'success' => true,
                    'message' => 'Authentification 2FA requise',
                    'requires_2fa' => true,
                    'user_id' => $utilisateur->getId(),
                    'user' => [
                        'id' => $utilisateur->getId(),
                        'email' => $utilisateur->getEmail(),
                        'login' => $utilisateur->getLogin(),
                        'nom' => $utilisateur->getNom(),
                        'prenom' => $utilisateur->getPrenom(),
                    ],
                ], 200);
            }

            // Mettre à jour la dernière connexion
            $utilisateur->setDerniereConnexion(new \DateTimeImmutable());
            $this->entityManager->flush();

            // Générer un token JWT
            $token = $this->generateToken($utilisateur);

            // Retourner les données de l'utilisateur
            return $this->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'requires_2fa' => false,
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
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint pour vérifier le token
     * GET /api/auth/verify
     * Header: Authorization: Bearer {token}
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "user": {
     *     "id": 1,
     *     "email": "admin@rehoboth.com",
     *     "nom": "Dupont",
     *     "prenom": "Jean",
     *     "role": "Administrateur"
     *   },
     *   "token_expires_in": 900,
     *   "inactivity_expires_in": 900
     * }
     * 
     * Response (Erreur - 401):
     * {
     *   "success": false,
     *   "error": "Token expiré par inactivité (15 minutes)"
     * }
     */
    #[Route('/verify', name: 'verify', methods: ['GET'])]
    public function verify(Request $request): JsonResponse
    {
        try {
            // Récupérer le token du header
            $authHeader = $request->headers->get('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json([
                    'success' => false,
                    'error' => 'Token manquant ou invalide',
                ], 401);
            }

            $token = substr($authHeader, 7);

            // Vérifier le token (avec vérification d'inactivité)
            $utilisateurId = $this->verifyToken($token);

            if (!$utilisateurId) {
                return $this->json([
                    'success' => false,
                    'error' => 'Token invalide ou expiré',
                ], 401);
            }

            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)
                ->find($utilisateurId);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            // Obtenir les temps d'expiration
            $tokenExpiresIn = $this->jwtService->getTimeToExpiration($token);
            $inactivityExpiresIn = $this->jwtService->getTimeToInactivityExpiration($token);

            return $this->json([
                'success' => true,
                'user' => [
                    'id' => $utilisateur->getId(),
                    'email' => $utilisateur->getEmail(),
                    'nom' => $utilisateur->getNom(),
                    'prenom' => $utilisateur->getPrenom(),
                    'role' => $utilisateur->getRoleId()?->getNom(),
                ],
                'token_expires_in' => $tokenExpiresIn,
                'inactivity_expires_in' => $inactivityExpiresIn,
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint pour renouveler l'activité du token
     * POST /api/auth/refresh-activity
     * Header: Authorization: Bearer {token}
     * 
     * Cette endpoint renouvelle le timestamp d'activité du token
     * pour éviter l'expiration par inactivité (15 minutes)
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "message": "Activité renouvelée",
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     * }
     * 
     * Response (Erreur - 401):
     * {
     *   "success": false,
     *   "error": "Token expiré par inactivité (15 minutes)"
     * }
     */
    #[Route('/refresh-activity', name: 'refresh_activity', methods: ['POST'])]
    public function refreshActivity(Request $request): JsonResponse
    {
        try {
            // Récupérer le token du header
            $authHeader = $request->headers->get('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->json([
                    'success' => false,
                    'error' => 'Token manquant ou invalide',
                ], 401);
            }

            $token = substr($authHeader, 7);

            // Vérifier le token d'abord
            $utilisateurId = $this->verifyToken($token);

            if (!$utilisateurId) {
                return $this->json([
                    'success' => false,
                    'error' => 'Token invalide ou expiré',
                ], 401);
            }

            // Renouveler l'activité du token
            $newToken = $this->jwtService->refreshTokenActivity($token);

            return $this->json([
                'success' => true,
                'message' => 'Activité renouvelée',
                'token' => $newToken,
            ], 200);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Endpoint pour vérifier le code 2FA lors du login
     * POST /api/auth/verify-2fa
     * 
     * Body JSON:
     * {
     *   "user_id": 1,
     *   "code": "123456"
     * }
     * 
     * Response (Succès - 200):
     * {
     *   "success": true,
     *   "message": "Code 2FA valide",
     *   "user": {
     *     "id": 1,
     *     "email": "admin@rehoboth.com",
     *     "login": "admin",
     *     "nom": "Dupont",
     *     "prenom": "Jean",
     *     "telephone": "+33612345678",
     *     "role": "Administrateur",
     *     "profil": "Administrateur",
     *     "specialite": null,
     *     "hopital": "Rehoboth Hospital",
     *     "photo": null
     *   },
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     * }
     * 
     * Response (Erreur - 401):
     * {
     *   "success": false,
     *   "error": "Code 2FA invalide"
     * }
     */
    #[Route('/verify-2fa', name: 'verify_2fa_login', methods: ['POST'])]
    public function verify2FALogin(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Vérifier les champs requis
            if (!isset($data['user_id']) || !isset($data['code'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Les champs "user_id" et "code" sont requis',
                ], 400);
            }

            $userId = (int)$data['user_id'];
            $code = trim($data['code']);

            // Récupérer l'utilisateur
            $utilisateur = $this->entityManager->getRepository(Utilisateurs::class)
                ->find($userId);

            if (!$utilisateur) {
                return $this->json([
                    'success' => false,
                    'error' => 'Utilisateur non trouvé',
                ], 404);
            }

            // Vérifier que la 2FA est activée
            if (!$utilisateur->getAuthentification2fa()) {
                return $this->json([
                    'success' => false,
                    'error' => 'La 2FA n\'est pas activée pour cet utilisateur',
                ], 400);
            }

            // Valider le code TOTP
            if (!preg_match('/^\d{6}$/', $code)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Le code doit être composé de 6 chiffres',
                ], 400);
            }

            // Vérifier le code TOTP
            if (!$this->verify2FACode($utilisateur->getSecret2fa(), $code)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Code 2FA invalide',
                ], 401);
            }

            // Mettre à jour la dernière connexion
            $utilisateur->setDerniereConnexion(new \DateTimeImmutable());
            $this->entityManager->flush();

            // Générer un token JWT
            $token = $this->generateToken($utilisateur);

            // Retourner les données de l'utilisateur
            return $this->json([
                'success' => true,
                'message' => 'Code 2FA valide',
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
            return $this->json([
                'success' => false,
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint de déconnexion
     * POST /api/auth/logout
     */
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ], 200);
    }

    /**
     * Génère un token JWT sécurisé avec Firebase/PHP-JWT
     * 
     * Le token contient:
     * - ID utilisateur
     * - Email
     * - Login
     * - Rôle et profil
     * - Timestamps d'émission et d'expiration
     * - Identifiant unique (jti)
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

    /**
     * Vérifie et décode un token JWT
     * 
     * Valide:
     * - La signature du token
     * - L'expiration du token
     * - Le format du token
     * 
     * @param string $token Token JWT à vérifier
     * @return int|null ID utilisateur si valide, null sinon
     */
    private function verifyToken(string $token): ?int
    {
        try {
            // Valider le token avec le service JWT
            $payload = $this->jwtService->validateToken($token);

            // Retourner l'ID utilisateur
            return $payload->id ?? null;
        } catch (ExpiredException $e) {
            // Token expiré
            return null;
        } catch (SignatureInvalidException $e) {
            // Signature invalide
            return null;
        } catch (\Exception $e) {
            // Erreur générale
            return null;
        }
    }

    /**
     * Vérifie un code TOTP 2FA
     * 
     * Utilise la librairie spomky-labs/otphp pour valider les codes TOTP
     * conformément à la RFC 6238.
     * 
     * @param string $secret Secret TOTP de l'utilisateur (en base32)
     * @param string $code Code TOTP à vérifier (6 chiffres)
     * @return bool True si le code est valide, false sinon
     * 
     * Remarques:
     * - La vérification inclut une tolérance de ±1 time window (±30 secondes)
     * - Les serveurs et clients doivent être synchronisés à ±30 secondes près
     */
    private function verify2FACode(string $secret, string $code): bool
    {
        try {
            // Créer une instance TOTP avec le secret
            $totp = \OTPHP\TOTP::create($secret);
            
            // Vérifier le code
            // La méthode verify() inclut automatiquement une tolérance de ±1 time window
            // C'est-à-dire que les codes de -30s, 0s et +30s sont acceptés
            $result = $totp->verify($code);
            
            // Logging pour débogage
            error_log("TOTP Verification:");
            error_log("  Secret: " . substr($secret, 0, 4) . "...");
            error_log("  Code provided: " . $code);
            error_log("  Verification result: " . ($result ? "VALID" : "INVALID"));
            
            // Si le code est invalide, on essaie aussi une vérification basique
            if (!$result) {
                // Générer le code actuel pour voir la différence
                $currentCode = $totp->now();
                error_log("  Current code on server: " . $currentCode);
                error_log("  Time window used: " . time() . " (unix timestamp)");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("TOTP Verification Error: " . $e->getMessage());
            return false;
        }
    }

}