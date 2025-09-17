<?php

declare(strict_types=1);

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * Service d'authentification utilisant Lexik JWT
 */
class AuthService
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}

    /**
     * Génère un token JWT pour un utilisateur
     */
    public function generateToken(object $user): string
    {
        return $this->jwtManager->create($user);
    }

    /**
     * Déconnexion (pour compatibilité)
     */
    public function logout(): array
    {
        return [
            'success' => true,
            'message' => 'Déconnexion réussie'
        ];
    }
}
