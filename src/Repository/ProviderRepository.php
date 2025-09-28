<?php

namespace App\Repository;

use App\Entity\Provider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Provider>
 */
class ProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Provider::class);
    }

    public function findByEmail(string $email): ?Provider
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySlug(string $slug): ?Provider
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByJobId(int $jobId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.job = :jobId')
            ->setParameter('jobId', $jobId)
            ->getQuery()
            ->getResult();
    }

    public function findByCountryId(int $countryId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.country = :countryId')
            ->setParameter('countryId', $countryId)
            ->getQuery()
            ->getResult();
    }

    public function findByCity(string $city): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.city = :city')
            ->setParameter('city', $city)
            ->getQuery()
            ->getResult();
    }

    public function findActiveProviders(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.services', 's')
            ->andWhere('s.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    public function searchProviders(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.firstName LIKE :query OR p.lastName LIKE :query OR p.email LIKE :query OR p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Provider
    {
        return $this->find($id);
    }

    public function findByJobSlug(string $jobSlug): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.job', 'j')
            ->andWhere('j.slug = :jobSlug')
            ->setParameter('jobSlug', $jobSlug)
            ->getQuery()
            ->getResult();
    }

    public function findByCountryName(string $countryName): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.country', 'c')
            ->andWhere('c.name = :countryName')
            ->setParameter('countryName', $countryName)
            ->getQuery()
            ->getResult();
    }

    public function getProviderWithSkills(int $providerId): ?array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.hardSkills', 'hs')
            ->leftJoin('p.softSkills', 'ss')
            ->leftJoin('p.languages', 'l')
            ->addSelect('hs', 'ss', 'l')
            ->andWhere('p.id = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getProviderWithPortfolio(int $providerId): ?array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.experiences', 'e')
            ->leftJoin('p.educations', 'ed')
            ->leftJoin('p.completedWorks', 'cw')
            ->leftJoin('cw.medias', 'cwm')
            ->addSelect('e', 'ed', 'cw', 'cwm')
            ->andWhere('p.id = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(Provider $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(Provider $entity): void
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
