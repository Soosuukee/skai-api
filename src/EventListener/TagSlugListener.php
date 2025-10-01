<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class TagSlugListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Tag) {
            return;
        }

        if ($entity->getSlug() === null || $entity->getSlug() === '') {
            $entity->setSlug($this->slugify((string)$entity->getTitle()));
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Tag) {
            return;
        }

        if ($args->hasChangedField('title')) {
            $entity->setSlug($this->slugify((string)$entity->getTitle()));

            $em = $args->getObjectManager();
            if ($em instanceof \Doctrine\ORM\EntityManagerInterface) {
                $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata(Tag::class), $entity);
            }
        }
    }

    private function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? '';
        $text = preg_replace('/[\s-]+/', '-', $text) ?? '';
        return trim($text, '-');
    }
}
