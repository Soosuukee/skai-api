<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/languages', name: 'api_languages_')]
class LanguageController extends AbstractController
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $languages = $this->languageRepository->findAll();

        $json = $this->serializer->serialize($languages, 'json', ['groups' => ['language:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true),
            'total' => count($languages)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $language = $this->languageRepository->find($id);

        if (!$language) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Language not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($language, 'json', ['groups' => ['language:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true)
        ]);
    }
}
