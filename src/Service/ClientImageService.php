<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Service d'aide pour copier des images de clients depuis /fixtures_images
 * vers l'arborescence publique.
 */
class ClientImageService
{
    private const PUBLIC_BASE = __DIR__ . '/../../public/images/clients';
    private const FIXTURES_BASE = __DIR__ . '/../../fixtures_images/client/profilepictures';

    /**
     * Crée la structure du client et copie l'image de profil si elle existe.
     * Retourne l'URL publique si une image a été copiée, sinon null.
     */
    public function createClientImageStructure(int $clientId, string $profilePicture): ?string
    {
        $baseDir = self::PUBLIC_BASE . '/' . $clientId . '/profile';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $source = self::FIXTURES_BASE . '/' . $profilePicture;
        if (!is_file($source)) {
            return null;
        }

        $extension = strtolower(pathinfo($profilePicture, PATHINFO_EXTENSION));
        $dest = $baseDir . '/profile-picture.' . $extension;
        copy($source, $dest);

        return '/images/clients/' . $clientId . '/profile/profile-picture.' . $extension;
    }
}
