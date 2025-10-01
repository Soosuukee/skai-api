<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\HardSkill;

class HardSkillSlugService
{
    public function __construct(private SlugService $slugService) {}

    public function generateSlugForNewHardSkill(string $title): string
    {
        return $this->slugService->slugify($title);
    }

    public function updateHardSkillSlug(HardSkill $skill): void
    {
        $skill->setSlug($this->slugService->slugify((string) $skill->getTitle()));
    }
}
