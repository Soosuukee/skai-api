<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Article;
use Soosuuke\IaPlatform\Repository\ArticleRepository;
use Soosuuke\IaPlatform\Service\ArticleSlugificationService;
use Soosuuke\IaPlatform\Service\ProviderImageService;
use Soosuuke\IaPlatform\Repository\TagRepository;

class ArticleFixtures
{
    private \PDO $pdo;
    private ArticleRepository $articleRepository;
    private ArticleSlugificationService $slugificationService;
    private ProviderImageService $imageService;
    private TagRepository $tagRepository;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->articleRepository = new ArticleRepository();
        $this->slugificationService = new ArticleSlugificationService();
        $this->imageService = new ProviderImageService();
        $this->tagRepository = new TagRepository();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Article...\n";

        $rows = [
            // Provider 2 (2 articles)
            [
                'providerId' => 2,
                'languageId' => 1, // Français
                'title' => 'ML industriel: bonnes pratiques',
                'summary' => "Tour d'horizon des pratiques essentielles pour fiabiliser vos projets ML.",
                'tag' => 'ML',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'pratiques.png'
            ],
            [
                'providerId' => 2,
                'languageId' => 2, // Anglais
                'title' => 'Industrial ML: Best Practices',
                'summary' => "Overview of essential practices to make your ML projects reliable.",
                'tag' => 'ML',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'optim.png'
            ],
            // Provider 3
            [
                'providerId' => 3,
                'languageId' => 1, // Français
                'title' => 'La data science au service des décisions',
                'summary' => 'Comment transformer les données en décisions actionnables.',
                'tag' => 'Data',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'decisions.png'
            ],
            [
                'providerId' => 3,
                'languageId' => 3, // Espagnol
                'title' => 'Feature store: fundamentos',
                'summary' => 'Estructurar, versionar y reutilizar tus features ML.',
                'tag' => 'MLOps',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'feature-store.png'
            ],
            // Provider 4
            [
                'providerId' => 4,
                'languageId' => 2, // Anglais
                'title' => 'AI R&D: from prototype to product',
                'summary' => 'Iterate fast and well to validate business value.',
                'tag' => 'R&D',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'proto-produit.png'
            ],
            [
                'providerId' => 4,
                'languageId' => 4, // Allemand
                'title' => 'KI-Systeme bewerten: welche Metriken?',
                'summary' => 'Verstehen Sie Präzision, Recall, AUC, Kalibrierung und Robustheit.',
                'tag' => 'Évaluation',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'metrics.png'
            ],
            // Provider 5
            [
                'providerId' => 5,
                'languageId' => 1, // Français
                'title' => 'Réussir vos projets de vision par ordinateur',
                'summary' => 'De la collecte des données au déploiement sur le terrain.',
                'tag' => 'CV',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'projets.png'
            ],
            [
                'providerId' => 5,
                'languageId' => 5, // Italien
                'title' => 'MLOps: costruire pipeline robusti',
                'summary' => 'CI/CD, tracciabilità, monitoraggio: gli elementi essenziali.',
                'tag' => 'MLOps',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'pipelines.avif'
            ],
            // Provider 6
            [
                'providerId' => 6,
                'languageId' => 2, // Anglais
                'title' => 'NLP: concrete use cases',
                'summary' => 'Information extraction, agents, RAG: overview.',
                'tag' => 'NLP',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'casusages.png'
            ],
            [
                'providerId' => 6,
                'languageId' => 1, // Français
                'title' => 'Fine-tuning de grands modèles',
                'summary' => 'Choisir la bonne stratégie: full, LoRA, adapters.',
                'tag' => 'LLM',
                'isPublished' => true,
                'isFeatured' => false,
                'cover' => 'finetune.png'
            ],
        ];

        foreach ($rows as $row) {
            $slug = $this->slugificationService->generateArticleSlug(
                $row['title'],
                function (string $candidate): bool {
                    return $this->articleRepository->findBySlug($candidate) !== null;
                }
            );

            $article = new Article(
                (int) $row['providerId'],
                (int) $row['languageId'],
                (string) $row['title'],
                (string) $row['summary'],
                $slug,
                (bool) $row['isPublished'],
                (bool) $row['isFeatured'],
                $row['cover'] ?? null
            );

            // Sections + contenus par défaut
            $sections = [
                [
                    'title' => 'Introduction',
                    'contents' => [
                        ['content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.']
                    ]
                ],
                [
                    'title' => 'Développement',
                    'contents' => [
                        ['content' => 'Suspendisse potenti. Curabitur pharetra massa at blandit venenatis.']
                    ]
                ],
                [
                    'title' => 'Conclusion',
                    'contents' => [
                        ['content' => 'Praesent in tellus at mauris gravida faucibus. Nulla facilisi.']
                    ]
                ],
            ];

            $this->articleRepository->saveArticleWithContent($article, $sections);

            // Insérer une image de contenu par défaut (URL publique) pour la première section
            $articleId = (int)$article->getId();
            $stmt = $this->pdo->prepare('SELECT id FROM article_section WHERE article_id = ? ORDER BY id');
            $stmt->execute([$articleId]);
            $sectionRow = $stmt->fetch();
            if ($sectionRow) {
                $stmt2 = $this->pdo->prepare('SELECT id FROM article_content WHERE article_content_id = ? ORDER BY id');
                $stmt2->execute([$sectionRow['id']]);
                $contentRow = $stmt2->fetch();
                if ($contentRow && !empty($row['cover'])) {
                    $url = $this->imageService->copyFixtureArticleImage((int)$row['providerId'], $articleId, (string)$row['cover']);
                    if ($url) {
                        $stmt3 = $this->pdo->prepare('INSERT INTO article_image (article_content_id, url) VALUES (?, ?)');
                        $stmt3->execute([$contentRow['id'], $url]);
                    }
                }
            }
            // Associer un tag s'il existe
            if (!empty($row['tag'])) {
                $tag = $this->tagRepository->findByTitle($row['tag']);
                if ($tag) {
                    $stmtTag = $this->pdo->prepare('INSERT INTO article_tag (article_id, tag_id) VALUES (?, ?)');
                    $stmtTag->execute([$article->getId(), $tag->getId()]);
                }
            }

            // Copier cover si fournie et stocker l'URL relative finale
            if (!empty($row['cover'])) {
                $finalUrl = $this->imageService->copyFixtureArticleImage((int)$row['providerId'], (int)$article->getId(), (string)$row['cover']);
                if ($finalUrl) {
                    $article->setCover($finalUrl);
                    $this->articleRepository->update($article);
                }
            }
            echo "Article créé (avec contenu): {$row['title']} (providerId: {$row['providerId']})\n";
        }

        echo "✅ Fixtures Article chargées avec succès.\n";
    }
}
