<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Tag;
use Soosuuke\IaPlatform\Repository\TagRepository;
use Soosuuke\IaPlatform\Service\TagSlugificationService;

class TagFixtures
{
    private \PDO $pdo;
    private TagRepository $tagRepository;
    private TagSlugificationService $slugificationService;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->tagRepository = new TagRepository();
        $this->slugificationService = new TagSlugificationService();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Tag...\n";

        $tags = [
            // IA Générale
            'Intelligence Artificielle',
            'Machine Learning',
            'Deep Learning',
            'Neural Networks',
            'Computer Vision',
            'Natural Language Processing',
            'Reinforcement Learning',
            'AI Ethics',
            'Explainable AI',
            'AI Safety',

            // Frameworks et Outils
            'TensorFlow',
            'PyTorch',
            'Keras',
            'Scikit-learn',
            'JAX',
            'Hugging Face',
            'OpenAI',
            'LangChain',
            'Streamlit',
            'Gradio',

            // Applications Spécifiques
            'Chatbots',
            'Voice Recognition',
            'Image Recognition',
            'Object Detection',
            'Text Generation',
            'Translation',
            'Sentiment Analysis',
            'Recommendation Systems',
            'Fraud Detection',
            'Predictive Analytics',

            // Technologies Avancées
            'Transformers',
            'BERT',
            'GPT',
            'CNN',
            'RNN',
            'LSTM',
            'GANs',
            'AutoML',
            'Federated Learning',
            'Edge AI',

            // Domaines d'Application
            'Healthcare AI',
            'Financial AI',
            'Autonomous Vehicles',
            'Robotics',
            'IoT',
            'Big Data',
            'Data Science',
            'Business Intelligence',
            'Cybersecurity',
            'Climate AI'
        ];

        // Harmonisation avec les intitulés utilisés dans ServiceFixtures et ArticleFixtures
        $extraTitles = [
            // Abréviations / variations présentes dans les fixtures
            'ML',
            'MLOps',
            'R&D',
            'CV',
            'LLM',
            'Évaluation',
            'BI',
            'NLP',
            'Data'
        ];

        foreach ($extraTitles as $t) {
            if (!in_array($t, $tags, true)) {
                $tags[] = $t;
            }
        }

        foreach ($tags as $tagTitle) {
            $slug = $this->slugificationService->generateTagSlug(
                $tagTitle,
                function (string $candidate): bool {
                    return $this->tagRepository->findBySlug($candidate) !== null;
                }
            );

            $tag = new Tag($tagTitle, null, $slug);
            $this->tagRepository->save($tag);
            echo "Tag créé : {$tagTitle} (slug: {$slug})\n";
        }

        echo "✅ Fixtures Tag chargées avec succès.\n";
    }
}
