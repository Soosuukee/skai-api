<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class ArticleTimestampListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article) {
            return;
        }

        $now = new \DateTimeImmutable();

        if ($entity->getPublishedAt() === null) {
            $entity->setPublishedAt($now);
        }

        // Toujours définir updatedAt à la création
        $entity->setUpdatedAt($now);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article) {
            return;
        }

        $now = new \DateTimeImmutable();

        // Si publishedAt est encore nul, le définir à maintenant
        if ($entity->getPublishedAt() === null) {
            $entity->setPublishedAt($now);
        }

        // Toujours rafraîchir updatedAt à chaque mise à jour
        $entity->setUpdatedAt($now);

        // Recalculer le changeset car on modifie des valeurs durant preUpdate
        $em = $args->getObjectManager();
        if ($em instanceof \Doctrine\ORM\EntityManagerInterface) {
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata(Article::class), $entity);
        }
    }
}
