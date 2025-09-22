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
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Provider || $entity instanceof Client) {
            $this->hashPassword($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (($entity instanceof Provider || $entity instanceof Client) && $args->hasChangedField('password')) {
            $this->hashPassword($entity);
        }
    }

    private function hashPassword($user): void
    {
        $password = $user->getPassword();
        if ($password && !str_starts_with($password, '$')) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }
    }
}
