<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\UploadConfig;

class FileUploadService
{
    /**
     * Upload une photo de profil de provider
     * Format: images/providers/{providerId}/profilepicture.{extension}
     */
    public function uploadProviderProfilePicture(array $file, int $providerId): string
    {
        $directory = UploadConfig::getProviderProfilePicturePath($providerId);
        return $this->uploadFile($file, $directory, 'profilepicture');
    }

    /**
     * Upload une photo de profil de client
     * Format: images/clients/{clientId}/profilepicture.{extension}
     */
    public function uploadClientProfilePicture(array $file, int $clientId): string
    {
        $directory = UploadConfig::getClientProfilePicturePath($clientId);
        return $this->uploadFile($file, $directory, 'profilepicture');
    }

    /**
     * Upload une image de couverture de service
     * Format: images/providers/{providerId}/service/{serviceId}/cover/
     */
    public function uploadServiceCover(array $file, int $providerId, int $serviceId): string
    {
        $directory = UploadConfig::getServiceCoverPath($providerId, $serviceId);
        return $this->uploadFile($file, $directory, 'cover');
    }

    /**
     * Upload une image de contenu de service
     * Format: images/providers/{providerId}/service/{serviceId}/{serviceSectionId}/{serviceContentId}/
     */
    public function uploadServiceImage(array $file, int $providerId, int $serviceId, int $serviceSectionId, int $serviceContentId): string
    {
        $directory = UploadConfig::getServiceImagePath($providerId, $serviceId, $serviceSectionId, $serviceContentId);
        return $this->uploadFile($file, $directory, 'image');
    }

    /**
     * Upload une image de couverture d'article
     * Format: images/providers/{providerId}/article/{articleId}/cover/
     */
    public function uploadArticleCover(array $file, int $providerId, int $articleId): string
    {
        $directory = UploadConfig::getArticleCoverPath($providerId, $articleId);
        return $this->uploadFile($file, $directory, 'cover');
    }

    /**
     * Upload une image de contenu d'article
     * Format: images/providers/{providerId}/article/{articleId}/{articleSectionId}/{articleContentId}/
     */
    public function uploadArticleImage(array $file, int $providerId, int $articleId, int $articleSectionId, int $articleContentId): string
    {
        $directory = UploadConfig::getArticleImagePath($providerId, $articleId, $articleSectionId, $articleContentId);
        return $this->uploadFile($file, $directory, 'image');
    }

    /**
     * Upload un logo d'entreprise (experience)
     * Format: images/providers/{providerId}/experience/{experienceId}/
     */
    public function uploadExperienceLogo(array $file, int $providerId, int $experienceId): string
    {
        $directory = UploadConfig::getExperiencePath($providerId, $experienceId);
        return $this->uploadFile($file, $directory, 'logo');
    }

    /**
     * Upload un logo d'institution (education)
     * Format: images/providers/{providerId}/education/{educationId}/
     */
    public function uploadEducationLogo(array $file, int $providerId, int $educationId): string
    {
        $directory = UploadConfig::getEducationPath($providerId, $educationId);
        return $this->uploadFile($file, $directory, 'logo');
    }

    /**
     * Upload un média de travail réalisé
     * Format: images/providers/{providerId}/completed-work/{workId}/
     */
    public function uploadCompletedWorkMedia(array $file, int $providerId, int $workId): string
    {
        $directory = UploadConfig::getCompletedWorkPath($providerId, $workId);
        return $this->uploadFile($file, $directory, 'media');
    }

    /**
     * Upload générique de fichier
     */
    private function uploadFile(array $file, string $directory, string $prefix): string
    {
        // Vérifications de sécurité
        $this->validateFile($file);

        // Créer le dossier s'il n'existe pas
        $uploadPath = UploadConfig::getUploadPath($directory);
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Générer un nom de fichier unique
        $originalName = $file['name'];
        $uniqueFilename = UploadConfig::generateUniqueFilename($originalName, $directory);

        // Chemin complet du fichier
        $filepath = $uploadPath . $uniqueFilename;

        // Déplacer le fichier uploadé
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \RuntimeException('Erreur lors du déplacement du fichier uploadé');
        }

