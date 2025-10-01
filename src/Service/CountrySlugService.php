<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Country;

class CountrySlugService
{
    public function __construct(private SlugService $slugService) {}

    public function generateSlugForNewCountry(string $name): string
    {
        return $this->slugService->slugify($name);
    }

    public function updateCountrySlug(Country $country): void
    {
        $country->setSlug($this->slugService->slugify((string) $country->getName()));
    }
}
