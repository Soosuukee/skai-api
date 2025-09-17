<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Provider;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist, priority: 500)]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500)]
class SlugEventListener
{
    public function __construct(
        private SlugManager $slugManager
    ) {}

    /**
     * Génère automatiquement un slug lors de la création d'une entité
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Provider && !$entity->getSlug()) {
            // Pour un nouveau provider, générer un slug temporaire
            $slug = $this->slugManager->generateProviderSlug(
                $entity->getFirstName(),
                $entity->getLastName()
            );
            $entity->setSlug($slug);
        }

        if ($entity instanceof Client && !$entity->getSlug()) {
            // Pour un nouveau client, générer un slug temporaire
            $slug = $this->slugManager->generateClientSlug(
                $entity->getFirstName(),
                $entity->getLastName()
            );
            $entity->setSlug($slug);
        }

        if ($entity instanceof Article && !$entity->getSlug()) {
            // Pour un nouvel article, générer un slug basé sur le titre
            $slug = $this->slugManager->generateArticleSlug($entity->getTitle());
            $entity->setSlug($slug);
        }

        if ($entity instanceof Service && !$entity->getSlug()) {
            // Pour un nouveau service, générer un slug basé sur le titre
            $slug = $this->slugManager->generateServiceSlug($entity->getTitle());
            $entity->setSlug($slug);
        }
    }

    /**
     * Met à jour le slug lors de la modification d'une entité
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        // Pour les providers, mettre à jour le slug si le prénom ou nom change
        if ($entity instanceof Provider) {
            if ($args->hasChangedField('firstName') || $args->hasChangedField('lastName')) {
                $this->slugManager->updateProviderSlug($entity);
            }
        }

        // Pour les clients, mettre à jour le slug si le prénom ou nom change
        if ($entity instanceof Client) {
            if ($args->hasChangedField('firstName') || $args->hasChangedField('lastName')) {
                $this->slugManager->updateClientSlug($entity);
            }
        }

        // Pour les articles, mettre à jour le slug si le titre change
        if ($entity instanceof Article) {
            if ($args->hasChangedField('title')) {
                $this->slugManager->updateArticleSlug($entity);
            }
        }

        // Pour les services, mettre à jour le slug si le titre change
        if ($entity instanceof Service) {
            if ($args->hasChangedField('title')) {
                $this->slugManager->updateServiceSlug($entity);
            }
        }
    }
}
