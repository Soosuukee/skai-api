<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findByRecipientId(int $recipientId, string $recipientType): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.recipientId = :recipientId')
            ->andWhere('n.recipientType = :recipientType')
            ->setParameter('recipientId', $recipientId)
            ->setParameter('recipientType', $recipientType)
            ->getQuery()
            ->getResult();
    }

    public function findUnreadByRecipientId(int $recipientId, string $recipientType): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.recipientId = :recipientId')
            ->andWhere('n.recipientType = :recipientType')
            ->andWhere('n.isRead = :read')
            ->setParameter('recipientId', $recipientId)
            ->setParameter('recipientType', $recipientType)
            ->setParameter('read', false)
            ->getQuery()
            ->getResult();
    }

    public function markAsRead(int $notificationId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', ':read')
            ->andWhere('n.id = :id')
            ->setParameter('read', true)
            ->setParameter('id', $notificationId)
            ->getQuery()
            ->execute();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(\App\Entity\Notification $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(\App\Entity\Notification $entity): void
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
