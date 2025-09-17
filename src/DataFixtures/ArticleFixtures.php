<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleSection;
use App\Entity\ArticleContent;
use App\Entity\Language;
use App\Repository\ProviderRepository;
use App\Repository\TagRepository;
use App\Service\ProviderImageService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Fixtures pour les articles
 */
class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private TagRepository $tagRepository,
        private SluggerInterface $slugger,
        private ProviderImageService $providerImageService
    ) {}

    public function load(ObjectManager $manager): void
    {
        $providers = $this->providerRepository->findAll();

        if (empty($providers)) {
            return; // Pas de providers, pas d'articles
        }

        // Les langues sont déjà créées par LanguageFixtures

        $articleTemplates = [
            [
                'title' => 'Guide complet de l\'Intelligence Artificielle',
                'summary' => 'Découvrez les fondamentaux de l\'IA et ses applications pratiques dans le monde moderne.',
                'tag' => 'IA',
                'isFeatured' => true
            ],
            [
                'title' => 'Machine Learning : de la théorie à la pratique',
                'summary' => 'Explorez les concepts clés du machine learning et leurs implémentations concrètes.',
                'tag' => 'Machine Learning',
                'isFeatured' => true
            ],
            [
                'title' => 'Data Science : transformer les données en insights',
                'summary' => 'Apprenez à extraire de la valeur de vos données grâce aux techniques de data science.',
                'tag' => 'Data Science',
                'isFeatured' => false
            ],
            [
                'title' => 'Les tendances technologiques de 2024',
                'summary' => 'Un aperçu des technologies émergentes qui façonneront l\'avenir du numérique.',
                'tag' => 'Technologie',
                'isFeatured' => false
            ],
            [
                'title' => 'Optimisation des performances en développement',
                'summary' => 'Conseils et techniques pour améliorer les performances de vos applications.',
                'tag' => 'Optimisation',
                'isFeatured' => false
            ],
            [
                'title' => 'Cybersécurité : protéger votre infrastructure',
                'summary' => 'Guide pratique pour sécuriser vos systèmes et données contre les menaces.',
                'tag' => 'Sécurité',
                'isFeatured' => true
            ]
        ];

        // Lister les fichiers d'articles disponibles dans les fixtures pour servir de cover
        $fixturesArticlesDir = __DIR__ . '/../../fixtures_images/providers/articles';
        $articleFiles = [];
        if (is_dir($fixturesArticlesDir)) {
            foreach (scandir($fixturesArticlesDir) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $path = $fixturesArticlesDir . '/' . $entry;
                if (is_file($path)) {
                    $articleFiles[] = $entry;
                }
            }
        }

        foreach ($providers as $providerIndex => $provider) {
            // 2-4 articles par provider
            $articleCount = rand(2, 4);
            $selectedArticles = array_rand($articleTemplates, $articleCount);

            // Si on a sélectionné un seul article, array_rand retourne un int
            if (!is_array($selectedArticles)) {
                $selectedArticles = [$selectedArticles];
            }

            foreach ($selectedArticles as $articleTemplateIndex) {
                $template = $articleTemplates[$articleTemplateIndex];

                $article = new Article();
                $article->setProvider($provider);

                // Sélectionner une langue aléatoire
                $languageNames = ['Français', 'Anglais', 'Espagnol', 'Allemand', 'Italien'];
                $randomLanguage = $languageNames[array_rand($languageNames)];
                $language = $this->getReference('language_' . $randomLanguage, Language::class);
                $article->setLanguage($language);

                $article->setTitle($template['title']);
                $article->setSummary($template['summary']);
                $article->setIsPublished(true);
                $article->setIsFeatured($template['isFeatured']);
                $article->setPublishedAt(new \DateTimeImmutable());
                $article->setUpdatedAt(new \DateTimeImmutable());

                // Générer le slug
                $slug = $this->slugger->slug($template['title'])->lower()->toString();
                $article->setSlug($slug);

                $manager->persist($article);
                $manager->flush(); // obtenir l'ID de l'article

                // Copier une image de cover d'article depuis fixtures si disponible
                if (!empty($articleFiles)) {
                    $coverFile = $articleFiles[($providerIndex + $articleTemplateIndex) % count($articleFiles)];
                    $coverUrl = $this->providerImageService->copyFixtureArticleImage((int) $provider->getId(), (int) $article->getId(), $coverFile);
                    if ($coverUrl !== null) {
                        $article->setCover($coverUrl);
                    }
                }

                $manager->persist($article);
                $this->addReference('article_' . ($providerIndex + 1) . '_' . rand(1000, 9999), $article);

                // Ajouter un tag s'il existe (chercher une entité unique par title)
                $tag = $this->tagRepository->findOneBy(['title' => $template['tag']]);
                if ($tag !== null) {
                    $article->addTag($tag);
                }

                // Ajouter des sections et du contenu (images non obligatoires)
                $sectionCount = rand(1, 3);
                for ($si = 1; $si <= $sectionCount; $si++) {
                    $section = new ArticleSection();
                    $section->setArticle($article);
                    $section->setTitle('Section ' . $si . ' - ' . $article->getTitle());
                    $manager->persist($section);
                    $manager->flush();

                    $contentCount = rand(1, 3);
                    for ($ci = 1; $ci <= $contentCount; $ci++) {
                        $content = new ArticleContent();
                        $content->setArticleSection($section);
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
            LanguageFixtures::class,
        ];
    }
}
