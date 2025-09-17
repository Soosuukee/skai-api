<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Client;
use App\Entity\Provider;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class UserPasswordListener
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$this->supports($entity)) {
            return;
        }

        $this->hashPasswordIfNeeded($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$this->supports($entity)) {
            return;
        }

        if ($args->hasChangedField('password')) {
            $this->hashPasswordIfNeeded($entity);

            // Recompute changeset since we changed the entity value
            /** @var EntityManagerInterface $em */
            $em = $args->getObjectManager();
            $metadata = $em->getClassMetadata(get_class($entity));
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($metadata, $entity);
        }
    }

    private function supports(object $entity): bool
    {
        return $entity instanceof PasswordAuthenticatedUserInterface
            && ($entity instanceof Provider || $entity instanceof Client);
    }

    private function hashPasswordIfNeeded(PasswordAuthenticatedUserInterface $user): void
    {
        $current = method_exists($user, 'getPassword') ? (string) $user->getPassword() : '';

        // Heuristic: if already hashed (bcrypt/argon start with $), skip
        if ($current !== '' && str_starts_with($current, '$')) {
            return;
        }

        // If empty, nothing to hash
        if ($current === '') {
            return;
        }

        $hashed = $this->passwordHasher->hashPassword($user, $current);
        if ($user instanceof Provider) {
            $user->setPassword($hashed);
        } elseif ($user instanceof Client) {
            $user->setPassword($hashed);
        }
    }
}


