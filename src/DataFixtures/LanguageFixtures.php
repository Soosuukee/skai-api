<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les langues
 */
class LanguageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $languages = [
            'Français',
            'Anglais',
            'Espagnol',
            'Allemand',
            'Italien',
            'Portugais',
            'Néerlandais',
            'Japonais',
            'Chinois',
            'Arabe',
        ];

        foreach ($languages as $index => $languageName) {
            $language = new Language();
            $language->setName($languageName);

            $manager->persist($language);
            $this->addReference('language_' . $languageName, $language);
        }

        $manager->flush();
    }
}
