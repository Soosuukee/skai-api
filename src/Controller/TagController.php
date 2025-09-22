<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/tags', name: 'api_tags_')]
class TagController extends AbstractController
{
    public function __construct(
        private TagRepository $tagRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $tags = $this->tagRepository->findAll();

        $json = $this->serializer->serialize($tags, 'json', ['groups' => ['tag:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true),
            'total' => count($tags)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Tag not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true)
        ]);
    }

    #[Route('/slug/{slug}', name: 'show_by_slug', methods: ['GET'])]
    public function showBySlug(string $slug): JsonResponse
    {
        $tag = $this->tagRepository->findBySlug($slug);

        if (!$tag) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Tag not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($tag, 'json', ['groups' => ['tag:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true)
        ]);
    }
}
