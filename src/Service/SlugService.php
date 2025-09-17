<?php

declare(strict_types=1);

namespace App\Service;

class SlugService
{
    /**
     * Génère un slug à partir d'un texte
     */
    public function slugify(string $text): string
    {
        // Supprimer les accents
        $text = $this->removeAccents($text);

        // Convertir en minuscules
        $text = strtolower($text);

        // Remplacer les caractères spéciaux par des tirets
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Supprimer les tirets en début et fin
        $text = trim($text, '-');

        // Supprimer les tirets multiples
        $text = preg_replace('/-+/', '-', $text);

        return $text;
    }

    /**
     * Génère un slug unique en ajoutant un suffixe numérique si nécessaire
     */
    public function generateUniqueSlug(string $baseSlug, callable $checkSlugExists, int $maxAttempts = 100): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while ($checkSlugExists($slug) && $counter <= $maxAttempts) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        if ($counter > $maxAttempts) {
            throw new \RuntimeException('Impossible de générer un slug unique après ' . $maxAttempts . ' tentatives');
        }

        return $slug;
    }

    /**
     * Supprime les accents d'une chaîne
     */
    private function removeAccents(string $text): string
    {
        $accents = [
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'Ç' => 'C',
            'ç' => 'c',
            'Ñ' => 'N',
            'ñ' => 'n'
        ];

        return strtr($text, $accents);
    }

    /**
     * Génère un ID court à partir d'un ID numérique
     */
    public function generateShortId(int $id): string
    {
        // Convertir l'ID en base 36 pour obtenir une chaîne plus courte
        return base_convert((string) $id, 10, 36);
    }

    /**
     * Génère un ID aléatoire au format {lettre}{9 chiffres}
     */
    public function generateRandomId(): string
    {
        // Générer une lettre aléatoire de A à Z
        $letter = chr(rand(65, 90)); // A-Z en ASCII

        // Générer 9 chiffres aléatoires
        $digits = '';
        for ($i = 0; $i < 9; $i++) {
            $digits .= rand(0, 9);
        }

        return $letter . $digits;
    }

    /**
     * Génère un slug pour un nom complet (prénom-nom-id)
     */
    public function slugifyFullName(string $firstName, string $lastName, int $id): string
    {
        $firstNameSlug = $this->slugify($firstName);
        $lastNameSlug = $this->slugify($lastName);
        $shortId = $this->generateShortId($id);

        return $firstNameSlug . '-' . $lastNameSlug . '-' . $shortId;
    }

    /**
     * Génère un slug pour un nom complet avec ID aléatoire (prénom-nom-{lettre}{9 chiffres})
     */
    public function slugifyFullNameWithRandomId(string $firstName, string $lastName): string
    {
        $firstNameSlug = $this->slugify($firstName);
        $lastNameSlug = $this->slugify($lastName);
        $randomId = $this->generateRandomId();

        return $firstNameSlug . '-' . $lastNameSlug . '-' . $randomId;
    }

    /**
     * Génère un slug pour un titre
     */
    public function slugifyTitle(string $title): string
    {
        return $this->slugify($title);
    }
}
