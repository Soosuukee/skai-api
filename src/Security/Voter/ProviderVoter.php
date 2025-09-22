<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Provider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProviderVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const VIEW_CONTACT = 'VIEW_CONTACT';
    public const VIEW_STATS = 'VIEW_STATS';
    public const MANAGE_SERVICES = 'MANAGE_SERVICES';
    public const MANAGE_ARTICLES = 'MANAGE_ARTICLES';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::DELETE,
            self::VIEW_CONTACT,
            self::VIEW_STATS,
            self::MANAGE_SERVICES,
            self::MANAGE_ARTICLES
        ]) && $subject instanceof Provider;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VIEW_CONTACT => $this->canViewContact($subject, $user),
            self::VIEW_STATS => $this->canViewStats($subject, $user),
            self::MANAGE_SERVICES => $this->canManageServices($subject, $user),
            self::MANAGE_ARTICLES => $this->canManageArticles($subject, $user),
            default => false,
        };
    }

    private function canView(Provider $provider, mixed $user): bool
    {
        return true;
    }

    private function canEdit(Provider $provider, mixed $user): bool
    {
        return $user instanceof Provider && $provider === $user;
    }

    private function canDelete(Provider $provider, mixed $user): bool
    {
        return $user instanceof Provider && $provider === $user;
    }

    private function canViewContact(Provider $provider, mixed $user): bool
    {
        return $user instanceof Provider && $provider === $user;
    }

    private function canViewStats(Provider $provider, mixed $user): bool
    {
        return $user instanceof Provider && $provider === $user;
    }

    private function canManageServices(Provider $provider, mixed $user): bool
    {
        return $user instanceof Provider && $provider === $user;
    }

    private function canManageArticles(Provider $provider, mixed $user): bool
    {
        return $user instanceof Provider && $provider === $user;
    }
}
