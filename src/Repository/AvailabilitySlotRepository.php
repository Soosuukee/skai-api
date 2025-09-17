<?php

namespace App\Repository;

use App\Entity\AvailabilitySlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AvailabilitySlot>
 */
class AvailabilitySlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AvailabilitySlot::class);
    }

    public function findByProviderId(int $providerId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.provider = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getResult();
    }

    public function findAvailableSlots(int $providerId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.provider = :providerId')
            ->andWhere('a.isBooked = :booked')
            ->setParameter('providerId', $providerId)
            ->setParameter('booked', false)
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(int $providerId, \DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.provider = :providerId')
            ->andWhere('a.startTime >= :start')
            ->andWhere('a.endTime <= :end')
            ->setParameter('providerId', $providerId)
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(\App\Entity\AvailabilitySlot $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(\App\Entity\AvailabilitySlot $entity): void
    {
        $em = $this->getEntityManager();
        $em->flush();
    }

    public function delete(int $id): void
    {
        $entity = $this->find($id);
        if ($entity) {
            $em = $this->getEntityManager();
            $em->remove($entity);
            $em->flush();
        }
    }
}
