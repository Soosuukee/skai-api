<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByProviderId(int $providerId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.provider = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getResult();
    }

    public function findByClientId(int $clientId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();
    }

    public function getAverageRating(int $providerId): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(CAST(r.rating AS DECIMAL)) as avgRating')
            ->andWhere('r.provider = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    public function getRatingCount(int $providerId): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.provider = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(\App\Entity\Review $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(\App\Entity\Review $entity): void
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
