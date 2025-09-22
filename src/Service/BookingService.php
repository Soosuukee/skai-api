<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Booking;
use App\Entity\AvailabilitySlot;
use App\Entity\Client;
use App\Repository\BookingRepository;
use App\Repository\AvailabilitySlotRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Enum\BookingStatus;

class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private AvailabilitySlotRepository $availabilitySlotRepository,
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManager
    ) {}

    // Crée un nouveau booking
    public function createBooking(int $clientId, int $slotId, BookingStatus $status = BookingStatus::PENDING): Booking
    {
        $client = $this->clientRepository->find($clientId);
        if (!$client) {
            throw new \InvalidArgumentException('Client non trouvé');
        }

        $slot = $this->availabilitySlotRepository->find($slotId);
        if (!$slot) {
            throw new \InvalidArgumentException('Slot de disponibilité non trouvé');
        }

        if ($slot->isBooked()) {
            throw new \InvalidArgumentException('Ce slot est déjà réservé');
        }

        $booking = new Booking();
        $booking->setClient($client);
        $booking->setSlot($slot);
        $booking->setStatus($status);
        $booking->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }

    // Annule un booking
    public function cancelBooking(int $bookingId): void
    {
        $booking = $this->bookingRepository->find($bookingId);
        if (!$booking) {
            throw new \InvalidArgumentException('Booking non trouvé');
        }

        $this->entityManager->remove($booking);
        $this->entityManager->flush();
    }

    // Met à jour le statut d'un booking
    public function updateBookingStatus(int $bookingId, BookingStatus $status): Booking
    {
        $booking = $this->bookingRepository->find($bookingId);
        if (!$booking) {
            throw new \InvalidArgumentException('Booking non trouvé');
        }

        $booking->setStatus($status);
        $this->entityManager->flush();

        return $booking;
    }

    // Vérifie si un slot est disponible
    public function isSlotAvailable(int $slotId): bool
    {
        $slot = $this->availabilitySlotRepository->find($slotId);
        return $slot && !$slot->isBooked();
    }

    // Récupère tous les bookings d'un client
    public function getClientBookings(int $clientId): array
    {
        return $this->bookingRepository->findByClientId($clientId);
    }

    // Récupère tous les bookings d'un slot
    public function getSlotBookings(int $slotId): array
    {
        $booking = $this->bookingRepository->findBySlotId($slotId);
        return $booking ? [$booking] : [];
    }

    // Confirme un booking (change le statut en ACCEPTED)
    public function confirmBooking(int $bookingId): Booking
    {
        return $this->updateBookingStatus($bookingId, BookingStatus::ACCEPTED);
    }

    // Refuse un booking (change le statut en DECLINED)
    public function rejectBooking(int $bookingId): Booking
    {
        return $this->updateBookingStatus($bookingId, BookingStatus::DECLINED);
    }
}
