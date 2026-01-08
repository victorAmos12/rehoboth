<?php

namespace App\EventListener;

use App\Service\JwtService;
use Firebase\JWT\ExpiredException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener pour valider les tokens JWT sur toutes les requêtes API
 * 
 * Vérifie:
 * - La présence du token
 * - La validité du token
 * - L'expiration du token
 * - L'inactivité du token (15 minutes)
 * 
 * Rejette les requêtes avec un token invalide ou expiré (401)
 */
class JwtTokenListener implements EventSubscriberInterface
{
    /**
     * Constructeur avec injection du service JWT
     * 
     * @param JwtService $jwtService Service JWT
     */
    public function __construct(
        private JwtService $jwtService,
    ) {
    }

    /**
     * Retourne les événements écoutés
     * 
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Laisser NelmioCorsBundle traiter la requête en premier (préflight + ajout des headers),
            // puis appliquer la vérification JWT.
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    /**
     * Valide le token JWT à chaque requête API
     * 
     * @param RequestEvent $event Événement de requête
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Ignorer les requêtes non-API
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        // Ignorer les préflights CORS
        if ($request->isMethod('OPTIONS')) {
            return;
        }

        // Ignorer les endpoints publics (login, etc.)
        $publicEndpoints = [
            '/api/auth/login',
            '/api/auth/logout',
        ];

        if (in_array($request->getPathInfo(), $publicEndpoints)) {
            return;
        }

        // Récupérer le header Authorization
        $authHeader = $request->headers->get('Authorization');

        // Si pas de token, rejeter
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => 'Token manquant. Authentification requise.',
            ], 401));
            return;
        }

        // Extraire le token
        $token = substr($authHeader, 7);

        // Valider le token
        try {
            $payload = $this->jwtService->validateToken($token);

            // Ajouter le payload à la requête pour utilisation dans les contrôleurs
            $request->attributes->set('jwt_payload', $payload);

        } catch (ExpiredException $e) {
            // Token expiré ou inactif
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => $e->getMessage() ?: 'Token expiré. Veuillez vous reconnecter.',
            ], 401));

        } catch (\Exception $e) {
            // Token invalide
            $event->setResponse(new JsonResponse([
                'success' => false,
                'error' => 'Token invalide. Authentification requise.',
            ], 401));
        }
    }
}
