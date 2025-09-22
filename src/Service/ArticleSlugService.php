<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArticleSlugService
{
    public function __construct(
        private SlugService $slugService,
        private ArticleRepository $articleRepository,
        private EntityManagerInterface $entityManager
    ) {}

    // Génère un slug pour un article basé sur son titre
    public function generateSlug(Article $article): string
    {
        $title = $article->getTitle();

        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        return $this->slugService->slugify($title);
    }

    // Génère un slug pour un article (pas besoin d'unicité globale)
    public function generateSlugForArticle(Article $article): string
    {
        return $this->generateSlug($article);
    }

    // Met à jour le slug d'un article et le sauvegarde
    public function updateArticleSlug(Article $article): void
    {
        $slug = $this->generateSlugForArticle($article);
        $article->setSlug($slug);

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    // Génère un slug pour un nouvel article
    public function generateSlugForNewArticle(string $title): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        return $this->slugService->slugify($title);
    }


    // Met à jour le slug d'un article existant

    public function updateSlugForExistingArticle(Article $article): void
    {
        $this->updateArticleSlug($article);
    }


    // Génère un slug basé sur le titre et le nom du provider

    public function generateSlugWithProvider(string $title, string $providerFirstName, string $providerLastName): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $titleSlug = $this->slugService->slugify($title);
        $providerSlug = $this->slugService->slugify($providerFirstName . '-' . $providerLastName);

        return $titleSlug . '-' . $providerSlug;
    }
}
