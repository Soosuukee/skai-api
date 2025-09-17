<?php

declare(strict_types=1);

namespace App\Service\Example;

use App\Service\FileUploadService;

/**
 * Exemple d'utilisation du service d'upload de fichiers
 */
class FileUploadUsageExample
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Exemple d'upload de photo de profil de provider
     */
    public function uploadProviderProfilePictureExample(): string
    {
        // Simuler un fichier uploadé (en réalité, ceci vient de $_FILES)
        $file = [
            'name' => 'profile-picture.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/uploaded_file.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024000 // 1MB
        ];

        $providerId = 123;

        try {
            $fileUrl = $this->fileUploadService->uploadProviderProfilePicture($file, $providerId);
            // Résultat : "/uploads/profiles/providers/provider-123_64f8a2b1c3d4e.jpg"

            return $fileUrl;
        } catch (\RuntimeException $e) {
            // Gérer l'erreur
            throw new \RuntimeException('Erreur upload: ' . $e->getMessage());
        }
    }

    /**
     * Exemple d'upload de photo de profil de client
     */
    public function uploadClientProfilePictureExample(): string
    {
        $file = [
            'name' => 'client-avatar.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/client_avatar.png',
            'error' => UPLOAD_ERR_OK,
            'size' => 512000 // 512KB
        ];

        $clientId = 456;

        $fileUrl = $this->fileUploadService->uploadClientProfilePicture($file, $clientId);
        // Résultat : "/uploads/profiles/clients/client-456_64f8a2b1c3d4e.png"

        return $fileUrl;
    }

    /**
     * Exemple d'upload d'image de couverture de service
     */
    public function uploadServiceCoverExample(): string
    {
        $file = [
            'name' => 'service-cover.webp',
            'type' => 'image/webp',
            'tmp_name' => '/tmp/service_cover.webp',
            'error' => UPLOAD_ERR_OK,
            'size' => 2048000 // 2MB
        ];

        $serviceId = 789;

        $fileUrl = $this->fileUploadService->uploadServiceCover($file, $serviceId);
        // Résultat : "/uploads/services/covers/service-789_64f8a2b1c3d4e.webp"

        return $fileUrl;
    }

    /**
     * Exemple d'upload d'image d'article
     */
    public function uploadArticleImageExample(): string
    {
        $file = [
            'name' => 'article-image.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/article_image.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1536000 // 1.5MB
        ];

        $articleId = 101112;

        $fileUrl = $this->fileUploadService->uploadArticleImage($file, $articleId);
        // Résultat : "/uploads/articles/images/article-101112_64f8a2b1c3d4e.jpg"

        return $fileUrl;
    }

    /**
     * Exemple de gestion des erreurs d'upload
     */
    public function handleUploadErrorsExample(): void
    {
        // Fichier trop volumineux
        $largeFile = [
            'name' => 'huge-file.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/huge_file.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 10 * 1024 * 1024 // 10MB (dépasse la limite de 5MB pour les images)
        ];

        try {
            $this->fileUploadService->uploadProviderProfilePicture($largeFile, 123);
        } catch (\RuntimeException $e) {
            // Erreur attendue : "Fichier trop volumineux. Taille actuelle: 10MB, Taille max: 5MB"
            echo "Erreur capturée: " . $e->getMessage();
        }

        // Extension non autorisée
        $invalidFile = [
            'name' => 'document.exe',
            'type' => 'application/x-executable',
            'tmp_name' => '/tmp/document.exe',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024000
        ];

        try {
            $this->fileUploadService->uploadProviderProfilePicture($invalidFile, 123);
        } catch (\RuntimeException $e) {
            // Erreur attendue : "Extension non autorisée. Extensions autorisées: jpg, jpeg, png, gif, webp"
            echo "Erreur capturée: " . $e->getMessage();
        }
    }

    /**
     * Exemple de suppression de fichier
     */
    public function deleteFileExample(): void
    {
        $fileUrl = "/uploads/profiles/providers/provider-123_64f8a2b1c3d4e.jpg";

        $deleted = $this->fileUploadService->deleteFile($fileUrl);

        if ($deleted) {
            echo "Fichier supprimé avec succès";
        } else {
            echo "Impossible de supprimer le fichier";
        }
    }

    /**
     * Exemple de vérification d'existence de fichier
     */
    public function checkFileExistsExample(): void
    {
        $fileUrl = "/uploads/profiles/providers/provider-123_64f8a2b1c3d4e.jpg";

        $exists = $this->fileUploadService->fileExists($fileUrl);

        if ($exists) {
            echo "Le fichier existe";

            // Récupérer les informations du fichier
            $fileInfo = $this->fileUploadService->getFileInfo($fileUrl);
            if ($fileInfo) {
                echo "Taille: " . $fileInfo['size_formatted'];
                echo "Type: " . $fileInfo['mime_type'];
                echo "Créé le: " . date('Y-m-d H:i:s', $fileInfo['created_at']);
            }
        } else {
            echo "Le fichier n'existe pas";
        }
    }

    /**
     * Exemple d'upload multiple
     */
    public function uploadMultipleFilesExample(): array
    {
        $files = [
            [
                'name' => 'image1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/image1.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024000
            ],
            [
                'name' => 'image2.png',
                'type' => 'image/png',
                'tmp_name' => '/tmp/image2.png',
                'error' => UPLOAD_ERR_OK,
                'size' => 512000
            ]
        ];

        $serviceId = 789;
        $uploadedFiles = $this->fileUploadService->uploadMultipleFiles(
            $files,
            'services/images/',
            'service-' . $serviceId
        );

        // Résultat : [
        //     "/uploads/services/images/service-789_64f8a2b1c3d4e.jpg",
        //     "/uploads/services/images/service-789_64f8a2b1c3d4f.png"
        // ]

        return $uploadedFiles;
    }
}
