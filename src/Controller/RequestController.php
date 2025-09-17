<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Request;
use App\Repository\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/requests', name: 'api_requests_')]
class RequestController extends AbstractController
{
    public function __construct(
        private RequestRepository $requestRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $requests = $this->requestRepository->findAll();

        return new JsonResponse([
            'success' => true,
            'data' => $requests,
            'total' => count($requests)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $request = $this->requestRepository->find($id);

        if (!$request) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Request not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'data' => $request
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(HttpRequest $httpRequest): JsonResponse
    {
        try {
            $data = json_decode($httpRequest->getContent(), true);

            $request = new Request();
            $request->setTitle($data['title'] ?? '');
            $request->setDescription($data['description'] ?? '');
            $request->setStatus($data['status'] ?? 'pending');
            $request->setCreatedAt(new \DateTimeImmutable());

            // Set relations if provided
            if (isset($data['clientId'])) {
                // TODO: Set client relation
            }
            if (isset($data['providerId'])) {
                // TODO: Set provider relation
            }

            // Validate entity
            $errors = $this->validator->validate($request);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($request);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $request,
                'message' => 'Request created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, HttpRequest $httpRequest): JsonResponse
    {
        $request = $this->requestRepository->find($id);

        if (!$request) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Request not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($httpRequest->getContent(), true);

            if (isset($data['title'])) $request->setTitle($data['title']);
            if (isset($data['description'])) $request->setDescription($data['description']);
            if (isset($data['status'])) $request->setStatus($data['status']);

            // Validate entity
            $errors = $this->validator->validate($request);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $request,
                'message' => 'Request updated successfully'
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
        $request = $this->requestRepository->find($id);

        if (!$request) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Request not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($request);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
