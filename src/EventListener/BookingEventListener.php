<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Booking;
use App\Entity\Enum\BookingStatus;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::preUpdate)]
class BookingEventListener
{
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Booking) {
            $slot = $entity->getSlot();
            if ($slot) {
                $slot->setIsBooked(false);
                $args->getObjectManager()->persist($slot);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Booking) {
            $slot = $entity->getSlot();
            if ($slot && $args->hasChangedField('status')) {
                $newStatus = $entity->getStatus();

                if ($newStatus === BookingStatus::ACCEPTED) {
                    $slot->setIsBooked(true);
                    $args->getObjectManager()->persist($slot);
                } elseif ($newStatus === BookingStatus::DECLINED) {
                    $slot->setIsBooked(false);
                    $args->getObjectManager()->persist($slot);
                }
            }
        }
    }
}
