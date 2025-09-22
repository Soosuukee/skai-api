<?php

declare(strict_types=1);

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthService
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager
    ) {}

    public function generateToken(object $user): string
    {
        return $this->jwtManager->create($user);
    }

    public function logout(): array
    {
        return [
            'success' => true,
            'message' => 'Déconnexion réussie'
        ];
    }
}
