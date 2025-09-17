<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Service;
use Soosuuke\IaPlatform\Entity\ServiceTag;
use Soosuuke\IaPlatform\Repository\TagRepository;
use Soosuuke\IaPlatform\Repository\ServiceRepository;
use Soosuuke\IaPlatform\Service\ServiceSlugificationService;
use Soosuuke\IaPlatform\Service\ProviderImageService;

class ServiceFixtures
{
    private \PDO $pdo;
    private ServiceRepository $serviceRepository;
    private ServiceSlugificationService $slugificationService;
    private ProviderImageService $imageService;
    private TagRepository $tagRepository;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->serviceRepository = new ServiceRepository();
        $this->slugificationService = new ServiceSlugificationService();
        $this->imageService = new ProviderImageService();
        $this->tagRepository = new TagRepository();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Service...\n";

        // Données construites (copiées depuis services.json et adaptées)
        $items = [
            [
                'providerId' => 1,
                'title' => 'Conférences & Keynotes',
                'summary' => "Conférences et keynotes sur l'IA et le GPU computing pour vos événements et conférences.",
                'tag' => 'Conférences',
                'minPrice' => 15000,
                'maxPrice' => 15000,
                'isActive' => true,
                'isFeatured' => true,
                'serviceCover' => 'cover-1.jpg'
            ],
            [
                'providerId' => 1,
                'title' => 'Conseil stratégique IA & GPU',
                'summary' => "Conseil stratégique en intelligence artificielle et GPU computing pour optimiser vos projets.",
                'tag' => 'Conseil',
                'minPrice' => null,
                'maxPrice' => null,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'ferrari-laferrari.jpeg'
            ],
            [
                'providerId' => 1,
                'title' => "Programmes d'accélération startups IA",
                'summary' => "Programmes d'accélération spécialisés pour les startups en intelligence artificielle.",
                'tag' => 'Accélération',
                'minPrice' => 9500,
                'maxPrice' => 9500,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'bugatti-chiron.jpg'
            ],
            [
                'providerId' => 2,
                'title' => 'Développement de modèles Machine Learning',
                'summary' => 'Développement et optimisation de modèles de machine learning sur mesure.',
                'tag' => 'Machine Learning',
                'minPrice' => 8000,
                'maxPrice' => 12000,
                'isActive' => true,
                'isFeatured' => true,
                'serviceCover' => 'lamborghini-aventador.jpg'
            ],
            [
                'providerId' => 2,
                'title' => "Optimisation d'algorithmes IA",
                'summary' => "Optimisation et amélioration des performances de vos algorithmes d'intelligence artificielle.",
                'tag' => 'Optimisation',
                'minPrice' => 6000,
                'maxPrice' => 9000,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'rolls-royce-phantom.jpg'
            ],
            [
                'providerId' => 3,
                'title' => 'Analyse de données avancée',
                'summary' => 'Analyse approfondie de vos données avec des techniques avancées de data science.',
                'tag' => 'Data Science',
                'minPrice' => 5000,
                'maxPrice' => 8000,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'bentley-continental.jpg'
            ],
            [
                'providerId' => 4,
                'title' => 'Recherche en IA innovante',
                'summary' => 'Recherche et développement de solutions innovantes en intelligence artificielle.',
                'tag' => 'Recherche',
                'minPrice' => 12000,
                'maxPrice' => 18000,
                'isActive' => true,
                'isFeatured' => true,
                'serviceCover' => 'mclaren-720s.webp'
            ],
            [
                'providerId' => 5,
                'title' => 'Solutions de vision par ordinateur',
                'summary' => 'Développement de solutions de vision par ordinateur pour vos applications.',
                'tag' => 'Computer Vision',
                'minPrice' => 7000,
                'maxPrice' => 11000,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'porsche-911.avif'
            ],
            [
                'providerId' => 6,
                'title' => 'Deep Learning spécialisé',
                'summary' => 'Solutions spécialisées en deep learning pour vos projets complexes.',
                'tag' => 'Deep Learning',
                'minPrice' => 9000,
                'maxPrice' => 14000,
                'isActive' => true,
                'isFeatured' => true,
                'serviceCover' => 'aston-martin-db11.jpg'
            ],
            [
                'providerId' => 3,
                'title' => 'Tableaux de bord et BI',
                'summary' => 'Conception de dashboards interactifs et pipelines BI.',
                'tag' => 'BI',
                'minPrice' => 4500,
                'maxPrice' => 9000,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'maserati-mc20.jpg'
            ],
            [
                'providerId' => 4,
                'title' => 'Prototypage rapide IA',
                'summary' => 'Mise en place rapide de POC IA sur vos données.',
                'tag' => 'Prototype',
                'minPrice' => 6000,
                'maxPrice' => 12000,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'pagani-huayra.jpg'
            ],
            [
                'providerId' => 5,
                'title' => 'MLOps & déploiement',
                'summary' => 'CI/CD modèles, monitoring et déploiement scalable.',
                'tag' => 'MLOps',
                'minPrice' => 8000,
                'maxPrice' => 15000,
                'isActive' => true,
                'isFeatured' => true,
                'serviceCover' => 'koenigsegg-agera.webp'
            ],
            [
                'providerId' => 6,
                'title' => 'NLP appliqué',
                'summary' => "Extraction d'information et chatbots pour vos métiers.",
                'tag' => 'NLP',
                'minPrice' => 7000,
                'maxPrice' => 13000,
                'isActive' => true,
                'isFeatured' => false,
                'serviceCover' => 'mercedes-amg-gt.avif'
            ],
        ];

