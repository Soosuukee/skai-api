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

    /**
     * Génère un slug pour un article basé sur son titre
     */
    public function generateSlug(Article $article): string
    {
        $title = $article->getTitle();

        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        return $this->slugService->slugifyTitle($title);
    }

    /**
     * Génère un slug unique pour un article
     */
    public function generateUniqueSlug(Article $article): string
    {
        $baseSlug = $this->generateSlug($article);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) use ($article) {
                $existingArticle = $this->articleRepository->findBySlug($slug);
                // Exclure l'article actuel s'il existe déjà
                return $existingArticle !== null && $existingArticle->getId() !== $article->getId();
            }
        );
    }

    /**
     * Met à jour le slug d'un article et le sauvegarde
     */
    public function updateArticleSlug(Article $article): void
    {
        $slug = $this->generateUniqueSlug($article);
        $article->setSlug($slug);

        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    /**
     * Génère un slug pour un nouvel article
     */
    public function generateSlugForNewArticle(string $title): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $baseSlug = $this->slugService->slugifyTitle($title);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->articleRepository->findBySlug($slug) !== null;
            }
        );
    }

    /**
     * Met à jour le slug d'un article existant
     */
    public function updateSlugForExistingArticle(Article $article): void
    {
        $this->updateArticleSlug($article);
    }

    /**
     * Génère un slug basé sur le titre et le nom du provider
     */
    public function generateSlugWithProvider(string $title, string $providerFirstName, string $providerLastName): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $titleSlug = $this->slugService->slugifyTitle($title);
        $providerSlug = $this->slugService->slugify($providerFirstName . '-' . $providerLastName);

        $baseSlug = $titleSlug . '-' . $providerSlug;

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->articleRepository->findBySlug($slug) !== null;
            }
        );
    }
}
