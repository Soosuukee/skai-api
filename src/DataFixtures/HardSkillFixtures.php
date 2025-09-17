<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\HardSkill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les compétences techniques
 */
class HardSkillFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $hardSkills = [
            'Programmation',
            'Développement Web',
            'Développement Mobile',
            'Base de données',
            'Architecture Logicielle',
            'DevOps',
            'Cloud Computing',
            'Cybersécurité',
            'Machine Learning',
            'Data Science',
            'Blockchain',
            'IoT',
            'API Development',
            'Microservices',
            'Containerization',
            'Version Control',
            'Testing',
            'Performance Optimization',
            'System Design',
            'Network Administration',
        ];

        foreach ($hardSkills as $index => $skillTitle) {
            $skill = new HardSkill();
            $skill->setTitle($skillTitle);

            $manager->persist($skill);
            $this->addReference('hardskill_' . ($index + 1), $skill);
        }

        $manager->flush();
    }
}
