<?php

declare(strict_types=1);

namespace App\Config;

class UploadConfig
{
    // Dossiers d'upload
    public const PROVIDER_PROFILE_DIR = 'images/providers/';
    public const CLIENT_PROFILE_DIR = 'images/clients/';
    public const PROVIDER_SERVICE_COVER_DIR = 'images/providers/';
    public const PROVIDER_SERVICE_IMAGE_DIR = 'images/providers/';
    public const PROVIDER_ARTICLE_COVER_DIR = 'images/providers/';
    public const PROVIDER_ARTICLE_IMAGE_DIR = 'images/providers/';
    public const PROVIDER_EXPERIENCE_DIR = 'images/providers/';
    public const PROVIDER_EDUCATION_DIR = 'images/providers/';
    public const PROVIDER_COMPLETED_WORK_DIR = 'images/providers/';

    // Taille maximale des fichiers
    public const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
    public const MAX_DOCUMENT_SIZE = 10 * 1024 * 1024; // 10MB

    // Extensions autorisées
    public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    public const ALLOWED_DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'txt'];

    // Types MIME autorisés
    public const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    public const ALLOWED_DOCUMENT_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ];

    /**
     * Retourne le chemin complet du dossier d'upload
     */
    public static function getUploadPath(string $directory): string
    {
        $base = $_ENV['UPLOAD_PATH'] ?? (dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public');
        return rtrim($base, '/\\') . '/' . ltrim($directory, '/\\');
    }

    /**
     * Génère un nom de fichier unique
     */
    public static function generateUniqueFilename(string $originalName, string $directory): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);

        // Nettoyer le nom de base
        $basename = self::sanitizeFilename($basename);

        // Générer un nom unique
        $uniqueName = $basename . '_' . uniqid() . '.' . strtolower($extension);

        return $uniqueName;
    }

    /**
     * Nettoie un nom de fichier
     */
    private static function sanitizeFilename(string $filename): string
    {
        // Supprimer les caractères spéciaux
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);

        // Limiter la longueur
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 50);
        }

        return $filename ?: 'file';
    }

    /**
     * Retourne l'URL relative du fichier
     */
    public static function getRelativeUrl(string $directory, string $filename): string
    {
        return '/' . $directory . $filename;
    }

    /**
     * Retourne l'URL complète du fichier
     */
    public static function getFullUrl(string $directory, string $filename): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        return $baseUrl . self::getRelativeUrl($directory, $filename);
    }

    /**
     * Vérifie si l'extension est autorisée
     */
    public static function isAllowedExtension(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS) ||
            in_array($extension, self::ALLOWED_DOCUMENT_EXTENSIONS);
    }

    /**
     * Vérifie si le type MIME est autorisé
     */
    public static function isAllowedType(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_IMAGE_TYPES) ||
            in_array($mimeType, self::ALLOWED_DOCUMENT_TYPES);
    }

    /**
     * Vérifie si le fichier est une image
     */
    public static function isImage(string $mimeType): bool
    {
        return strpos($mimeType, 'image/') === 0;
    }

    /**
     * Supprime un fichier
     */
    public static function deleteFile(string $directory, string $filename): bool
    {
        $filepath = self::getUploadPath($directory) . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    /**
     * Retourne la taille maximale selon le type de fichier
     */
    public static function getMaxSize(string $mimeType): int
    {
        return self::isImage($mimeType) ? self::MAX_IMAGE_SIZE : self::MAX_DOCUMENT_SIZE;
    }

    /**
     * Formate la taille en format lisible
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Génère le chemin pour une image de couverture de service
     * Format: images/providers/{providerId}/service/{serviceId}/cover/
     */
    public static function getServiceCoverPath(int $providerId, int $serviceId): string
    {
        return "images/providers/{$providerId}/service/{$serviceId}/cover/";
    }

    /**
     * Génère le chemin pour une image de contenu de service
     * Format: images/providers/{providerId}/service/{serviceId}/{serviceSectionId}/{serviceContentId}/
     */
    public static function getServiceImagePath(int $providerId, int $serviceId, int $serviceSectionId, int $serviceContentId): string
    {
        return "images/providers/{$providerId}/service/{$serviceId}/{$serviceSectionId}/{$serviceContentId}/";
    }

    /**
     * Génère le chemin pour une image de couverture d'article
     * Format: images/providers/{providerId}/article/{articleId}/cover/
     */
    public static function getArticleCoverPath(int $providerId, int $articleId): string
    {
        return "images/providers/{$providerId}/article/{$articleId}/cover/";
    }

    /**
     * Génère le chemin pour une image de contenu d'article
     * Format: images/providers/{providerId}/article/{articleId}/{articleSectionId}/{articleContentId}/
     */
    public static function getArticleImagePath(int $providerId, int $articleId, int $articleSectionId, int $articleContentId): string
    {
        return "images/providers/{$providerId}/article/{$articleId}/{$articleSectionId}/{$articleContentId}/";
    }

    /**
     * Génère le chemin pour une expérience
     * Format: images/providers/{providerId}/experience/{experienceId}/
     */
    public static function getExperiencePath(int $providerId, int $experienceId): string
    {
        return "images/providers/{$providerId}/experience/{$experienceId}/";
    }

    /**
     * Génère le chemin pour une éducation
     * Format: images/providers/{providerId}/education/{educationId}/
     */
    public static function getEducationPath(int $providerId, int $educationId): string
    {
        return "images/providers/{$providerId}/education/{$educationId}/";
    }

    /**
     * Génère le chemin pour un travail réalisé
     * Format: images/providers/{providerId}/completed-work/{workId}/
     */
    public static function getCompletedWorkPath(int $providerId, int $workId): string
    {
        return "images/providers/{$providerId}/completed-work/{$workId}/";
    }

    /**
     * Génère le chemin pour une photo de profil de provider
     * Format: images/providers/{providerId}/profilepicture.{extension}
     */
    public static function getProviderProfilePicturePath(int $providerId): string
    {
        return "images/providers/{$providerId}/profile/";
    }

    /**
     * Génère le chemin pour une photo de profil de client
     * Format: images/clients/{clientId}/profilepicture.{extension}
     */
    public static function getClientProfilePicturePath(int $clientId): string
    {
        return "images/clients/{$clientId}/profile/";
    }
}
