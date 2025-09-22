<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;
use App\Repository\AvailabilitySlotRepository;
use App\Service\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/api/v1/bookings', name: 'api_bookings_')]
class BookingController extends AbstractController
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private BookingService $bookingService,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette ressource');
        }

        // Récupérer les bookings selon le type d'utilisateur
        if ($user instanceof \App\Entity\Client) {
            $bookings = $this->bookingRepository->findBy(['client' => $user]);
        } elseif ($user instanceof \App\Entity\Provider) {
            // Pour les providers, récupérer les bookings de leurs slots
            $bookings = $this->bookingRepository->findByProvider($user);
        } else {
            $bookings = [];
        }

        $data = json_decode($this->serializer->serialize($bookings, 'json', [
            'groups' => ['booking:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($bookings)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Client) {
            throw new AccessDeniedException('Seuls les clients peuvent créer des bookings');
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['slotId'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'slotId est requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            $booking = $this->bookingService->createBooking(
                $user->getId(),
                $data['slotId']
            );

            $bookingData = json_decode($this->serializer->serialize($booking, 'json', [
                'groups' => ['booking:read']
            ]), true);

            return new JsonResponse([
                'success' => true,
                'data' => $bookingData,
                'message' => 'Booking créé avec succès'
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/confirm', name: 'confirm', methods: ['POST'])]
    public function confirm(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Provider) {
            throw new AccessDeniedException('Seuls les providers peuvent confirmer des bookings');
        }

        try {
            $booking = $this->bookingService->confirmBooking($id);

            // Vérifier que le booking appartient au provider
            if ($booking->getSlot()->getProvider() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez pas confirmer ce booking');
            }

            $bookingData = json_decode($this->serializer->serialize($booking, 'json', [
                'groups' => ['booking:read']
            ]), true);

            return new JsonResponse([
                'success' => true,
                'data' => $bookingData,
                'message' => 'Booking confirmé avec succès'
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Provider) {
            throw new AccessDeniedException('Seuls les providers peuvent rejeter des bookings');
        }

        try {
            $booking = $this->bookingService->rejectBooking($id);

            // Vérifier que le booking appartient au provider
            if ($booking->getSlot()->getProvider() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez pas rejeter ce booking');
            }

            $bookingData = json_decode($this->serializer->serialize($booking, 'json', [
                'groups' => ['booking:read']
            ]), true);

            return new JsonResponse([
                'success' => true,
                'data' => $bookingData,
                'message' => 'Booking rejeté avec succès'
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'cancel', methods: ['DELETE'])]
    public function cancel(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour annuler un booking');
        }

        try {
            $booking = $this->bookingRepository->find($id);
            if (!$booking) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Booking non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Vérifier que l'utilisateur peut annuler ce booking
            if ($user instanceof \App\Entity\Client && $booking->getClient() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez pas annuler ce booking');
            }
            if ($user instanceof \App\Entity\Provider && $booking->getSlot()->getProvider() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez pas annuler ce booking');
            }

            $this->bookingService->cancelBooking($id);

            return new JsonResponse([
                'success' => true,
                'message' => 'Booking annulé avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

