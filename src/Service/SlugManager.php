<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Provider;
use App\Entity\Service;

/**
 * Service centralisé pour la gestion des slugs de toutes les entités
 */
class SlugManager
{
    public function __construct(
        private ProviderSlugService $providerSlugService,
        private ClientSlugService $clientSlugService,
        private ArticleSlugService $articleSlugService,
        private ServiceSlugService $serviceSlugService
    ) {}

    /**
     * Génère et met à jour le slug d'un provider
     */
    public function updateProviderSlug(Provider $provider): void
    {
        $this->providerSlugService->updateProviderSlug($provider);
    }

    /**
     * Génère et met à jour le slug d'un client
     */
    public function updateClientSlug(Client $client): void
    {
        $this->clientSlugService->updateClientSlug($client);
    }

    /**
     * Génère et met à jour le slug d'un article
     */
    public function updateArticleSlug(Article $article): void
    {
        $this->articleSlugService->updateArticleSlug($article);
    }

    /**
     * Génère et met à jour le slug d'un service
     */
    public function updateServiceSlug(Service $service): void
    {
        $this->serviceSlugService->updateServiceSlug($service);
    }

    /**
     * Génère un slug pour un nouveau provider
     */
    public function generateProviderSlug(string $firstName, string $lastName): string
    {
        return $this->providerSlugService->generateSlugForNewProvider($firstName, $lastName);
    }

    /**
     * Génère un slug pour un nouveau client
     */
    public function generateClientSlug(string $firstName, string $lastName): string
    {
        return $this->clientSlugService->generateSlugForNewClient($firstName, $lastName);
    }

    /**
     * Génère un slug pour un nouvel article
     */
    public function generateArticleSlug(string $title): string
    {
        return $this->articleSlugService->generateSlugForNewArticle($title);
    }

    /**
     * Génère un slug pour un nouveau service
     */
    public function generateServiceSlug(string $title): string
    {
        return $this->serviceSlugService->generateSlugForNewService($title);
    }
}