        // Retourner l'URL relative pour la base de données
        return UploadConfig::getRelativeUrl($directory, $uniqueFilename);
    }

    /**
     * Valide un fichier uploadé
     */
    private function validateFile(array $file): void
    {
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier sur le disque',
                UPLOAD_ERR_EXTENSION => 'Upload arrêté par une extension PHP'
            ];

            $message = $errorMessages[$file['error']] ?? 'Erreur inconnue lors de l\'upload';
            throw new \RuntimeException($message);
        }

        // Vérifier que le fichier a été uploadé via HTTP POST
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Fichier non uploadé via HTTP POST');
        }

        // Vérifier l'extension
        if (!UploadConfig::isAllowedExtension($file['name'])) {
            $allowedExtensions = array_merge(
                UploadConfig::ALLOWED_IMAGE_EXTENSIONS,
                UploadConfig::ALLOWED_DOCUMENT_EXTENSIONS
            );
            throw new \RuntimeException('Extension non autorisée. Extensions autorisées: ' . implode(', ', $allowedExtensions));
        }

        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!UploadConfig::isAllowedType($mimeType)) {
            throw new \RuntimeException('Type de fichier non autorisé: ' . $mimeType);
        }

        // Vérifier la taille
        $maxSize = UploadConfig::getMaxSize($mimeType);
        if ($file['size'] > $maxSize) {
            $maxSizeFormatted = UploadConfig::formatSize($maxSize);
            $fileSizeFormatted = UploadConfig::formatSize($file['size']);
            throw new \RuntimeException("Fichier trop volumineux. Taille actuelle: {$fileSizeFormatted}, Taille max: {$maxSizeFormatted}");
        }
    }

    /**
     * Supprime un fichier
     */
    public function deleteFile(string $fileUrl): bool
    {
        // Extraire le chemin du fichier depuis l'URL
        $path = parse_url($fileUrl, PHP_URL_PATH);
        if (!$path) {
            return false;
        }

        // Enlever le préfixe /uploads/
        $relativePath = str_replace('/uploads/', '', $path);

        // Déterminer le dossier
        $parts = explode('/', $relativePath);
        if (count($parts) < 2) {
            return false;
        }

        $directory = $parts[0] . '/';
        $filename = $parts[1];

        return UploadConfig::deleteFile($directory, $filename);
    }

    /**
     * Vérifie si un fichier existe
     */
    public function fileExists(string $fileUrl): bool
    {
        $path = parse_url($fileUrl, PHP_URL_PATH);
        if (!$path) {
            return false;
        }

        $relativePath = str_replace('/uploads/', '', $path);
        $parts = explode('/', $relativePath);

        if (count($parts) < 2) {
            return false;
        }

        $directory = $parts[0] . '/';
        $filename = $parts[1];

        $filepath = UploadConfig::getUploadPath($directory) . $filename;
        return file_exists($filepath);
    }

    /**
     * Retourne les informations d'un fichier
     */
    public function getFileInfo(string $fileUrl): ?array
    {
        $path = parse_url($fileUrl, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $relativePath = str_replace('/uploads/', '', $path);
        $parts = explode('/', $relativePath);

        if (count($parts) < 2) {
            return null;
        }

        $directory = $parts[0] . '/';
        $filename = $parts[1];
        $filepath = UploadConfig::getUploadPath($directory) . $filename;

        if (!file_exists($filepath)) {
            return null;
        }

        return [
            'filename' => $filename,
            'directory' => $directory,
            'size' => filesize($filepath),
            'size_formatted' => UploadConfig::formatSize(filesize($filepath)),
            'mime_type' => mime_content_type($filepath),
            'created_at' => filectime($filepath),
            'modified_at' => filemtime($filepath),
            'url' => $fileUrl
        ];
    }

    /**
     * Upload multiple de fichiers
     */
    public function uploadMultipleFiles(array $files, string $directory, string $prefix): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadedFiles[] = $this->uploadFile($file, $directory, $prefix);
            }
        }

        return $uploadedFiles;
    }

    /**
     * Nettoie les anciens fichiers orphelins
     */
    public function cleanupOrphanedFiles(string $directory, int $olderThanDays = 30): int
    {
        $uploadPath = UploadConfig::getUploadPath($directory);
        if (!is_dir($uploadPath)) {
            return 0;
        }

        $deletedCount = 0;
        $cutoffTime = time() - ($olderThanDays * 24 * 60 * 60);

        $files = scandir($uploadPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filepath = $uploadPath . $file;
            if (is_file($filepath) && filemtime($filepath) < $cutoffTime) {
                if (unlink($filepath)) {
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }
}
