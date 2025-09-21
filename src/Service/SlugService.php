<?php

declare(strict_types=1);

namespace App\Service;

class SlugService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const DIGITS = '0123456789';

    private const ACCENTS = [
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

    public function trim(string $text): string
    {
        return trim($text);
    }

    public function toLower(string $text): string
    {
        return strtolower($text);
    }

    public function removeAccents(string $text): string
    {
        return strtr($text, self::ACCENTS);
    }

    public function replaceSpacesWithDashes(string $text): string
    {
        return preg_replace('/\s+/', '-', $text);
    }

    public function generateSlugSuffix(): string
    {
        $digit = self::DIGITS[rand(0, strlen(self::DIGITS) - 1)];

        $letters = '';
        for ($i = 0; $i < 9; $i++) {
            $letters .= self::ALPHABET[rand(0, strlen(self::ALPHABET) - 1)];
        }

        $suffix = $digit . $letters;
        return $suffix;
    }

    public function slugifyUser(string $firstName, string $lastName, ?callable $checkSlugExists = null): string
    {
        $fullName = $firstName . ' ' . $lastName;
        $fullName = $this->trim($fullName);
        $fullName = $this->removeAccents($fullName);
        $fullName = $this->toLower($fullName);
        $fullName = $this->replaceSpacesWithDashes($fullName);

        $suffix = $this->generateSlugSuffix();
        $slugifyUser = $fullName . '-' . $suffix;

        // Si on a une fonction de vérification et que le slug existe, on refait
        if ($checkSlugExists && $checkSlugExists($slugifyUser)) {
            $suffix = $this->generateSlugSuffix();
            $slugifyUser = $fullName . '-' . $suffix;
        }

        return $slugifyUser;
    }

    public function slugify(string $text): string
    {
        $text = $this->trim($text);
        $text = $this->removeAccents($text);
        $text = $this->toLower($text);
        $text = $this->replaceSpacesWithDashes($text);

        return $text;
    }
}
