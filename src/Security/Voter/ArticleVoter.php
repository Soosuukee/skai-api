<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\Provider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const PUBLISH = 'PUBLISH';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::PUBLISH])
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::PUBLISH => $this->canPublish($subject, $user),
            default => false,
        };
    }

    private function canView(Article $article, mixed $user): bool
    {
        if ($article->isPublished()) {
            return true;
        }

        return $user instanceof Provider && $article->getProvider() === $user;
    }

    private function canEdit(Article $article, mixed $user): bool
    {
        return $user instanceof Provider && $article->getProvider() === $user;
    }

    private function canDelete(Article $article, mixed $user): bool
    {
        return $user instanceof Provider && $article->getProvider() === $user;
    }

    private function canPublish(Article $article, mixed $user): bool
    {
        return $user instanceof Provider && $article->getProvider() === $user;
    }
}
