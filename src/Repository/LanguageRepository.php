<?php

namespace App\Repository;

use App\Entity\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Language>
 */
class LanguageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Language::class);
    }

    public function findByName(string $name): ?Language
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(\App\Entity\Language $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(\App\Entity\Language $entity): void
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
