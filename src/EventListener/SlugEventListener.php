<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Provider;
use App\Entity\Service;
use App\Service\ProviderSlugService;
use App\Service\ClientSlugService;
use App\Service\ArticleSlugService;
use App\Service\ServiceSlugService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class SlugEventListener
{
    public function __construct(
        private ProviderSlugService $providerSlugService,
        private ClientSlugService $clientSlugService,
        private ArticleSlugService $articleSlugService,
        private ServiceSlugService $serviceSlugService
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Provider && !$entity->getSlug()) {
            $slug = $this->providerSlugService->generateSlugForNewProvider(
                $entity->getFirstName(),
                $entity->getLastName()
            );
            $entity->setSlug($slug);
        }

        if ($entity instanceof Client && !$entity->getSlug()) {
            $slug = $this->clientSlugService->generateSlugForNewClient(
                $entity->getFirstName(),
                $entity->getLastName()
            );
            $entity->setSlug($slug);
        }

        if ($entity instanceof Article && !$entity->getSlug()) {
            $slug = $this->articleSlugService->generateSlugForNewArticle($entity->getTitle());
            $entity->setSlug($slug);
        }

        if ($entity instanceof Service && !$entity->getSlug()) {
            $slug = $this->serviceSlugService->generateSlugForNewService($entity->getTitle());
            $entity->setSlug($slug);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Provider) {
            if ($args->hasChangedField('firstName') || $args->hasChangedField('lastName')) {
                $this->providerSlugService->updateProviderSlug($entity);
            }
        }

        if ($entity instanceof Client) {
            if ($args->hasChangedField('firstName') || $args->hasChangedField('lastName')) {
                $this->clientSlugService->updateClientSlug($entity);
            }
        }

        if ($entity instanceof Article) {
            if ($args->hasChangedField('title')) {
                $this->articleSlugService->updateArticleSlug($entity);
            }
        }

        if ($entity instanceof Service) {
            if ($args->hasChangedField('title')) {
                $this->serviceSlugService->updateServiceSlug($entity);
            }
        }
    }
}