        foreach ($items as $idx => $row) {
            $title = $row['title'] ?? ('service-' . ($idx + 1));

            // Générer slug unique à partir du titre
            $slug = $this->slugificationService->generateServiceSlug(
                $title,
                function (string $candidate): bool {
                    return $this->serviceRepository->findBySlug($candidate) !== null;
                }
            );

            $service = new Service(
                (int) $row['providerId'],
                (string) $row['title'],
                isset($row['maxPrice']) ? (float) $row['maxPrice'] : null,
                isset($row['minPrice']) ? (float) $row['minPrice'] : null,
                (bool) $row['isActive'],
                (bool) $row['isFeatured'],
                $row['serviceCover'] ?? null,
                (string) $row['summary'],
                $slug
            );

            // Sections + contenus par défaut (3 sections)
            $sections = [
                [
                    'title' => $title, // Utiliser le titre comme première section
                    'contents' => [
                        ['content' => $row['summary']]
                    ]
                ],
                [
                    'title' => 'Fonctionnalités',
                    'contents' => [
                        ['content' => 'Suspendisse potenti. Curabitur pharetra massa at blandit venenatis.']
                    ]
                ],
                [
                    'title' => 'Tarification',
                    'contents' => [
                        ['content' => 'Praesent in tellus at mauris gravida faucibus. Nulla facilisi.']
                    ]
                ]
            ];

            $this->serviceRepository->saveServiceWithContent($service, $sections);

            // Insérer des images de contenu par défaut (URL publiques)
            $serviceId = (int)$service->getId();
            $stmt = $this->pdo->prepare('SELECT id FROM service_section WHERE service_id = ? ORDER BY id');
            $stmt->execute([$serviceId]);
            $sectionsRows = $stmt->fetchAll();
            foreach ($sectionsRows as $sRow) {
                $stmt2 = $this->pdo->prepare('SELECT id FROM service_content WHERE service_content_id = ? ORDER BY id');
                $stmt2->execute([$sRow['id']]);
                $contentsRows = $stmt2->fetchAll();
                foreach ($contentsRows as $cRow) {
                    // Exemple: ajouter une image publique si une existe dans fixtures_images/providers/services
                    $example = $row['serviceCover'] ?? null;
                    if ($example) {
                        $url = $this->imageService->copyFixtureServiceCover((int)$row['providerId'], $serviceId, (string)$example);
                        if ($url) {
                            $stmt3 = $this->pdo->prepare('INSERT INTO service_image (service_content_id, url) VALUES (?, ?)');
                            $stmt3->execute([$cRow['id'], $url]);
                        }
                    }
                }
            }

            // Copier cover si fournie et stocker l'URL relative finale
            if (!empty($row['serviceCover'])) {
                $finalUrl = $this->imageService->copyFixtureServiceCover((int)$row['providerId'], (int)$service->getId(), (string)$row['serviceCover']);
                if ($finalUrl) {
                    $service->setCover($finalUrl);
                    $this->serviceRepository->update($service);
                }
            }
            echo "Service créé (avec contenu): {$title} (providerId: {$row['providerId']}, slug: {$slug})\n";

            // Associer un tag s'il existe
            if (!empty($row['tag'])) {
                $tag = $this->tagRepository->findByTitle($row['tag']);
                if ($tag) {
                    $stmt = $this->pdo->prepare('INSERT INTO service_tag (service_id, tag_id) VALUES (?, ?)');
                    $stmt->execute([$service->getId(), $tag->getId()]);
                }
            }
        }

        echo "✅ Fixtures Service chargées avec succès.\n";
    }
}
