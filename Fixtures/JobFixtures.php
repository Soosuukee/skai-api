<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Job;
use Soosuuke\IaPlatform\Repository\JobRepository;

class JobFixtures
{
    private \PDO $pdo;
    private JobRepository $jobRepository;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->jobRepository = new JobRepository();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Job...\n";

        $jobs = [
            'Data Scientist',
            'Ingénieur Machine Learning',
            'Data Analyst',
            'Ingénieur DevOps',
            'Ingénieur Vision par Ordinateur',
            'Spécialiste Deep Learning',
            'Expert en Optimisation GPU',
            'Architecte IA',
            'Chercheur en IA',
            'Spécialiste en Traitement du Langage Naturel'
        ];

        foreach ($jobs as $jobTitle) {
            $slug = strtolower(str_replace(' ', '-', $jobTitle));
            $job = new Job($jobTitle, $slug);
            $this->jobRepository->save($job);
            echo "Job créé : {$jobTitle} (slug: {$slug})\n";
        }

        echo "✅ Fixtures Job chargées avec succès.\n";
    }
}
