<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AvailabilitySlot;
use App\Repository\AvailabilitySlotRepository;
use App\Service\AvailabilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/api/v1/availability-slots', name: 'api_availability_slots_')]
class AvailabilitySlotController extends AbstractController
{
    public function __construct(
        private AvailabilitySlotRepository $availabilitySlotRepository,
        private AvailabilityService $availabilityService,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette ressource');
        }

        // Récupérer les slots selon le type d'utilisateur
        if ($user instanceof \App\Entity\Provider) {
            $slots = $this->availabilitySlotRepository->findBy(['provider' => $user]);
        } elseif ($user instanceof \App\Entity\Client) {
            // Les clients peuvent voir tous les slots disponibles (pour réserver)
            $slots = $this->availabilitySlotRepository->findAvailableSlots();
        } else {
            $slots = [];
        }

        $data = json_decode($this->serializer->serialize($slots, 'json', [
            'groups' => ['availability_slot:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($slots)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Provider) {
            throw new AccessDeniedException('Seuls les providers peuvent créer des slots de disponibilité');
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['startTime']) || !isset($data['endTime'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'startTime et endTime sont requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            $slot = $this->availabilityService->createSlot(
                $user->getId(),
                $data['startTime'],
                $data['endTime']
            );

            $slotData = json_decode($this->serializer->serialize($slot, 'json', [
                'groups' => ['availability_slot:read']
            ]), true);

            return new JsonResponse([
                'success' => true,
                'data' => $slotData,
                'message' => 'Slot créé avec succès'
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

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Provider) {
            throw new AccessDeniedException('Seuls les providers peuvent modifier des slots');
        }

        try {
            $slot = $this->availabilitySlotRepository->find($id);
            if (!$slot) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Slot non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Vérifier que le slot appartient au provider
            if ($slot->getProvider() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez pas modifier ce slot');
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['startTime'])) {
                $slot->setStartTime($data['startTime']);
            }
            if (isset($data['endTime'])) {
                $slot->setEndTime($data['endTime']);
            }
            if (isset($data['isBooked'])) {
                $slot->setIsBooked($data['isBooked']);
            }

            $this->entityManager->flush();

            $slotData = json_decode($this->serializer->serialize($slot, 'json', [
                'groups' => ['availability_slot:read']
            ]), true);

            return new JsonResponse([
                'success' => true,
                'data' => $slotData,
                'message' => 'Slot mis à jour avec succès'
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
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Provider) {
            throw new AccessDeniedException('Seuls les providers peuvent supprimer des slots');
        }

        try {
            $slot = $this->availabilitySlotRepository->find($id);
            if (!$slot) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Slot non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Vérifier que le slot appartient au provider
            if ($slot->getProvider() !== $user) {
                throw new AccessDeniedException('Vous ne pouvez pas supprimer ce slot');
            }

            $this->availabilityService->deleteSlot($id);

            return new JsonResponse([
                'success' => true,
                'message' => 'Slot supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/available', name: 'available', methods: ['GET'])]
    public function getAvailableSlots(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour accéder à cette ressource');
        }

        // Récupérer tous les slots disponibles
        $slots = $this->availabilitySlotRepository->findAvailableSlots();

        $data = json_decode($this->serializer->serialize($slots, 'json', [
            'groups' => ['availability_slot:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($slots)
        ]);
    }
}

