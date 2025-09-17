<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Service d'aide pour copier des images depuis /fixtures_images vers l'arborescence publique
 * pour un provider (profile, services, articles, experiences, education).
 */
class ProviderImageService
{
    private const PUBLIC_BASE = __DIR__ . '/../../public/images/providers';
    private const FIXTURES_BASE = __DIR__ . '/../../fixtures_images/providers';

    /**
     * Crée la structure de base et copie l'image de profil si elle existe.
     * Retourne l'URL publique si une image a été copiée, sinon null.
     */
    public function createProviderImageStructure(int $providerId, string $profilePicture): ?string
    {
        $baseDir = self::PUBLIC_BASE . '/' . $providerId;

        $directories = [
            $baseDir . '/profile',
            $baseDir . '/services',
            $baseDir . '/articles',
            $baseDir . '/experiences',
            $baseDir . '/education',
        ];
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Copier l'image de profil
        $sourceProfile = self::FIXTURES_BASE . '/profilepicture/' . $profilePicture;
        if (!is_file($sourceProfile)) {
            return null;
        }

        $extension = strtolower(pathinfo($profilePicture, PATHINFO_EXTENSION));
        $dest = $baseDir . '/profile/profile-picture.' . $extension;
        copy($sourceProfile, $dest);

        return '/images/providers/' . $providerId . '/profile/profile-picture.' . $extension;
    }

    /**
     * Copie une cover de service depuis les fixtures vers:
     * public/images/providers/{providerId}/services/{serviceId}/cover/service-cover.{ext}
     * Retourne l'URL publique ou null si le fichier source n'existe pas.
     */
    public function copyFixtureServiceCover(int $providerId, int $serviceId, string $fixtureFilename): ?string
    {
        $source = self::FIXTURES_BASE . '/services/' . $fixtureFilename;
        if (!is_file($source)) {
            return null;
        }

        $extension = strtolower(pathinfo($fixtureFilename, PATHINFO_EXTENSION));
        $destDir = self::PUBLIC_BASE . '/' . $providerId . '/services/' . $serviceId . '/cover';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/service-cover.' . $extension;
        copy($source, $dest);

        return '/images/providers/' . $providerId . '/services/' . $serviceId . '/cover/service-cover.' . $extension;
    }

    /**
     * Copie une image d'article depuis les fixtures vers:
     * public/images/providers/{providerId}/articles/{articleId}/article-image-1.{ext}
     */
    public function copyFixtureArticleImage(int $providerId, int $articleId, string $fixtureFilename): ?string
    {
        $source = self::FIXTURES_BASE . '/articles/' . $fixtureFilename;
        if (!is_file($source)) {
            return null;
        }

        $extension = strtolower(pathinfo($fixtureFilename, PATHINFO_EXTENSION));
        $destDir = self::PUBLIC_BASE . '/' . $providerId . '/articles/' . $articleId;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/article-image-1.' . $extension;
        copy($source, $dest);

        return '/images/providers/' . $providerId . '/articles/' . $articleId . '/article-image-1.' . $extension;
    }

    /**
     * Copie un logo d'expérience vers:
     * public/images/providers/{providerId}/experiences/{experienceId}/exp{experienceId}.{ext}
     */
    public function copyFixtureExperienceLogo(int $providerId, int $experienceId, string $fixtureFilename): ?string
    {
        $source = self::FIXTURES_BASE . '/experiences/' . $fixtureFilename;
        if (!is_file($source)) {
            return null;
        }

        $extension = strtolower(pathinfo($fixtureFilename, PATHINFO_EXTENSION));
        $destDir = self::PUBLIC_BASE . '/' . $providerId . '/experiences/' . $experienceId;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/exp' . $experienceId . '.' . $extension;
        copy($source, $dest);

        return '/images/providers/' . $providerId . '/experiences/' . $experienceId . '/exp' . $experienceId . '.' . $extension;
    }

    /**
     * Copie un logo d'éducation vers:
     * public/images/providers/{providerId}/education/{educationId}/education-image-1.{ext}
     */
    public function copyFixtureEducationLogo(int $providerId, int $educationId, string $fixtureFilename): ?string
    {
        $source = self::FIXTURES_BASE . '/educations/' . $fixtureFilename;
        if (!is_file($source)) {
            return null;
        }

        $extension = strtolower(pathinfo($fixtureFilename, PATHINFO_EXTENSION));
        $destDir = self::PUBLIC_BASE . '/' . $providerId . '/education/' . $educationId;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $dest = $destDir . '/education-image-1.' . $extension;
        copy($source, $dest);

        return '/images/providers/' . $providerId . '/education/' . $educationId . '/education-image-1.' . $extension;
    }
}
