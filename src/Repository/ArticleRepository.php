<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findByProviderId(int $providerId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.provider = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPublishedArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :published')
            ->setParameter('published', true)
            ->getQuery()
            ->getResult();
    }

    public function findFeaturedArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isFeatured = :featured')
            ->setParameter('featured', true)
            ->getQuery()
            ->getResult();
    }

    public function findByLanguageId(int $languageId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.language = :languageId')
            ->setParameter('languageId', $languageId)
            ->getQuery()
            ->getResult();
    }

    public function searchArticles(string $query): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.title LIKE :query OR a.summary LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Article
    {
        return $this->find($id);
    }

    public function findByProviderSlug(string $providerSlug): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.provider', 'p')
            ->andWhere('p.slug = :providerSlug')
            ->setParameter('providerSlug', $providerSlug)
            ->getQuery()
            ->getResult();
    }

    public function findByTagId(int $tagId): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.tags', 't')
            ->andWhere('t.id = :tagId')
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getResult();
    }

    public function findByTagSlug(string $tagSlug): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.tags', 't')
            ->andWhere('t.slug = :tagSlug')
            ->setParameter('tagSlug', $tagSlug)
            ->getQuery()
            ->getResult();
    }

    public function getArticleWithContent(int $articleId): ?array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.sections', 's')
            ->leftJoin('s.contents', 'c')
            ->leftJoin('c.images', 'i')
            ->addSelect('s', 'c', 'i')
            ->andWhere('a.id = :articleId')
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function saveArticleWithContent(Article $article, array $sections): void
    {
        $em = $this->getEntityManager();
        $em->persist($article);

        foreach ($sections as $sectionData) {
            $section = $sectionData['section'];
            $contents = $sectionData['contents'] ?? [];

            $section->setArticle($article);
            $em->persist($section);

            foreach ($contents as $contentData) {
                $content = $contentData['content'];
                $images = $contentData['images'] ?? [];

                $content->setArticleSection($section);
                $em->persist($content);

                foreach ($images as $image) {
                    $image->setArticleContent($content);
                    $em->persist($image);
                }
            }
        }

        $em->flush();
    }

    public function findArticleImageById(int $imageId): ?array
    {
        return $this->getEntityManager()
            ->getRepository(\App\Entity\ArticleImage::class)
            ->createQueryBuilder('ai')
            ->innerJoin('ai.articleContent', 'ac')
            ->innerJoin('ac.articleSection', 'as')
            ->innerJoin('as.article', 'a')
            ->addSelect('ac', 'as', 'a')
            ->andWhere('ai.id = :imageId')
            ->setParameter('imageId', $imageId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteArticleImageById(int $imageId): void
    {
        $image = $this->getEntityManager()
            ->getRepository(\App\Entity\ArticleImage::class)
            ->find($imageId);

        if ($image) {
            $em = $this->getEntityManager();
            $em->remove($image);
            $em->flush();
        }
    }

    public function findArticleIdByContentId(int $contentId): ?int
    {
        $result = $this->createQueryBuilder('a')
            ->innerJoin('a.sections', 's')
            ->innerJoin('s.contents', 'c')
            ->select('a.id')
            ->andWhere('c.id = :contentId')
            ->setParameter('contentId', $contentId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['id'] : null;
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(Article $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(Article $entity): void
    {
        $em = $this->getEntityManager();
        $em->flush();
    }

    public function delete(int $id): void
    {
        $entity = $this->find($id);
        if ($entity) {
            $em = $this->getEntityManager();
            $em->remove($entity);
            $em->flush();
        }
    }
}
