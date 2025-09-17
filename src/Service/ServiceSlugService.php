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

    /**
     * Génère un slug pour un service basé sur son titre
     */
    public function generateSlug(Service $service): string
    {
        $title = $service->getTitle();

        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        return $this->slugService->slugifyTitle($title);
    }

    /**
     * Génère un slug unique pour un service
     */
    public function generateUniqueSlug(Service $service): string
    {
        $baseSlug = $this->generateSlug($service);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) use ($service) {
                $existingService = $this->serviceRepository->findBySlug($slug);
                // Exclure le service actuel s'il existe déjà
                return $existingService !== null && $existingService->getId() !== $service->getId();
            }
        );
    }

    /**
     * Met à jour le slug d'un service et le sauvegarde
     */
    public function updateServiceSlug(Service $service): void
    {
        $slug = $this->generateUniqueSlug($service);
        $service->setSlug($slug);

        $this->entityManager->persist($service);
        $this->entityManager->flush();
    }

    /**
     * Génère un slug pour un nouveau service
     */
    public function generateSlugForNewService(string $title): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $baseSlug = $this->slugService->slugifyTitle($title);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->serviceRepository->findBySlug($slug) !== null;
            }
        );
    }

    /**
     * Met à jour le slug d'un service existant
     */
    public function updateSlugForExistingService(Service $service): void
    {
        $this->updateServiceSlug($service);
    }

    /**
     * Génère un slug basé sur le titre et le nom du provider
     */
    public function generateSlugWithProvider(string $title, string $providerFirstName, string $providerLastName): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $titleSlug = $this->slugService->slugifyTitle($title);
        $providerSlug = $this->slugService->slugify($providerFirstName . '-' . $providerLastName);

        $baseSlug = $titleSlug . '-' . $providerSlug;

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->serviceRepository->findBySlug($slug) !== null;
            }
        );
    }

    /**
     * Génère un slug basé sur le titre et le prix du service
     */
    public function generateSlugWithPrice(string $title, float $minPrice, float $maxPrice): string
    {
        if (!$title) {
            throw new \InvalidArgumentException('Le titre est requis pour générer un slug');
        }

        $titleSlug = $this->slugService->slugifyTitle($title);
        $priceRange = $minPrice === $maxPrice ? (string) $minPrice : $minPrice . '-' . $maxPrice;
        $priceSlug = $this->slugService->slugify($priceRange . '-euros');

        $baseSlug = $titleSlug . '-' . $priceSlug;

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->serviceRepository->findBySlug($slug) !== null;
            }
        );
    }
}
