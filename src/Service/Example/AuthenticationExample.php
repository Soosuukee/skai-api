<?php

declare(strict_types=1);

namespace App\Service\Example;

use App\Service\AuthService;
use App\Entity\Provider;
use App\Entity\Client;

/**
 * Exemple d'utilisation du système d'authentification
 * Démontre comment utiliser AuthService dans les contrôleurs
 */
class AuthenticationExample
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Exemple d'utilisation dans un contrôleur
     */
    public function exampleControllerUsage(): array
    {
        // 1. Vérifier si l'utilisateur est authentifié
        if (!$this->authService->isAuthenticated()) {
            return [
                'error' => 'Utilisateur non authentifié',
                'status' => 401
            ];
        }

        // 2. Obtenir l'utilisateur actuel
        $user = $this->authService->getCurrentUser();

        // 3. Vérifier le type d'utilisateur
        if ($this->authService->isProvider()) {
            $provider = $this->authService->getCurrentProvider();
            return [
                'user_type' => 'provider',
                'user_id' => $provider->getId(),
                'user_name' => $provider->getFirstName() . ' ' . $provider->getLastName()
            ];
        }

        if ($this->authService->isClient()) {
            $client = $this->authService->getCurrentClient();
            return [
                'user_type' => 'client',
                'user_id' => $client->getId(),
                'user_name' => $client->getFirstName() . ' ' . $client->getLastName()
            ];
        }

        return ['error' => 'Type d\'utilisateur inconnu'];
    }

    /**
     * Exemple de vérification d'accès à une ressource
     */
    public function exampleResourceAccess(): array
    {
        $userId = $this->authService->getCurrentUserId();

        // Vérifier l'accès à différentes ressources
        $access = [
            'can_access_own_profile' => $this->authService->canAccessResource('provider', $userId ?? 0),
            'can_access_service_1' => $this->authService->canAccessService(1),
            'can_access_article_1' => $this->authService->canAccessArticle(1),
            'can_access_booking_1' => $this->authService->canAccessBooking(1),
        ];

        return [
            'user_id' => $userId,
            'access' => $access
        ];
    }

    /**
     * Exemple de génération de token
     */
    public function exampleTokenGeneration(): array
    {
        // Simuler un utilisateur (en réalité, chargé depuis la DB)
        // Note: Les entités Doctrine n'ont pas de setId(), l'ID est généré automatiquement
        $provider = new Provider();
        $provider->setEmail('provider@example.com');
        $provider->setFirstName('John');
        $provider->setLastName('Doe');

        $client = new Client();
        $client->setEmail('client@example.com');
        $client->setFirstName('Jane');
        $client->setLastName('Smith');

        // Générer des tokens
        $providerToken = $this->authService->generateToken($provider);
        $clientToken = $this->authService->generateToken($client);

        return [
            'provider_token' => $providerToken,
            'client_token' => $clientToken,
            'note' => 'En production, utiliser une vraie bibliothèque JWT. Les IDs sont générés automatiquement par Doctrine.'
        ];
    }

    /**
     * Exemple de validation de routes publiques
     */
    public function examplePublicRoutes(): array
    {
        $routes = [
            '/api/v1/auth/login',
            '/api/v1/auth/register',
            '/api/v1/images/test.jpg',
            '/api/v1/countries',
            '/api/v1/providers/1', // Route protégée
        ];

        $results = [];
        foreach ($routes as $route) {
            $results[$route] = $this->authService->isPublicRoute($route);
        }

        return [
            'routes' => $results,
            'note' => 'Les routes marquées true sont publiques et ne nécessitent pas d\'authentification'
        ];
    }
}
