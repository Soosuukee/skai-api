<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour les pays
 */
class CountryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $countries = [
            'France',
            'Belgique',
            'Suisse',
            'Canada',
            'États-Unis',
            'Royaume-Uni',
            'Allemagne',
            'Espagne',
            'Italie',
            'Portugal',
        ];

        foreach ($countries as $index => $countryName) {
            $country = new Country();
            $country->setName($countryName);

            $manager->persist($country);
            $this->addReference('country_' . ($index + 1), $country);
        }

        $manager->flush();
    }
}
