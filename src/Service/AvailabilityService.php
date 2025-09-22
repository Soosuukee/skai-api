<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AvailabilitySlot;
use App\Entity\Provider;
use App\Repository\AvailabilitySlotRepository;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;

class AvailabilityService
{
    public function __construct(
        private AvailabilitySlotRepository $availabilitySlotRepository,
        private ProviderRepository $providerRepository,
        private EntityManagerInterface $entityManager
    ) {}

    // Crée un nouveau slot de disponibilité
    public function createSlot(int $providerId, string $startTime, string $endTime): AvailabilitySlot
    {
        $provider = $this->providerRepository->find($providerId);
        if (!$provider) {
            throw new \InvalidArgumentException('Provider non trouvé');
        }

        $slot = new AvailabilitySlot();
        $slot->setProvider($provider);
        $slot->setStartTime($startTime);
        $slot->setEndTime($endTime);
        $slot->setIsBooked(false);

        $this->entityManager->persist($slot);
        $this->entityManager->flush();

        return $slot;
    }

    // Récupère tous les slots disponibles d'un provider
    public function getAvailableSlots(int $providerId): array
    {
        return $this->availabilitySlotRepository->findAvailableSlots($providerId);
    }

    // Récupère tous les slots d'un provider (disponibles et occupés)
    public function getAllSlots(int $providerId): array
    {
        return $this->availabilitySlotRepository->findByProviderId($providerId);
    }

    // Récupère les slots dans une plage de dates
    public function getSlotsByDateRange(int $providerId, \DateTime $start, \DateTime $end): array
    {
        return $this->availabilitySlotRepository->findByDateRange($providerId, $start, $end);
    }

    // Marque un slot comme occupé
    public function markSlotAsBooked(int $slotId): void
    {
        $slot = $this->availabilitySlotRepository->find($slotId);
        if (!$slot) {
            throw new \InvalidArgumentException('Slot non trouvé');
        }

        $slot->setIsBooked(true);
        $this->entityManager->flush();
    }

    // Libère un slot (le marque comme disponible)
    public function freeSlot(int $slotId): void
    {
        $slot = $this->availabilitySlotRepository->find($slotId);
        if (!$slot) {
            throw new \InvalidArgumentException('Slot non trouvé');
        }

        $slot->setIsBooked(false);
        $this->entityManager->flush();
    }

    // Supprime un slot
    public function deleteSlot(int $slotId): void
    {
        $slot = $this->availabilitySlotRepository->find($slotId);
        if (!$slot) {
            throw new \InvalidArgumentException('Slot non trouvé');
        }

        if ($slot->isBooked()) {
            throw new \InvalidArgumentException('Impossible de supprimer un slot réservé');
        }

        $this->entityManager->remove($slot);
        $this->entityManager->flush();
    }

    // Vérifie si un slot est disponible
    public function isSlotAvailable(int $slotId): bool
    {
        $slot = $this->availabilitySlotRepository->find($slotId);
        return $slot && !$slot->isBooked();
    }
}
