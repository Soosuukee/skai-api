<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Experience;
use Soosuuke\IaPlatform\Repository\ExperienceRepository;
use Soosuuke\IaPlatform\Service\ProviderImageService;

class ExperienceFixtures
{
    private \PDO $pdo;
    private ExperienceRepository $experienceRepository;
    private ProviderImageService $imageService;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->experienceRepository = new ExperienceRepository();
        $this->imageService = new ProviderImageService();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Experience...\n";

        // Données normalisées (issues de data/providerExperiences.json)
        $rows = [
            [
                'providerId' => 1,
                'title' => 'CEO & Founder',
                'companyName' => 'NVIDIA',
                'firstTask' => "Développement de l'architecture GPU révolutionnaire",
                'secondTask' => 'Expansion internationale et acquisitions stratégiques',
                'thirdTask' => null,
                'startedAt' => '1993-01-01',
                'endedAt' => null,
                'companyLogo' => 'nvidia-logo.png'
            ],
            [
                'providerId' => 1,
                'title' => 'Ingénieur Senior',
                'companyName' => 'LSI Logic',
                'firstTask' => 'Conception de circuits intégrés haute performance',
                'secondTask' => 'Optimisation des architectures de processeurs',
                'thirdTask' => null,
                'startedAt' => '1990-01-01',
                'endedAt' => '1992-12-31',
                'companyLogo' => 'lsi-logic-logo.png'
            ],
            [
                'providerId' => 2,
                'title' => 'AI Researcher & Educator',
                'companyName' => 'deeplearning.ai',
                'firstTask' => 'Développement de cours en ligne sur le deep learning',
                'secondTask' => 'Recherche sur les réseaux de neurones',
                'thirdTask' => null,
                'startedAt' => '2017-01-01',
                'endedAt' => null,
                'companyLogo' => 'deeplearning-ai-logo.jpg'
            ],
            [
                'providerId' => 2,
                'title' => 'Chief Scientist',
                'companyName' => 'Baidu',
                'firstTask' => "Direction de l'équipe de recherche en IA",
                'secondTask' => "Développement d'algorithmes de reconnaissance vocale",
                'thirdTask' => null,
                'startedAt' => '2014-01-01',
                'endedAt' => '2017-12-31',
                'companyLogo' => 'baidu-logo.png'
            ],
            [
                'providerId' => 2,
                'title' => 'Co-fondateur',
                'companyName' => 'Coursera',
                'firstTask' => "Développement de la plateforme d'apprentissage en ligne",
                'secondTask' => 'Création de partenariats avec les universités',
                'thirdTask' => null,
                'startedAt' => '2012-01-01',
                'endedAt' => '2014-12-31',
                'companyLogo' => 'coursera-logo.png'
            ],
        ];

        foreach ($rows as $row) {
            $providerId = (int) ($row['providerId'] ?? 0);
            if ($providerId <= 0) {
                continue;
            }

            $title = (string) ($row['title'] ?? '');
            $companyName = (string) ($row['companyName'] ?? '');
            $firstTask = (string) ($row['firstTask'] ?? '');
            $secondTask = $row['secondTask'] ?? null;
            $thirdTask = $row['thirdTask'] ?? null;
            $startedAt = new \DateTimeImmutable((string) ($row['startedAt'] ?? 'now'));
            $endedAt = $row['endedAt'] ?? null;
            $endedAtObj = $endedAt !== null && $endedAt !== '' ? new \DateTimeImmutable((string) $endedAt) : null;
            $companyLogo = $row['companyLogo'] ?? null;

            $experience = new Experience(
                $providerId,
                $title,
                $companyName,
                $firstTask,
                $startedAt,
                $endedAtObj,
                $secondTask ? (string) $secondTask : null,
                $thirdTask ? (string) $thirdTask : null,
                $companyLogo ? (string) $companyLogo : null
            );

            $this->experienceRepository->save($experience);

            // Copier le logo d'expérience depuis fixtures_images et stocker l'URL relative finale
            if ($companyLogo && method_exists($experience, 'getId')) {
                $finalUrl = $this->imageService->copyFixtureExperienceLogo($providerId, (int)$experience->getId(), (string)$companyLogo);
                if ($finalUrl) {
                    $experience->setCompanyLogo($finalUrl);
                    $this->experienceRepository->update($experience);
                }
            }
            echo "Experience créée: {$title} ({$companyName}) pour provider {$providerId}\n";
        }

        echo "✅ Fixtures Experience chargées avec succès.\n";
    }
}
