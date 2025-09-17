<?php

declare(strict_types=1);

namespace App\Service\Example;

use App\Service\AuthService;
use App\Repository\ProviderRepository;
use App\Repository\ClientRepository;

/**
 * Exemple de test du système d'authentification
 * Démontre comment tester les fonctionnalités d'authentification
 */
class AuthenticationTestExample
{
    public function __construct(
        private AuthService $authService,
        private ProviderRepository $providerRepository,
        private ClientRepository $clientRepository
    ) {}

    /**
     * Test complet du système d'authentification
     */
    public function runFullTest(): array
    {
        $results = [];

        // 1. Test des routes publiques
        $results['public_routes'] = $this->testPublicRoutes();

        // 2. Test de génération de tokens
        $results['token_generation'] = $this->testTokenGeneration();

        // 3. Test de validation de tokens
        $results['token_validation'] = $this->testTokenValidation();

        // 4. Test des permissions d'accès
        $results['access_permissions'] = $this->testAccessPermissions();

        return [
            'test_results' => $results,
            'status' => 'completed',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Test des routes publiques
     */
    private function testPublicRoutes(): array
    {
        $routes = [
            '/api/v1/auth/login' => true,
            '/api/v1/auth/register' => true,
            '/api/v1/images/test.jpg' => true,
            '/api/v1/countries' => true,
            '/api/v1/jobs' => true,
            '/api/v1/languages' => true,
            '/api/v1/tags' => true,
            '/api/v1/hard-skills' => true,
            '/api/v1/soft-skills' => true,
            '/api/v1/providers/1' => false, // Protégée
            '/api/v1/services/1' => false,  // Protégée
        ];

        $results = [];
        foreach ($routes as $route => $expectedPublic) {
            $isPublic = $this->authService->isPublicRoute($route);
            $results[$route] = [
                'expected' => $expectedPublic,
                'actual' => $isPublic,
                'correct' => $isPublic === $expectedPublic
            ];
        }

        return $results;
    }

    /**
     * Test de génération de tokens
     */
    private function testTokenGeneration(): array
    {
        // Créer des utilisateurs de test (sans les sauvegarder en DB)
        $provider = new \App\Entity\Provider();
        $provider->setEmail('test-provider@example.com');
        $provider->setFirstName('Test');
        $provider->setLastName('Provider');

        $client = new \App\Entity\Client();
        $client->setEmail('test-client@example.com');
        $client->setFirstName('Test');
        $client->setLastName('Client');

        // Générer des tokens
        $providerToken = $this->authService->generateToken($provider);
        $clientToken = $this->authService->generateToken($client);

        return [
            'provider_token_generated' => !empty($providerToken),
            'client_token_generated' => !empty($clientToken),
            'provider_token_length' => strlen($providerToken),
            'client_token_length' => strlen($clientToken),
            'tokens_different' => $providerToken !== $clientToken
        ];
    }

    /**
     * Test de validation de tokens
     */
    private function testTokenValidation(): array
    {
        $results = [];

        // Test avec un token invalide
        $invalidToken = 'invalid_token_123';
        $results['invalid_token'] = $this->authService->validateToken($invalidToken) === null;

        // Test avec un token vide
        $emptyToken = '';
        $results['empty_token'] = $this->authService->validateToken($emptyToken) === null;

        // Test avec un token valide (simulé)
        $validToken = $this->authService->generateToken(new \App\Entity\Provider());
        $results['valid_token'] = $this->authService->validateToken($validToken) !== null;

        return $results;
    }

    /**
     * Test des permissions d'accès
     */
    private function testAccessPermissions(): array
    {
        $results = [];

        // Test sans authentification
        $results['not_authenticated'] = !$this->authService->isAuthenticated();
        $results['not_provider'] = !$this->authService->isProvider();
        $results['not_client'] = !$this->authService->isClient();

        // Test avec authentification simulée
        $testProvider = new \App\Entity\Provider();
        $testProvider->setEmail('test@example.com');

        // Simuler l'authentification
        $token = $this->authService->generateToken($testProvider);
        $this->authService->authenticate($token);

        $results['authenticated_after_login'] = $this->authService->isAuthenticated();
        $results['can_access_services'] = $this->authService->canAccessService(1);
        $results['can_access_articles'] = $this->authService->canAccessArticle(1);

        return $results;
    }

    /**
     * Test avec de vrais utilisateurs de la base de données
     */
    public function testWithRealUsers(): array
    {
        $results = [];

        // Chercher un provider existant
        $providers = $this->providerRepository->findAll();
        if (!empty($providers)) {
            $provider = $providers[0];
            $token = $this->authService->generateToken($provider);
            $results['provider_token_generated'] = !empty($token);

            if ($this->authService->authenticate($token)) {
                $results['provider_authenticated'] = true;
                $results['provider_is_provider'] = $this->authService->isProvider();
                $results['provider_user_id'] = $this->authService->getCurrentUserId();
            }
        }

        // Chercher un client existant
        $clients = $this->clientRepository->findAll();
        if (!empty($clients)) {
            $client = $clients[0];
            $token = $this->authService->generateToken($client);
            $results['client_token_generated'] = !empty($token);

            if ($this->authService->authenticate($token)) {
                $results['client_authenticated'] = true;
                $results['client_is_client'] = $this->authService->isClient();
                $results['client_user_id'] = $this->authService->getCurrentUserId();
            }
        }

        return $results;
    }

    /**
     * Test de performance du système d'authentification
     */
    public function testPerformance(): array
    {
        $iterations = 100;
        $results = [];

        // Test de génération de tokens
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $provider = new \App\Entity\Provider();
            $provider->setEmail("test{$i}@example.com");
            $this->authService->generateToken($provider);
        }
        $endTime = microtime(true);

        $results['token_generation_time'] = $endTime - $startTime;
        $results['tokens_per_second'] = $iterations / ($endTime - $startTime);

        // Test de validation de tokens
        $token = $this->authService->generateToken(new \App\Entity\Provider());
        $startTime = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $this->authService->validateToken($token);
        }
        $endTime = microtime(true);

        $results['token_validation_time'] = $endTime - $startTime;
        $results['validations_per_second'] = $iterations / ($endTime - $startTime);

        return $results;
    }
}
