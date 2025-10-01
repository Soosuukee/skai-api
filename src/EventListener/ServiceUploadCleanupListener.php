<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Config\UploadConfig;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::preRemove)]
class ServiceUploadCleanupListener
{
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Service) {
            return;
        }

        $providerId = $entity->getProvider()?->getId();
        $serviceId = $entity->getId();
        if ($providerId === null || $serviceId === null) {
            return;
        }

        // Dossier de cover du service
        $relativeCoverDir = UploadConfig::getServiceCoverPath((int)$providerId, (int)$serviceId);
        $this->removeRelativeDirectoryRecursively($relativeCoverDir);

        // Dossiers d'images de contenu: images/providers/{pid}/service/{sid}/**
        $baseServiceDir = "images/providers/{$providerId}/service/{$serviceId}/";
        $this->removeRelativeDirectoryRecursively($baseServiceDir);
    }

    private function removeRelativeDirectoryRecursively(string $relativeDirectory): void
    {
        $absolutePath = UploadConfig::getUploadPath($relativeDirectory);
        if (!is_dir($absolutePath)) {
            return;
        }

        $items = scandir($absolutePath);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $absolutePath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeRelativeDirectoryRecursively(
                    rtrim($relativeDirectory, '/\\') . '/' . $item . '/'
                );
            } else {
                @unlink($path);
            }
        }

        @rmdir($absolutePath);
    }
}
