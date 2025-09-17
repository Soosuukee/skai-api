<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/bookings', name: 'api_bookings_')]
class BookingController extends AbstractController
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $bookings = $this->bookingRepository->findAll();

        return new JsonResponse([
            'success' => true,
            'data' => $bookings,
            'total' => count($bookings)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->find($id);

        if (!$booking) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Booking not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'data' => $booking
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $booking = new Booking();
            $booking->setStatus($data['status'] ?? 'pending');
            $booking->setCreatedAt(new \DateTimeImmutable());

            // Set relations if provided
            if (isset($data['clientId'])) {
                // TODO: Set client relation
            }
            if (isset($data['slotId'])) {
                // TODO: Set slot relation
            }

            // Validate entity
            $errors = $this->validator->validate($booking);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($booking);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking created successfully'
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
        $booking = $this->bookingRepository->find($id);

        if (!$booking) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Booking not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['status'])) $booking->setStatus($data['status']);

            // Validate entity
            $errors = $this->validator->validate($booking);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $booking,
                'message' => 'Booking updated successfully'
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
        $booking = $this->bookingRepository->find($id);

        if (!$booking) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Booking not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($booking);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Booking deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
