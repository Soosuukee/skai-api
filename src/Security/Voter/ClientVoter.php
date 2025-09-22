<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Client;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClientVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const VIEW_CONTACT = 'VIEW_CONTACT';
    public const VIEW_STATS = 'VIEW_STATS';
    public const MANAGE_BOOKINGS = 'MANAGE_BOOKINGS';
    public const MANAGE_REVIEWS = 'MANAGE_REVIEWS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::DELETE,
            self::VIEW_CONTACT,
            self::VIEW_STATS,
            self::MANAGE_BOOKINGS,
            self::MANAGE_REVIEWS
        ]) && $subject instanceof Client;
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
            self::MANAGE_BOOKINGS => $this->canManageBookings($subject, $user),
            self::MANAGE_REVIEWS => $this->canManageReviews($subject, $user),
            default => false,
        };
    }

    private function canView(Client $client, mixed $user): bool
    {
        return true;
    }

    private function canEdit(Client $client, mixed $user): bool
    {
        return $user instanceof Client && $client === $user;
    }

    private function canDelete(Client $client, mixed $user): bool
    {
        return $user instanceof Client && $client === $user;
    }

    private function canViewContact(Client $client, mixed $user): bool
    {
        return $user instanceof Client && $client === $user;
    }

    private function canViewStats(Client $client, mixed $user): bool
    {
        return $user instanceof Client && $client === $user;
    }

    private function canManageBookings(Client $client, mixed $user): bool
    {
        return $user instanceof Client && $client === $user;
    }

    private function canManageReviews(Client $client, mixed $user): bool
    {
        return $user instanceof Client && $client === $user;
    }
}
