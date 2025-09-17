<?php

namespace App\Repository;

use App\Entity\HardSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HardSkill>
 */
class HardSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HardSkill::class);
    }

    public function findByTitle(string $title): ?HardSkill
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function searchSkills(string $query): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(\App\Entity\HardSkill $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(\App\Entity\HardSkill $entity): void
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
