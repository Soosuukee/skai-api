<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Provider;
use App\Entity\Client;
use App\Repository\ProviderRepository;
use App\Repository\ClientRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User Provider pour charger les utilisateurs (Provider/Client)
 * Implémente UserProviderInterface pour l'intégration avec Symfony Security
 */
class UserProvider implements UserProviderInterface
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ClientRepository $clientRepository
    ) {}

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Provider && !$user instanceof Client) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Recharger l'utilisateur depuis la base de données
        if ($user instanceof Provider) {
            $refreshedUser = $this->providerRepository->findById($user->getId());
        } else {
            $refreshedUser = $this->clientRepository->findById($user->getId());
        }

        if (!$refreshedUser) {
            throw new UserNotFoundException('Utilisateur non trouvé lors du refresh');
        }

        /** @var UserInterface $refreshedUser */
        return $refreshedUser;
    }

    public function supportsClass(string $class): bool
    {
        return Provider::class === $class || Client::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Chercher d'abord dans les providers
        $user = $this->providerRepository->findByEmail($identifier);

        if ($user) {
            /** @var UserInterface $user */
            return $user;
        }

        // Chercher ensuite dans les clients
        $user = $this->clientRepository->findByEmail($identifier);

        if ($user) {
            /** @var UserInterface $user */
            return $user;
        }

        throw new UserNotFoundException(sprintf('Utilisateur avec l\'email "%s" non trouvé.', $identifier));
    }

    /**
     * Charge un utilisateur par ID et type
     */
    public function loadUserByIdAndType(int $userId, string $userType): Provider|Client|null
    {
        return match ($userType) {
            'provider' => $this->providerRepository->findById($userId),
            'client' => $this->clientRepository->findById($userId),
            default => null
        };
    }

    /**
     * Retourne tous les providers
     */
    public function getProviders(): array
    {
        return $this->providerRepository->findAll();
    }

    /**
     * Retourne tous les clients
     */
    public function getClients(): array
    {
        return $this->clientRepository->findAll();
    }
}
