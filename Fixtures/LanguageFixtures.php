<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Language;
use Soosuuke\IaPlatform\Repository\LanguageRepository;

class LanguageFixtures
{
    private \PDO $pdo;
    private LanguageRepository $languageRepository;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->languageRepository = new LanguageRepository();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Language...\n";

        $languages = [
            'Français',
            'Anglais',
            'Espagnol',
            'Allemand',
            'Italien',
            'Portugais',
            'Russe',
            'Chinois',
            'Japonais',
            'Coréen'
        ];

        foreach ($languages as $languageName) {
            $language = new Language($languageName);
            $this->languageRepository->save($language);
            echo "Langue créée : {$languageName}\n";
        }

        echo "✅ Fixtures Language chargées avec succès.\n";
    }
}
