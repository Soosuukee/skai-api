<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/articles', name: 'api_articles_')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private FileUploadService $fileUploadService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $articles = $this->articleRepository->findAll();

        $data = array_map(function (Article $a) {
            // Build nested sections -> contents -> images to match types
            $sectionsData = [];
            $sectionRepo = $this->entityManager->getRepository(\App\Entity\ArticleSection::class);
            $contentRepo = $this->entityManager->getRepository(\App\Entity\ArticleContent::class);
            $imageRepo = $this->entityManager->getRepository(\App\Entity\ArticleImage::class);

            $sections = $sectionRepo->findBy(['article' => $a]);
            foreach ($sections as $section) {
                $contentsData = [];
                $contents = $contentRepo->findBy(['articleSection' => $section]);
                foreach ($contents as $content) {
                    $imagesData = [];
                    $images = $imageRepo->findBy(['articleContent' => $content]);
                    foreach ($images as $image) {
                        $imagesData[] = [
                            'articleImageId' => $image->getId(),
                            'articleContentId' => $content->getId(),
                            'Url' => $image->getUrl(),
                        ];
                    }
                    $contentsData[] = [
                        'articleContentId' => $content->getId(),
                        'articleSectionId' => $section->getId(),
                        'content' => (string) $content->getContent(),
                        'images' => $imagesData,
                    ];
                }
                $sectionsData[] = [
                    'articleSectionId' => $section->getId(),
                    'articleId' => $a->getId(),
                    'title' => (string) $section->getTitle(),
                    'content' => $contentsData,
                ];
            }

            return [
                'articleId'   => $a->getId(),
                'providerId'  => $a->getProvider()?->getId(),
                'languageId'  => $a->getLanguage()?->getId(),
                'title'       => $a->getTitle(),
                'slug'        => $a->getSlug(),
                'publishedAt' => $a->getPublishedAt()?->format(DATE_ATOM),
                'summary'     => $a->getSummary(),
                'isPublished' => (bool) $a->isPublished(),
                'isFeatured'  => (bool) $a->isFeatured(),
                'cover'       => $a->getCover(),
                'tags'        => array_map(fn($t) => $t->getTitle(), $a->getTags()->toArray()),
                'sections'    => $sectionsData,
            ];
        }, $articles);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($articles)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id): JsonResponse
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Build nested sections -> contents -> images
        $sectionsData = [];
        $sectionRepo = $this->entityManager->getRepository(\App\Entity\ArticleSection::class);
        $contentRepo = $this->entityManager->getRepository(\App\Entity\ArticleContent::class);
        $imageRepo = $this->entityManager->getRepository(\App\Entity\ArticleImage::class);

        $sections = $sectionRepo->findBy(['article' => $article]);
        foreach ($sections as $section) {
            $contentsData = [];
            $contents = $contentRepo->findBy(['articleSection' => $section]);
            foreach ($contents as $content) {
                $imagesData = [];
                $images = $imageRepo->findBy(['articleContent' => $content]);
                foreach ($images as $image) {
                    $imagesData[] = [
                        'articleImageId' => $image->getId(),
                        'articleContentId' => $content->getId(),
                        'Url' => $image->getUrl(),
                    ];
                }
                $contentsData[] = [
                    'articleContentId' => $content->getId(),
                    'articleSectionId' => $section->getId(),
                    'content' => (string) $content->getContent(),
                    'images' => $imagesData,
                ];
            }
            $sectionsData[] = [
                'articleSectionId' => $section->getId(),
                'articleId' => $article->getId(),
                'title' => (string) $section->getTitle(),
                'content' => $contentsData,
            ];
        }

        $data = [
            'articleId'   => $article->getId(),
            'providerId'  => $article->getProvider()?->getId(),
            'languageId'  => $article->getLanguage()?->getId(),
            'title'       => $article->getTitle(),
            'slug'        => $article->getSlug(),
            'publishedAt' => $article->getPublishedAt()?->format(DATE_ATOM),
            'summary'     => $article->getSummary(),
            'isPublished' => (bool) $article->isPublished(),
            'isFeatured'  => (bool) $article->isFeatured(),
            'cover'       => $article->getCover(),
            'tags'        => array_map(fn($t) => $t->getTitle(), $article->getTags()->toArray()),
            'sections'    => $sectionsData,
        ];
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }



    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $article = new Article();
            $article->setTitle($data['title'] ?? '');
            $article->setSummary($data['summary'] ?? '');
            $article->setIsPublished($data['isPublished'] ?? false);
            $article->setIsFeatured($data['isFeatured'] ?? false);
            $article->setUpdatedAt(new \DateTimeImmutable());

            // Set published date if published
            if ($article->isPublished()) {
                $article->setPublishedAt(new \DateTimeImmutable());
            }

            // Set relations if provided
            if (isset($data['providerId'])) {
                // TODO: Set provider relation
            }
            if (isset($data['languageId'])) {
                // TODO: Set language relation
            }

            // Validate entity
            $errors = $this->validator->validate($article);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $article,
                'message' => 'Article created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update route moved to ProviderController with provider slug

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($article);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Article deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/images', name: 'upload_images', methods: ['POST'])]
    public function uploadImages(int $id, Request $request): JsonResponse
    {
        $article = $this->articleRepository->findById($id);

        if (!$article) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Article not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $uploadedFiles = $request->files->all();

        if (empty($uploadedFiles)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No files uploaded'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $uploadedUrls = [];
            $providerId = $article->getProvider()?->getId();

            if (!$providerId) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Article provider not found'
                ], Response::HTTP_BAD_REQUEST);
            }

            foreach ($uploadedFiles as $key => $file) {
                // Determine if it's a cover image or content image
                if (strpos($key, 'cover') !== false) {
                    $fileUrl = $this->fileUploadService->uploadArticleCover(
                        $file->getPathname(),
                        $providerId,
                        $article->getId()
                    );
                    $article->setCover($fileUrl);
                } else {
                    // For content images, we need section and content IDs
                    // This would typically come from the request data
                    $sectionId = $request->request->get('sectionId', 1);
                    $contentId = $request->request->get('contentId', 1);

                    $fileUrl = $this->fileUploadService->uploadArticleImage(
                        $file->getPathname(),
                        $providerId,
                        $article->getId(),
                        (int) $sectionId,
                        (int) $contentId
                    );
                }

                $uploadedUrls[] = $fileUrl;
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'uploaded_urls' => $uploadedUrls
                ],
                'message' => 'Images uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
