<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Service;
use App\Entity\ServiceSection;
use App\Entity\ServiceContent;
use App\Repository\ProviderRepository;
use App\Repository\TagRepository;
use App\Service\ProviderImageService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Fixtures pour les services
 */
class ServiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private TagRepository $tagRepository,
        private SluggerInterface $slugger,
        private ProviderImageService $providerImageService,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $providers = $this->providerRepository->findAll();

        if (empty($providers)) {
            return; // Pas de providers, pas de services
        }

        $serviceTemplates = [
            [
                'title' => 'Conseil en Intelligence Artificielle',
                'summary' => 'Conseil stratégique en IA pour optimiser vos projets et processus métier.',
                'tag' => 'IA',
                'minPrice' => 5000,
                'maxPrice' => 10000,
                'isFeatured' => true
            ],
            [
                'title' => 'Développement de modèles Machine Learning',
                'summary' => 'Création et optimisation de modèles ML sur mesure pour vos besoins spécifiques.',
                'tag' => 'Machine Learning',
                'minPrice' => 8000,
                'maxPrice' => 15000,
                'isFeatured' => true
            ],
            [
                'title' => 'Analyse de données avancée',
                'summary' => 'Analyse approfondie de vos données avec des techniques de data science modernes.',
                'tag' => 'Data Science',
                'minPrice' => 4000,
                'maxPrice' => 8000,
                'isFeatured' => false
            ],
            [
                'title' => 'Formation en technologies émergentes',
                'summary' => 'Formations personnalisées sur les dernières technologies et tendances du marché.',
                'tag' => 'Formation',
                'minPrice' => 2000,
                'maxPrice' => 5000,
                'isFeatured' => false
            ],
            [
                'title' => 'Audit et optimisation de systèmes',
                'summary' => 'Audit complet de vos systèmes existants et recommandations d\'optimisation.',
                'tag' => 'Audit',
                'minPrice' => 3000,
                'maxPrice' => 7000,
                'isFeatured' => false
            ],
            [
                'title' => 'Recherche et développement',
                'summary' => 'R&D personnalisée pour explorer de nouvelles technologies et solutions.',
                'tag' => 'R&D',
                'minPrice' => 10000,
                'maxPrice' => 20000,
                'isFeatured' => true
            ]
        ];

        // Lister les fichiers de cover disponibles dans les fixtures
        $fixturesServicesDir = __DIR__ . '/../../fixtures_images/providers/services';
        $coverFiles = [];
        if (is_dir($fixturesServicesDir)) {
            foreach (scandir($fixturesServicesDir) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $path = $fixturesServicesDir . '/' . $entry;
                if (is_file($path)) {
                    $coverFiles[] = $entry;
                }
            }
        }

        foreach ($providers as $providerIndex => $provider) {
            // 2-4 services par provider
            $serviceCount = rand(2, 4);
            $selectedServices = array_rand($serviceTemplates, $serviceCount);

            // Si on a sélectionné un seul service, array_rand retourne un int
            if (!is_array($selectedServices)) {
                $selectedServices = [$selectedServices];
            }

            foreach ($selectedServices as $serviceTemplateIndex) {
                $template = $serviceTemplates[$serviceTemplateIndex];

                $service = new Service();
                $service->setProvider($provider);
                $service->setTitle($template['title']);
                $service->setSummary($template['summary']);
                $service->setMinPrice((string)$template['minPrice']);
                $service->setMaxPrice((string)$template['maxPrice']);
                $service->setIsActive(true);
                $service->setIsFeatured($template['isFeatured']);
                $service->setCreatedAt(new \DateTimeImmutable());

                // Générer le slug
                $slug = $this->slugger->slug($template['title'])->lower()->toString();
                $service->setSlug($slug);

                $manager->persist($service);
                $manager->flush(); // obtenir l'ID du service

                // Assigner une cover non nulle en copiant une image de fixtures
                if (!empty($coverFiles)) {
                    $coverFile = $coverFiles[($providerIndex + $serviceTemplateIndex) % count($coverFiles)];
                    $coverUrl = $this->providerImageService->copyFixtureServiceCover((int) $provider->getId(), (int) $service->getId(), $coverFile);
                    if ($coverUrl !== null) {
                        $service->setCover($coverUrl);
                    } else {
                        // Fallback: définir une valeur non nulle par défaut
                        $ext = strtolower(pathinfo($coverFile, PATHINFO_EXTENSION)) ?: 'jpg';
                        $service->setCover('/images/providers/' . (int) $provider->getId() . '/services/' . (int) $service->getId() . '/cover/service-cover.' . $ext);
                    }
                } else {
                    // Fallback si aucun fichier de fixtures n'est présent
                    $service->setCover('/images/providers/' . (int) $provider->getId() . '/services/' . (int) $service->getId() . '/cover/service-cover.jpg');
                }

                $manager->persist($service);
                $this->addReference('service_' . ($providerIndex + 1) . '_' . rand(1000, 9999), $service);

                // Ajouter un tag s'il existe (chercher une entité unique par title)
                $tag = $this->tagRepository->findOneBy(['title' => $template['tag']]);
                if ($tag !== null) {
                    $service->addTag($tag);
                }

                // Ajouter des sections et du contenu (sans images obligatoires)
                $sectionCount = rand(1, 3);
                for ($si = 1; $si <= $sectionCount; $si++) {
                    $section = new ServiceSection();
                    $section->setService($service);
                    $section->setTitle('Section ' . $si . ' - ' . $service->getTitle());
                    $manager->persist($section);
                    $manager->flush();

                    $contentCount = rand(1, 3);
                    for ($ci = 1; $ci <= $contentCount; $ci++) {
                        $content = new ServiceContent();
                        $content->setServiceSection($section);
                        $content->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
                        $manager->persist($content);
                    }
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProviderFixtures::class,
            TagFixtures::class,
        ];
    }
}
