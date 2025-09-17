<?php

declare(strict_types=1);

namespace App\Service\Example;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Provider;
use App\Entity\Service;
use App\Service\SlugManager;

/**
 * Exemple d'utilisation des services de slugification
 */
class SlugUsageExample
{
    public function __construct(
        private SlugManager $slugManager
    ) {}

    /**
     * Exemple d'utilisation pour les providers
     */
    public function providerSlugExample(): void
    {
        // Créer un nouveau provider
        $provider = new Provider();
        $provider->setFirstName('Jean');
        $provider->setLastName('Dupont');
        $provider->setEmail('jean.dupont@example.com');

        // Générer un slug temporaire pour un nouveau provider
        $temporarySlug = $this->slugManager->generateProviderSlug('Jean', 'Dupont');
        $provider->setSlug($temporarySlug);

        // Sauvegarder le provider (il aura un ID)
        // ... sauvegarde en base ...

        // Mettre à jour le slug avec l'ID réel
        $this->slugManager->updateProviderSlug($provider);
        // Résultat : "jean-dupont-A123456789" (où A123456789 est une lettre + 9 chiffres aléatoires)
    }

    /**
     * Exemple d'utilisation pour les clients
     */
    public function clientSlugExample(): void
    {
        // Créer un nouveau client
        $client = new Client();
        $client->setFirstName('Marie');
        $client->setLastName('Martin');
        $client->setEmail('marie.martin@example.com');

        // Générer un slug temporaire
        $temporarySlug = $this->slugManager->generateClientSlug('Marie', 'Martin');
        $client->setSlug($temporarySlug);

        // Sauvegarder le client
        // ... sauvegarde en base ...

        // Mettre à jour le slug avec l'ID réel
        $this->slugManager->updateClientSlug($client);
        // Résultat : "marie-martin-B987654321" (où B987654321 est une lettre + 9 chiffres aléatoires)
    }

    /**
     * Exemple d'utilisation pour les articles
     */
    public function articleSlugExample(): void
    {
        // Créer un nouvel article
        $article = new Article();
        $article->setTitle('Comment créer une API REST avec Symfony');

        // Générer un slug basé sur le titre
        $slug = $this->slugManager->generateArticleSlug('Comment créer une API REST avec Symfony');
        $article->setSlug($slug);

        // Résultat : "comment-creer-une-api-rest-avec-symfony"

        // Si le titre change, mettre à jour le slug
        $article->setTitle('Guide complet pour créer une API REST');
        $this->slugManager->updateArticleSlug($article);
        // Résultat : "guide-complet-pour-creer-une-api-rest"
    }

    /**
     * Exemple d'utilisation pour les services
     */
    public function serviceSlugExample(): void
    {
        // Créer un nouveau service
        $service = new Service();
        $service->setTitle('Développement d\'application web');

        // Générer un slug basé sur le titre
        $slug = $this->slugManager->generateServiceSlug('Développement d\'application web');
        $service->setSlug($slug);

        // Résultat : "developpement-dapplication-web"

        // Si le titre change, mettre à jour le slug
        $service->setTitle('Création d\'applications web modernes');
        $this->slugManager->updateServiceSlug($service);
        // Résultat : "creation-dapplications-web-modernes"
    }

    /**
     * Exemple de gestion des doublons
     */
    public function duplicateHandlingExample(): void
    {
        // Premier article
        $article1 = new Article();
        $article1->setTitle('Mon Premier Article');
        $slug1 = $this->slugManager->generateArticleSlug('Mon Premier Article');
        // Résultat : "mon-premier-article"

        // Deuxième article avec le même titre
        $article2 = new Article();
        $article2->setTitle('Mon Premier Article');
        $slug2 = $this->slugManager->generateArticleSlug('Mon Premier Article');
        // Résultat : "mon-premier-article-1" (automatiquement numéroté)

        // Troisième article
        $article3 = new Article();
        $article3->setTitle('Mon Premier Article');
        $slug3 = $this->slugManager->generateArticleSlug('Mon Premier Article');
        // Résultat : "mon-premier-article-2"
    }
}
