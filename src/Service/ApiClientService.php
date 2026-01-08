<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service API Client pour communiquer avec le backend
 * Gère les requêtes HTTP, authentification et erreurs
 * 
 * Utilisation :
 * $response = $this->apiClient->post('/api/auth/login', ['login' => 'admin', 'password' => 'pass']);
 */
class ApiClientService
{
    private HttpClientInterface $httpClient;
    private string $baseUrl;
    private ?string $token = null;

    public function __construct(HttpClientInterface $httpClient, string $baseUrl = 'http://localhost:8000')
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Définir le token d'authentification
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Récupérer le token d'authentification
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Effectuer une requête GET
     */
    public function get(string $endpoint, array $options = []): array
    {
        return $this->request('GET', $endpoint, $options);
    }

    /**
     * Effectuer une requête POST
     */
    public function post(string $endpoint, array $data = [], array $options = []): array
    {
        $options['json'] = $data;
        return $this->request('POST', $endpoint, $options);
    }

    /**
     * Effectuer une requête PUT
     */
    public function put(string $endpoint, array $data = [], array $options = []): array
    {
        $options['json'] = $data;
        return $this->request('PUT', $endpoint, $options);
    }

    /**
     * Effectuer une requête PATCH
     */
    public function patch(string $endpoint, array $data = [], array $options = []): array
    {
        $options['json'] = $data;
        return $this->request('PATCH', $endpoint, $options);
    }

    /**
     * Effectuer une requête DELETE
     */
    public function delete(string $endpoint, array $options = []): array
    {
        return $this->request('DELETE', $endpoint, $options);
    }

    /**
     * Effectuer une requête HTTP générique
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            // Construire l'URL complète
            $url = $this->baseUrl . $endpoint;

            // Ajouter les headers par défaut
            $headers = $options['headers'] ?? [];
            $headers['Content-Type'] = 'application/json';
            $headers['Accept'] = 'application/json';

            // Ajouter le token d'authentification si disponible
            if ($this->token) {
                $headers['Authorization'] = 'Bearer ' . $this->token;
            }

            $options['headers'] = $headers;

            // Effectuer la requête
            $response = $this->httpClient->request($method, $url, $options);

            // Récupérer le code de statut
            $statusCode = $response->getStatusCode();

            // Récupérer le contenu
            $content = $response->getContent();

            // Décoder le JSON
            $data = json_decode($content, true);

            // Retourner la réponse formatée
            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status' => $statusCode,
                'data' => $data,
                'message' => $data['message'] ?? null,
                'error' => $data['error'] ?? null,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 500,
                'data' => null,
                'message' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
