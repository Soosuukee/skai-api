<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\SoftSkill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les compétences relationnelles
 */
class SoftSkillFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $softSkills = [
            'Communication',
            'Travail en équipe',
            'Leadership',
            'Gestion de projet',
            'Résolution de problèmes',
            'Créativité',
            'Adaptabilité',
            'Esprit critique',
            'Empathie',
            'Négociation',
            'Présentation',
            'Gestion du temps',
            'Organisation',
            'Motivation',
            'Flexibilité',
            'Collaboration',
            'Mentorat',
            'Innovation',
            'Pensée analytique',
            'Intelligence émotionnelle',
        ];

        foreach ($softSkills as $index => $skillTitle) {
            $skill = new SoftSkill();
            $skill->setTitle($skillTitle);

            $manager->persist($skill);
            $this->addReference('softskill_' . ($index + 1), $skill);
        }

        $manager->flush();
    }
}
