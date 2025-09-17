<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/reviews', name: 'api_reviews_')]
class ReviewController extends AbstractController
{
    public function __construct(
        private ReviewRepository $reviewRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $reviews = $this->reviewRepository->findAll();

        return new JsonResponse([
            'success' => true,
            'data' => $reviews,
            'total' => count($reviews)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $review = $this->reviewRepository->find($id);

        if (!$review) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Review not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'data' => $review
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $review = new Review();
            $review->setComment($data['comment'] ?? '');
            $review->setRating($data['rating'] ?? 0);
            $review->setCreatedAt(new \DateTimeImmutable());

            // Set relations if provided
            if (isset($data['clientId'])) {
                // TODO: Set client relation
            }
            if (isset($data['providerId'])) {
                // TODO: Set provider relation
            }

            // Validate entity
            $errors = $this->validator->validate($review);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($review);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $review,
                'message' => 'Review created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $review = $this->reviewRepository->find($id);

        if (!$review) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Review not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['comment'])) $review->setComment($data['comment']);
            if (isset($data['rating'])) $review->setRating($data['rating']);

            // Validate entity
            $errors = $this->validator->validate($review);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $review,
                'message' => 'Review updated successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $review = $this->reviewRepository->find($id);

        if (!$review) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Review not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($review);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
