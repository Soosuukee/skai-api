<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\LanguageRepository;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/v1/articles', name: 'api_articles_')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private ProviderRepository $providerRepository,
        private LanguageRepository $languageRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $articles = $this->articleRepository->findAll();

        $data = json_decode($this->serializer->serialize($articles, 'json', [
            'groups' => ['article:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($articles)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof \App\Entity\Provider)) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $article = new Article();
        $article->setProvider($user);

        $languageId = (int)($data['languageId'] ?? 0);
        $language = $languageId ? $this->languageRepository->find($languageId) : null;
        if (!$language) {
            return new JsonResponse(['success' => false, 'error' => 'languageId is required'], Response::HTTP_BAD_REQUEST);
        }
        $article->setLanguage($language);

        $article->setTitle((string)($data['title'] ?? ''));
        $article->setSummary((string)($data['summary'] ?? ''));
        $article->setIsPublished((bool)($data['isPublished'] ?? false));
        $article->setIsFeatured((bool)($data['isFeatured'] ?? false));
        if (!empty($data['cover'])) {
            $article->setCover((string)$data['cover']);
        }

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return new JsonResponse(['success' => false, 'errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        $payload = json_decode($this->serializer->serialize($article, 'json', ['groups' => ['article:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $payload], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof \App\Entity\Provider)) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $article = $this->articleRepository->find($id);
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        if ($article->getProvider()?->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if (isset($data['title'])) $article->setTitle((string)$data['title']);
        if (isset($data['summary'])) $article->setSummary((string)$data['summary']);
        if (array_key_exists('isPublished', $data)) $article->setIsPublished((bool)$data['isPublished']);
        if (array_key_exists('isFeatured', $data)) $article->setIsFeatured((bool)$data['isFeatured']);
        if (isset($data['cover'])) $article->setCover((string)$data['cover']);
        if (isset($data['languageId'])) {
            $language = $this->languageRepository->find((int)$data['languageId']);
            if ($language) $article->setLanguage($language);
        }

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return new JsonResponse(['success' => false, 'errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->flush();

        $payload = json_decode($this->serializer->serialize($article, 'json', ['groups' => ['article:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $payload]);
    }

    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    public function patch(int $id, Request $request): JsonResponse
    {
        return $this->update($id, $request);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof \App\Entity\Provider)) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        $article = $this->articleRepository->find($id);
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }
        if ($article->getProvider()?->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        $data = json_decode($request->getContent(), true) ?? [];
        $currentPassword = (string)($data['currentPassword'] ?? '');
        if ($currentPassword === '') {
            return new JsonResponse(['success' => false, 'error' => 'currentPassword is required'], Response::HTTP_BAD_REQUEST);
        }
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true, 'message' => 'Article deleted']);
    }
}
