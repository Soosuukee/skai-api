<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Job;

class JobSlugService
{
    public function __construct(private SlugService $slugService) {}

    public function generateSlugForNewJob(string $title): string
    {
        return $this->slugService->slugify($title);
    }

    public function updateJobSlug(Job $job): void
    {
        $job->setSlug($this->slugService->slugify((string) $job->getTitle()));
    }
}
