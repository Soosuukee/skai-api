<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findByEmail(string $email): ?Client
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySlug(string $slug): ?Client
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCountryId(int $countryId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.country = :countryId')
            ->setParameter('countryId', $countryId)
            ->getQuery()
            ->getResult();
    }

    public function findByCity(string $city): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Client
    {
        return $this->find($id);
    }

    public function findByCountryName(string $countryName): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.country', 'co')
            ->andWhere('co.name = :countryName')
            ->setParameter('countryName', $countryName)
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(Client $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(Client $entity): void
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
