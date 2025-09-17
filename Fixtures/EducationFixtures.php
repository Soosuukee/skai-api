<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Education;
use Soosuuke\IaPlatform\Repository\EducationRepository;
use Soosuuke\IaPlatform\Service\ProviderImageService;

class EducationFixtures
{
    private \PDO $pdo;
    private EducationRepository $educationRepository;
    private ProviderImageService $imageService;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->educationRepository = new EducationRepository();
        $this->imageService = new ProviderImageService();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Education...\n";

        // Données normalisées (issues de data/providerEducation.json)
        $rows = [
            [
                'providerId' => 1,
                'title' => 'Bachelor en Génie Électrique',
                'institutionName' => "Université de l'État d'Oregon",
                'description' => 'Parcours accrédité ABET couvrant les fondements du génie électrique : circuits analogiques et numériques, électronique de puissance, électromagnétisme, traitement du signal et microprocesseurs.',
                'startedAt' => '1980-01-01',
                'endedAt' => '1984-12-31',
                'institutionImage' => 'oregon-state-logo.png'
            ],
            [
                'providerId' => 1,
                'title' => 'Master en Génie Électrique',
                'institutionName' => 'Université de Stanford',
                'description' => 'Cycle de recherche axé sur les architectures matérielles avancées et le calcul parallèle : VLSI, conception de processeurs graphiques et algorithmes accélérés.',
                'startedAt' => '1990-01-01',
                'endedAt' => '1992-12-31',
                'institutionImage' => 'stanford-logo.png'
            ],
            [
                'providerId' => 2,
                'title' => 'Master en Intelligence Artificielle',
                'institutionName' => 'Université de Stanford',
                'description' => "Formation approfondie en machine learning, deep learning et traitement du langage naturel. Recherche sur les réseaux de neurones et l'optimisation.",
                'startedAt' => '2000-01-01',
                'endedAt' => '2002-12-31',
                'institutionImage' => 'stanford-logo.png'
            ],
            [
                'providerId' => 2,
                'title' => 'PhD en Informatique',
                'institutionName' => 'Université de Californie, Berkeley',
                'description' => "Doctorat en informatique avec spécialisation en machine learning et reconnaissance de patterns. Thèse sur les algorithmes d'apprentissage automatique.",
                'startedAt' => '2002-01-01',
                'endedAt' => '2005-12-31',
                'institutionImage' => 'berkeley-logo.png'
            ],
            [
                'providerId' => 2,
                'title' => 'Formation Continue en Deep Learning',
                'institutionName' => 'MIT',
                'description' => 'Programme de formation continue spécialisé dans les réseaux de neurones profonds et les architectures transformer.',
                'startedAt' => '2020-01-01',
                'endedAt' => null,
                'institutionImage' => 'mit-logo.png'
            ],
        ];

        foreach ($rows as $row) {
            $providerId = (int) ($row['providerId'] ?? 0);
            if ($providerId <= 0) {
                continue;
            }

            $title = (string) ($row['title'] ?? '');
            $institutionName = (string) ($row['institutionName'] ?? '');
            $description = (string) ($row['description'] ?? '');
            $startedAt = new \DateTimeImmutable((string) ($row['startedAt'] ?? 'now'));
            $endedRaw = $row['endedAt'] ?? null;
            $endedAt = $endedRaw !== null && $endedRaw !== '' ? new \DateTimeImmutable((string) $endedRaw) : null;
            $institutionImage = $row['institutionImage'] ?? null;

            $education = new Education(
                $providerId,
                $title,
                $institutionName,
                $description,
                $startedAt,
                $endedAt,
                $institutionImage ? (string) $institutionImage : null
            );

            $this->educationRepository->save($education);

            // Copier le logo d'éducation et stocker l'URL relative finale
            if ($institutionImage && method_exists($education, 'getId')) {
                $finalUrl = $this->imageService->copyFixtureEducationLogo($providerId, (int)$education->getId(), (string)$institutionImage);
                if ($finalUrl) {
                    $education->setInstitutionImage($finalUrl);
                    $this->educationRepository->update($education);
                }
            }

            echo "Education créée: {$title} ({$institutionName}) pour provider {$providerId}\n";
        }

        echo "✅ Fixtures Education chargées avec succès.\n";
    }
}
