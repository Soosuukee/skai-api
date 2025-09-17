<?php

declare(strict_types=1);

namespace App\Service\Example;

use App\Service\FileUploadService;

/**
 * Exemple d'utilisation du service d'upload pour les photos de profil
 */
class ProfilePictureUploadExample
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Exemple d'upload de photo de profil de provider
     */
    public function uploadProviderProfilePictureExample(): string
    {
        $file = [
            'name' => 'profile-photo.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/provider_profile.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024000
        ];

        $providerId = 123;

        $fileUrl = $this->fileUploadService->uploadProviderProfilePicture($file, $providerId);

        // Résultat : "/uploads/images/providers/123/profilepicture_64f8a2b1c3d4e.jpg"
        // Chemin physique : "public/uploads/images/providers/123/profilepicture_64f8a2b1c3d4e.jpg"

        return $fileUrl;
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
            'size' => 512000
        ];

        $clientId = 456;

        $fileUrl = $this->fileUploadService->uploadClientProfilePicture($file, $clientId);

        // Résultat : "/uploads/images/clients/456/profilepicture_64f8a2b1c3d4f.png"
        // Chemin physique : "public/uploads/images/clients/456/profilepicture_64f8a2b1c3d4f.png"

        return $fileUrl;
    }

    /**
     * Exemple de structure complète pour un provider
     */
    public function showProviderCompleteStructureExample(): array
    {
        $providerId = 123;

        return [
            'profile_picture' => "images/providers/{$providerId}/profilepicture_64f8a2b1c3d4e.jpg",
            'services' => [
                'service_456' => [
                    'cover' => "images/providers/{$providerId}/service/456/cover/cover_64f8a2b1c3d4f.jpg",
                    'content_images' => [
                        "images/providers/{$providerId}/service/456/789/101112/image_64f8a2b1c3d4g.png",
                        "images/providers/{$providerId}/service/456/789/131415/image_64f8a2b1c3d4h.jpg"
                    ]
                ]
            ],
            'articles' => [
                'article_789' => [
                    'cover' => "images/providers/{$providerId}/article/789/cover/cover_64f8a2b1c3d4i.webp",
                    'content_images' => [
                        "images/providers/{$providerId}/article/789/161718/192021/image_64f8a2b1c3d4j.png"
                    ]
                ]
            ],
            'experiences' => [
                "images/providers/{$providerId}/experience/222324/logo_64f8a2b1c3d4k.png"
            ],
            'educations' => [
                "images/providers/{$providerId}/education/252627/logo_64f8a2b1c3d4l.jpg"
            ],
            'completed_works' => [
                "images/providers/{$providerId}/completed-work/282930/media_64f8a2b1c3d4m.png"
            ]
        ];
    }

    /**
     * Exemple de structure complète pour un client
     */
    public function showClientCompleteStructureExample(): array
    {
        $clientId = 456;

        return [
            'profile_picture' => "images/clients/{$clientId}/profilepicture_64f8a2b1c3d4e.png",
            'uploads' => [
                // Les clients n'ont que leur photo de profil
                // Ils peuvent uploader des fichiers dans le contexte de leurs demandes/réservations
            ]
        ];
    }

    /**
     * Exemple d'utilisation dans un contrôleur
     */
    public function controllerExample(): array
    {
        // Simuler un contrôleur
        $providerId = 123;
        $clientId = 456;

        // Upload photo de profil provider
        $providerFile = [
            'name' => 'john-doe-profile.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/john_doe_profile.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1536000
        ];

        $providerProfileUrl = $this->fileUploadService->uploadProviderProfilePicture($providerFile, $providerId);

        // Upload photo de profil client
        $clientFile = [
            'name' => 'marie-martin-avatar.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/marie_martin_avatar.png',
            'error' => UPLOAD_ERR_OK,
            'size' => 768000
        ];

        $clientProfileUrl = $this->fileUploadService->uploadClientProfilePicture($clientFile, $clientId);

        return [
            'provider_profile' => $providerProfileUrl,
            'client_profile' => $clientProfileUrl,
            'message' => 'Photos de profil uploadées avec succès'
        ];
    }
}
