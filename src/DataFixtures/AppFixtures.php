<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures principales qui orchestre le chargement de toutes les fixtures
 * Cette classe est responsable de l'ordre de chargement des données
 */
class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Cette méthode est appelée automatiquement par Doctrine
        // Les fixtures sont chargées selon l'ordre défini dans getDependencies()

        // Aucune donnée directe à charger ici
        // Toutes les données sont chargées par les fixtures dépendantes
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            // L'ordre est important : les fixtures sont chargées dans cet ordre
            CountryFixtures::class,        // 1. Pays
            TagFixtures::class,            // 2. Tags
            SoftSkillFixtures::class,      // 3. Compétences relationnelles
            HardSkillFixtures::class,      // 4. Compétences techniques
            LanguageFixtures::class,       // 5. Langues
            JobFixtures::class,            // 6. Jobs
            ProviderFixtures::class,       // 7. Providers
            ClientFixtures::class,         // 8. Clients
            ServiceFixtures::class,        // 9. Services
            ArticleFixtures::class,        // 10. Articles
        ];
    }
}
