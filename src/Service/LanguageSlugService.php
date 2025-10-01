<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Language;

class LanguageSlugService
{
    public function __construct(private SlugService $slugService) {}

    public function generateSlugForNewLanguage(string $name): string
    {
        return $this->slugService->slugify($name);
    }

    public function updateLanguageSlug(Language $language): void
    {
        $language->setSlug($this->slugService->slugify((string) $language->getName()));
    }
}
