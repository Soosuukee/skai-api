<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Provider;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist, priority: 500)]
class SlugPostPersistListener
{
    public function __construct(
        private SlugManager $slugManager
    ) {}

    /**
     * Met à jour le slug avec l'ID réel après la sauvegarde
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        // Pour les providers, mettre à jour le slug avec l'ID réel
        if ($entity instanceof Provider) {
            $this->slugManager->updateProviderSlug($entity);
        }

        // Pour les clients, mettre à jour le slug avec l'ID réel
        if ($entity instanceof Client) {
            $this->slugManager->updateClientSlug($entity);
        }
    }
}
