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

        $refreshedUser = $user instanceof Provider
            ? $this->providerRepository->findById($user->getId())
            : $this->clientRepository->findById($user->getId());

        if (!$refreshedUser) {
            throw new UserNotFoundException('Utilisateur non trouvé lors du refresh');
        }

        return $refreshedUser;
    }

    public function supportsClass(string $class): bool
    {
        return Provider::class === $class || Client::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->providerRepository->findByEmail($identifier)
            ?? $this->clientRepository->findByEmail($identifier);

        if (!$user) {
            throw new UserNotFoundException(sprintf('Utilisateur avec l\'email "%s" non trouvé.', $identifier));
        }

        return $user;
    }
}
