<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ServiceSlugService
{
    public function __construct(
        private SlugService $slugService,
        private ServiceRepository $serviceRepository,
        private EntityManagerInterface $entityManager
    ) {}

    // Génère un slug pour un service basé sur son titre
    public function generateSlug(Service $service): string
    {
        $title = $service->getTitle();

        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        return $this->slugService->slugify($title);
    }

    // Met à jour le slug d'un service et le sauvegarde
    public function updateServiceSlug(Service $service): void
    {
        $slug = $this->generateSlug($service);
        $service->setSlug($slug);

        $this->entityManager->persist($service);
        $this->entityManager->flush();
    }

    // Génère un slug pour un nouveau service
    public function generateSlugForNewService(string $title): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        return $this->slugService->slugify($title);
    }


    // Génère un slug basé sur le titre et le nom du provider
    public function generateSlugWithProvider(string $title, string $providerFirstName, string $providerLastName): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $titleSlug = $this->slugService->slugify($title);
        $providerSlug = $this->slugService->slugify($providerFirstName . '-' . $providerLastName);

        return $titleSlug . '-' . $providerSlug;
    }

    // Génère un slug basé sur le titre et le prix du service
    public function generateSlugWithPrice(string $title, float $minPrice, float $maxPrice): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $titleSlug = $this->slugService->slugify($title);
        $priceRange = $minPrice === $maxPrice ? (string) $minPrice : $minPrice . '-' . $maxPrice;
        $priceSlug = $this->slugService->slugify($priceRange . '-euros');

        return $titleSlug . '-' . $priceSlug;
    }
}
