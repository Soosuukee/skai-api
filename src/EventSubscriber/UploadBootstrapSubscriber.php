<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Config\UploadConfig;
use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Provider;
use App\Entity\Service;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

/**
 * Creates expected upload directories as soon as entities are persisted,
 * so front-end integrations can rely on folder existence before first upload.
 */
class UploadBootstrapSubscriber implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [Events::postPersist];
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        // Base directory bootstrap for Provider and Client
        if ($entity instanceof Provider && null !== $entity->getId()) {
            $providerId = (int) $entity->getId();
            // Mirror fixtures structure under public/images/providers/{id}
            $this->ensureDirectory(UploadConfig::getProviderProfilePicturePath($providerId)); // /profile/
            $this->ensureDirectory("images/providers/{$providerId}/services/");
            $this->ensureDirectory("images/providers/{$providerId}/articles/");
            $this->ensureDirectory("images/providers/{$providerId}/experiences/");
            $this->ensureDirectory("images/providers/{$providerId}/education/");
            $this->ensureDirectory("images/providers/{$providerId}/completed-work/");
            return;
        }

        if ($entity instanceof Client && null !== $entity->getId()) {
            $this->ensureDirectory(UploadConfig::getClientProfilePicturePath($entity->getId())); // /profile/
            return;
        }

        // Service folders (cover folder at minimum)
        if ($entity instanceof Service && null !== $entity->getId() && null !== $entity->getProvider()?->getId()) {
            $providerId = (int) $entity->getProvider()->getId();
            $serviceId = (int) $entity->getId();
            $this->ensureDirectory(UploadConfig::getServiceCoverPath($providerId, $serviceId));
            return;
        }

        // Article folders (cover folder at minimum)
        if ($entity instanceof Article && null !== $entity->getId() && null !== $entity->getProvider()?->getId()) {
            $providerId = (int) $entity->getProvider()->getId();
            $articleId = (int) $entity->getId();
            $this->ensureDirectory(UploadConfig::getArticleCoverPath($providerId, $articleId));
            return;
        }

        // Experience folder
        if ($entity instanceof Experience && null !== $entity->getId() && null !== $entity->getProvider()?->getId()) {
            $providerId = (int) $entity->getProvider()->getId();
            $experienceId = (int) $entity->getId();
            $this->ensureDirectory(UploadConfig::getExperiencePath($providerId, $experienceId));
            return;
        }

        // Education folder
        if ($entity instanceof Education && null !== $entity->getId() && null !== $entity->getProvider()?->getId()) {
            $providerId = (int) $entity->getProvider()->getId();
            $educationId = (int) $entity->getId();
            $this->ensureDirectory(UploadConfig::getEducationPath($providerId, $educationId));
            return;
        }
    }

    private function ensureDirectory(string $relativeDirectory): void
    {
        $absolutePath = UploadConfig::getUploadPath($relativeDirectory);
        if (!is_dir($absolutePath)) {
            @mkdir($absolutePath, 0755, true);
        }
    }
}
