<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Country;
use Soosuuke\IaPlatform\Repository\CountryRepository;

class CountryFixtures
{
    private \PDO $pdo;
    private CountryRepository $countryRepository;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->countryRepository = new CountryRepository();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Country...\n";

        // Données des pays depuis le JSON
        $countriesData = [
            ['name' => 'France'],
            ['name' => 'États-Unis'],
            ['name' => 'Japon'],
            ['name' => 'Italie'],
            ['name' => 'Inde'],
            ['name' => 'Espagne'],
            ['name' => 'Singapour'],
            ['name' => 'Royaume-Uni'],
            ['name' => 'Taïwan'],
            ['name' => 'Mexique']
        ];

        foreach ($countriesData as $countryData) {
            $country = new Country($countryData['name']);
            $this->countryRepository->save($country);
            echo "Pays créé : {$countryData['name']}\n";
        }

        echo "✅ Fixtures Country chargées avec succès.\n";
    }
}
