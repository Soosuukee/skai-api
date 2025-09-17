<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\SoftSkill;
use Soosuuke\IaPlatform\Entity\HardSkill;
use Soosuuke\IaPlatform\Repository\SoftSkillRepository;
use Soosuuke\IaPlatform\Repository\HardSkillRepository;

class SkillFixtures
{
    private \PDO $pdo;
    private SoftSkillRepository $softSkillRepository;
    private HardSkillRepository $hardSkillRepository;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->softSkillRepository = new SoftSkillRepository();
        $this->hardSkillRepository = new HardSkillRepository();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Skills...\n";

        // Soft Skills (compétences comportementales)
        $softSkills = [
            'Communication',
            'Leadership',
            'Travail d\'équipe',
            'Résolution de problèmes',
            'Adaptabilité',
            'Créativité',
            'Gestion du temps',
            'Empathie',
            'Négociation',
            'Pensée critique'
        ];

        // Hard Skills (compétences techniques) depuis le JSON
        $hardSkills = [
            'CUDA',
            'TensorFlow',
            'PyTorch',
            'JAX',
            'Keras',
            'MXNet',
            'Caffe',
            'ONNX',
            'TensorRT',
            'HuggingFace Transformers',
            'BERT',
            'GPT',
            'CNN',
            'RNN',
            'LSTM',
            'GRU',
            'Transformer',
            'Attention Mechanisms',
            'Computer Vision',
            'Object Detection',
            'Image Segmentation',
            'GANs',
            'Reinforcement Learning',
            'Q-Learning',
            'Policy Gradient',
            'Deep RL',
            'RLHF',
            'Traitement du langage naturel',
            'Text Classification',
            'Named Entity Recognition',
            'Machine Translation',
            'Speech Recognition',
            'Audio Processing',
            'Time Series Forecasting',
            'Recommendation Systems',
            'Anomaly Detection',
            'Graph Neural Networks',
            'Distributed Training',
            'Data Parallelism',
            'Horovod',
            'DeepSpeed',
            'LoRA',
            'Quantization',
            'Pruning',
            'Model Compression',
            'Explainable AI',
            'AutoML',
            'Hyperparameter Tuning',
            'Bayesian Optimization',
            'MLOps',
            'Kubeflow',
            'MLflow',
            'Apache Airflow',
            'Spark MLlib',
            'Dask',
            'NVIDIA Triton Inference Server',
            'Edge AI',
            'Federated Learning',
            'Differential Privacy',
            'Synthetic Data Generation'
        ];

        // Créer les Soft Skills
        foreach ($softSkills as $skillName) {
            $softSkill = new SoftSkill($skillName);
            $this->softSkillRepository->save($softSkill);
            echo "Soft Skill créé : $skillName\n";
        }

        // Créer les Hard Skills
        foreach ($hardSkills as $skillName) {
            $hardSkill = new HardSkill($skillName);
            $this->hardSkillRepository->save($hardSkill);
            echo "Hard Skill créé : $skillName\n";
        }

        echo "✅ Fixtures Skills chargées avec succès.\n";
    }
}
