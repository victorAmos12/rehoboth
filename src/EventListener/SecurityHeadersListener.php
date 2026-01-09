<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener pour gérer les en-têtes de sécurité COOP/COEP
 * 
 * Permet la communication entre la fenêtre popup de Google et la fenêtre parente
 * en configurant les en-têtes Cross-Origin-Opener-Policy et Cross-Origin-Embedder-Policy
 * 
 * IMPORTANT: Ce listener doit s'exécuter APRÈS nelmio_cors pour éviter les conflits
 */
class SecurityHeadersListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // Priorité négative pour s'exécuter APRÈS nelmio_cors (qui a priorité 0)
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        error_log("SecurityHeadersListener - Path: " . $pathInfo);

        // Pour les endpoints Google Auth, utiliser same-origin-allow-popups
        // Cela permet à la fenêtre popup de communiquer avec la fenêtre parente
        if (strpos($pathInfo, '/api/auth/google') === 0) {
            error_log("SecurityHeadersListener - Setting COOP headers for Google Auth endpoint");
            
            // Supprimer les en-têtes conflictuels d'abord
            $response->headers->remove('Cross-Origin-Opener-Policy');
            $response->headers->remove('Cross-Origin-Embedder-Policy');
            
            // Ajouter les bons en-têtes
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
            $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
            
            error_log("SecurityHeadersListener - COOP headers set: same-origin-allow-popups");
        } else {
            // Pour les autres endpoints, utiliser same-origin
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        }

        // Ajouter les en-têtes CORS supplémentaires
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        // Log des en-têtes finaux
        error_log("SecurityHeadersListener - Final COOP header: " . $response->headers->get('Cross-Origin-Opener-Policy'));
    }
}
