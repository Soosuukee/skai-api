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

    // Génère un slug unique pour un client
    public function generateSlug(Client $client): string
    {
        $firstName = $client->getFirstName();
        $lastName = $client->getLastName();

        if (!$firstName || !$lastName) {
            throw new \InvalidArgumentException('Le prénom et le nom sont requis pour générer un slug');
        }

        return $this->slugService->slugifyUser(
            $firstName,
            $lastName,
            function (string $slug) {
                return $this->clientRepository->findBySlug($slug) !== null;
            }
        );
    }

    // Met à jour le slug d'un client et le sauvegarde
    public function updateClientSlug(Client $client): void
    {
        $slug = $this->generateSlug($client);
        $client->setSlug($slug);

        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }

    // Génère un slug pour un nouveau client (avant la sauvegarde)
    public function generateSlugForNewClient(string $firstName, string $lastName): string
    {
        if (!$firstName || !$lastName) {
            throw new \InvalidArgumentException('Le prénom et le nom sont requis pour générer un slug');
        }

        return $this->slugService->slugifyUser(
            $firstName,
            $lastName,
            function (string $slug) {
                return $this->clientRepository->findBySlug($slug) !== null;
            }
        );
    }
}
