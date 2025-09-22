<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Service;
use App\Entity\Provider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ServiceVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const BOOK = 'BOOK';
    public const ACTIVATE = 'ACTIVATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::BOOK, self::ACTIVATE])
            && $subject instanceof Service;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::BOOK => $this->canBook($subject, $user),
            self::ACTIVATE => $this->canActivate($subject, $user),
            default => false,
        };
    }

    private function canView(Service $service, mixed $user): bool
    {
        if ($service->isActive()) {
            return true;
        }

        return $user instanceof Provider && $service->getProvider() === $user;
    }

    private function canEdit(Service $service, mixed $user): bool
    {
        return $user instanceof Provider && $service->getProvider() === $user;
    }

    private function canDelete(Service $service, mixed $user): bool
    {
        return $user instanceof Provider && $service->getProvider() === $user;
    }

    private function canBook(Service $service, mixed $user): bool
    {
        if (!$user instanceof Provider) {
            return false;
        }

        return $service->isActive() && $service->getProvider() !== $user;
    }

    private function canActivate(Service $service, mixed $user): bool
    {
        return $user instanceof Provider && $service->getProvider() === $user;
    }
}
