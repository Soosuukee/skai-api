<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class ServiceCoherenceListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Service) {
            return;
        }

        $this->normalizePrices($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Service) {
            return;
        }

        $this->normalizePrices($entity);

        $em = $args->getObjectManager();
        if ($em instanceof \Doctrine\ORM\EntityManagerInterface) {
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($em->getClassMetadata(Service::class), $entity);
        }
    }

    private function normalizePrices(Service $service): void
    {
        $min = $service->getMinPrice();
        $max = $service->getMaxPrice();

        if ($min === null || $max === null) {
            return; // rien à faire si l'un est manquant
        }

        // Les champs sont des strings, comparer en float de manière cohérente
        $minF = (float) $min;
        $maxF = (float) $max;

        if ($minF > $maxF) {
            // swap
            $service->setMinPrice((string) $maxF);
            $service->setMaxPrice((string) $minF);
        }
    }
}
