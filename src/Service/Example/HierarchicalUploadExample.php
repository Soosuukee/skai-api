<?php

declare(strict_types=1);

namespace App\Service\Example;

use App\Service\FileUploadService;

/**
 * Exemple d'utilisation du service d'upload avec la structure hiérarchique
 */
class HierarchicalUploadExample
{
    public function __construct(
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Exemple d'upload d'image de couverture de service
     */
    public function uploadServiceCoverExample(): string
    {
        $file = [
            'name' => 'service-cover.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/service_cover.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024000
        ];

        $providerId = 123;
        $serviceId = 456;

        $fileUrl = $this->fileUploadService->uploadServiceCover($file, $providerId, $serviceId);

        // Résultat : "/uploads/images/providers/123/service/456/cover/cover_64f8a2b1c3d4e.jpg"
        // Chemin physique : "public/uploads/images/providers/123/service/456/cover/cover_64f8a2b1c3d4e.jpg"

        return $fileUrl;
    }

    /**
     * Exemple d'upload d'image de contenu de service
     */
    public function uploadServiceImageExample(): string
    {
        $file = [
            'name' => 'service-image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/service_image.png',
            'error' => UPLOAD_ERR_OK,
            'size' => 512000
        ];

        $providerId = 123;
        $serviceId = 456;
        $serviceSectionId = 789;
        $serviceContentId = 101112;

        $fileUrl = $this->fileUploadService->uploadServiceImage(
            $file,
            $providerId,
            $serviceId,
            $serviceSectionId,
            $serviceContentId
        );

        // Résultat : "/uploads/images/providers/123/service/456/789/101112/image_64f8a2b1c3d4f.png"
        // Chemin physique : "public/uploads/images/providers/123/service/456/789/101112/image_64f8a2b1c3d4f.png"

        return $fileUrl;
    }

    /**
     * Exemple d'upload d'image de couverture d'article
     */
    public function uploadArticleCoverExample(): string
    {
        $file = [
            'name' => 'article-cover.webp',
            'type' => 'image/webp',
            'tmp_name' => '/tmp/article_cover.webp',
            'error' => UPLOAD_ERR_OK,
            'size' => 1536000
        ];

        $providerId = 123;
        $articleId = 789;

        $fileUrl = $this->fileUploadService->uploadArticleCover($file, $providerId, $articleId);

        // Résultat : "/uploads/images/providers/123/article/789/cover/cover_64f8a2b1c3d4g.webp"
        // Chemin physique : "public/uploads/images/providers/123/article/789/cover/cover_64f8a2b1c3d4g.webp"

        return $fileUrl;
    }

    /**
     * Exemple d'upload d'image de contenu d'article
     */
    public function uploadArticleImageExample(): string
    {
        $file = [
            'name' => 'article-content.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/article_content.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 768000
        ];

        $providerId = 123;
        $articleId = 789;
        $articleSectionId = 131415;
        $articleContentId = 161718;

        $fileUrl = $this->fileUploadService->uploadArticleImage(
            $file,
            $providerId,
            $articleId,
            $articleSectionId,
            $articleContentId
        );

        // Résultat : "/uploads/images/providers/123/article/789/131415/161718/image_64f8a2b1c3d4h.jpg"
        // Chemin physique : "public/uploads/images/providers/123/article/789/131415/161718/image_64f8a2b1c3d4h.jpg"

        return $fileUrl;
    }

    /**
     * Exemple d'upload de logo d'expérience
     */
    public function uploadExperienceLogoExample(): string
    {
        $file = [
            'name' => 'company-logo.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/company_logo.png',
            'error' => UPLOAD_ERR_OK,
            'size' => 256000
        ];

        $providerId = 123;
        $experienceId = 192021;

        $fileUrl = $this->fileUploadService->uploadExperienceLogo($file, $providerId, $experienceId);

        // Résultat : "/uploads/images/providers/123/experience/192021/logo_64f8a2b1c3d4i.png"
        // Chemin physique : "public/uploads/images/providers/123/experience/192021/logo_64f8a2b1c3d4i.png"

        return $fileUrl;
    }

    /**
     * Exemple d'upload de logo d'éducation
     */
    public function uploadEducationLogoExample(): string
    {
        $file = [
            'name' => 'university-logo.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/university_logo.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 384000
        ];

        $providerId = 123;
        $educationId = 222324;

        $fileUrl = $this->fileUploadService->uploadEducationLogo($file, $providerId, $educationId);

        // Résultat : "/uploads/images/providers/123/education/222324/logo_64f8a2b1c3d4j.jpg"
        // Chemin physique : "public/uploads/images/providers/123/education/222324/logo_64f8a2b1c3d4j.jpg"

        return $fileUrl;
    }

    /**
     * Exemple d'upload de média de travail réalisé
     */
    public function uploadCompletedWorkMediaExample(): string
    {
        $file = [
            'name' => 'project-screenshot.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/project_screenshot.png',
            'error' => UPLOAD_ERR_OK,
            'size' => 2048000
        ];

        $providerId = 123;
        $workId = 252627;

        $fileUrl = $this->fileUploadService->uploadCompletedWorkMedia($file, $providerId, $workId);

        // Résultat : "/uploads/images/providers/123/completed-work/252627/media_64f8a2b1c3d4k.png"
        // Chemin physique : "public/uploads/images/providers/123/completed-work/252627/media_64f8a2b1c3d4k.png"

        return $fileUrl;
    }

    /**
     * Exemple de structure complète pour un provider
     */
    public function showProviderStructureExample(): array
    {
        $providerId = 123;

        return [
            'provider_profile' => "profiles/providers/provider-{$providerId}_64f8a2b1c3d4e.jpg",
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
}
