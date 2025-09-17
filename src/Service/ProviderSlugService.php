<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Provider;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProviderSlugService
{
    public function __construct(
        private SlugService $slugService,
        private ProviderRepository $providerRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Génère un slug pour un provider au format firstname-lastname-{lettre}{9 chiffres}
     */
    public function generateSlug(Provider $provider): string
    {
        $firstName = $provider->getFirstName();
        $lastName = $provider->getLastName();

        if (!$firstName || !$lastName) {
            throw new \InvalidArgumentException('Le prénom et le nom sont requis pour générer un slug');
        }

        return $this->slugService->slugifyFullNameWithRandomId($firstName, $lastName);
    }

    /**
     * Génère un slug unique pour un provider
     */
    public function generateUniqueSlug(Provider $provider): string
    {
        $baseSlug = $this->generateSlug($provider);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->providerRepository->findBySlug($slug) !== null;
            }
        );
    }

    /**
     * Met à jour le slug d'un provider et le sauvegarde
     */
    public function updateProviderSlug(Provider $provider): void
    {
        $slug = $this->generateUniqueSlug($provider);
        $provider->setSlug($slug);

        $this->entityManager->persist($provider);
        $this->entityManager->flush();
    }

    /**
     * Génère un slug pour un nouveau provider (avant la sauvegarde)
     */
    public function generateSlugForNewProvider(string $firstName, string $lastName): string
    {
        if (!$firstName || !$lastName) {
            throw new \InvalidArgumentException('Le prénom et le nom sont requis pour générer un slug');
        }

        // Générer un slug avec ID aléatoire
        $baseSlug = $this->slugService->slugifyFullNameWithRandomId($firstName, $lastName);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->providerRepository->findBySlug($slug) !== null;
            }
        );
    }

    /**
     * Met à jour le slug d'un provider existant avec son ID
     */
    public function updateSlugWithId(Provider $provider): void
    {
        if (!$provider->getId()) {
            throw new \InvalidArgumentException('Le provider doit avoir un ID pour mettre à jour le slug');
        }

        $this->updateProviderSlug($provider);
    }
}
