<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SoftSkill;

class SoftSkillSlugService
{
    public function __construct(private SlugService $slugService) {}

    public function generateSlugForNewSoftSkill(string $title): string
    {
        return $this->slugService->slugify($title);
    }

    public function updateSoftSkillSlug(SoftSkill $skill): void
    {
        $skill->setSlug($this->slugService->slugify((string) $skill->getTitle()));
    }
}
