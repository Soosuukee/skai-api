<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Job;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class JobFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $titles = [
            'Data Scientist',
            'Ingénieur Machine Learning',
            'Data Analyst',
            'Ingénieur DevOps',
            'Ingénieur Vision par Ordinateur',
            'Spécialiste Deep Learning',
            'Architecte IA',
            'Chercheur en IA',
            'Spécialiste NLP',
        ];

        foreach ($titles as $index => $title) {
            $job = new Job();
            $job->setTitle($title);
            $job->setSlug($this->slugify($title));

            $manager->persist($job);
            $this->addReference('job_' . ($index + 1), $job);
        }

        $manager->flush();
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: 'n-a';
    }
}
