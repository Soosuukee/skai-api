<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClientSlugService
{
    public function __construct(
        private SlugService $slugService,
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Génère un slug pour un client au format firstname-lastname-{lettre}{9 chiffres}
     */
    public function generateSlug(Client $client): string
    {
        $firstName = $client->getFirstName();
        $lastName = $client->getLastName();

        if (!$firstName || !$lastName) {
            throw new \InvalidArgumentException('Le prénom et le nom sont requis pour générer un slug');
        }

        return $this->slugService->slugifyFullNameWithRandomId($firstName, $lastName);
    }

    /**
     * Génère un slug unique pour un client
     */
    public function generateUniqueSlug(Client $client): string
    {
        $baseSlug = $this->generateSlug($client);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->clientRepository->findBySlug($slug) !== null;
            }
        );
    }

    /**
     * Met à jour le slug d'un client et le sauvegarde
     */
    public function updateClientSlug(Client $client): void
    {
        $slug = $this->generateUniqueSlug($client);
        $client->setSlug($slug);

        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }

    /**
     * Génère un slug pour un nouveau client (avant la sauvegarde)
     */
    public function generateSlugForNewClient(string $firstName, string $lastName): string
    {
        if (!$firstName || !$lastName) {
            throw new \InvalidArgumentException('Le prénom et le nom sont requis pour générer un slug');
        }

        // Générer un slug avec ID aléatoire
        $baseSlug = $this->slugService->slugifyFullNameWithRandomId($firstName, $lastName);

        return $this->slugService->generateUniqueSlug(
            $baseSlug,
            function (string $slug) {
                return $this->clientRepository->findBySlug($slug) !== null;
            }
        );
    }

    /**
     * Met à jour le slug d'un client existant avec son ID
     */
    public function updateSlugWithId(Client $client): void
    {
        if (!$client->getId()) {
            throw new \InvalidArgumentException('Le client doit avoir un ID pour mettre à jour le slug');
        }

        $this->updateClientSlug($client);
    }
}
