<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Config\UploadConfig;
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::preRemove)]
class ArticleUploadCleanupListener
{
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article) {
            return;
        }

        $providerId = $entity->getProvider()?->getId();
        $articleId = $entity->getId();
        if ($providerId === null || $articleId === null) {
            return;
        }

        // Dossier de cover
        $relativeCoverDir = UploadConfig::getArticleCoverPath((int)$providerId, (int)$articleId);
        $this->removeRelativeDirectoryRecursively($relativeCoverDir);

        // Dossiers d'images de contenu: images/providers/{pid}/article/{aid}/**
        $baseArticleDir = "images/providers/{$providerId}/article/{$articleId}/";
        $this->removeRelativeDirectoryRecursively($baseArticleDir);
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
