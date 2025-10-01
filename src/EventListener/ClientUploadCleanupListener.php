<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Config\UploadConfig;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::preRemove)]
class ClientUploadCleanupListener
{
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Client) {
            return;
        }

        $clientId = $entity->getId();
        if ($clientId === null) {
            return;
        }

        // Supprimer tout le dossier du client: images/clients/{clientId}/
        $relativeDir = "images/clients/{$clientId}/";
        $this->removeRelativeDirectoryRecursively($relativeDir);
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
