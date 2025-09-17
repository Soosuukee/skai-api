<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les tags
 */
class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [
            'React',
            'Vue.js',
            'Angular',
            'Node.js',
            'PHP',
            'Python',
            'JavaScript',
            'TypeScript',
            'Symfony',
            'Laravel',
            'Django',
            'Flask',
            'MySQL',
            'PostgreSQL',
            'MongoDB',
            'Redis',
            'Docker',
            'Kubernetes',
            'AWS',
            'Azure',
            'Git',
            'CI/CD',
            'API REST',
            'GraphQL',
            'Microservices',
            'Machine Learning',
            'AI',
            'Blockchain',
            'IoT',
            'Mobile',
            'Web Design',
            'SEO',
            'E-commerce',
            'SaaS',
            'Startup',
        ];

        foreach ($tags as $index => $tagTitle) {
            $tag = new Tag();
            $tag->setTitle($tagTitle);
            $tag->setSlug($this->generateSlug($tagTitle));

            $manager->persist($tag);
            $this->addReference('tag_' . ($index + 1), $tag);
        }

        $manager->flush();
    }

    private function generateSlug(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
