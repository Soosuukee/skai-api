<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Repository\CountryRepository;
use App\Service\ClientImageService;
use App\Service\ClientSlugService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private CountryRepository $countryRepository,
        private ClientSlugService $clientSlugService,
        private ClientImageService $clientImageService,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $countries = $this->countryRepository->findAll();

        $clients = [
            ['firstName' => 'Alexandre', 'lastName' => 'Martin', 'email' => 'alexandre.martin@example.com', 'city' => 'Paris', 'state' => 'Île-de-France', 'postalCode' => '75001', 'address' => '123 Rue de Rivoli', 'profilePicture' => 'clientavatar-01.webp'],
            ['firstName' => 'Sarah', 'lastName' => 'Johnson', 'email' => 'sarah.johnson@example.com', 'city' => 'New York', 'state' => 'New York', 'postalCode' => '10001', 'address' => '456 Broadway', 'profilePicture' => 'clientavatar-02.avif'],
            ['firstName' => 'Yuki', 'lastName' => 'Tanaka', 'email' => 'yuki.tanaka@example.com', 'city' => 'Tokyo', 'state' => 'Japon', 'postalCode' => '100-0001', 'address' => '789 Ginza Street', 'profilePicture' => 'clientavatar-03.webp'],
            ['firstName' => 'Maria', 'lastName' => 'Garcia', 'email' => 'maria.garcia@example.com', 'city' => 'Madrid', 'state' => 'Espagne', 'postalCode' => '28001', 'address' => '321 Gran Via', 'profilePicture' => 'clientavatar-04.jpg'],
            ['firstName' => 'David', 'lastName' => 'Chen', 'email' => 'david.chen@example.com', 'city' => 'Singapore', 'state' => 'Singapour', 'postalCode' => '018956', 'address' => '654 Orchard Road', 'profilePicture' => 'clientavatar-05.webp'],
            ['firstName' => 'Emma', 'lastName' => 'Wilson', 'email' => 'emma.wilson@example.com', 'city' => 'London', 'state' => 'Royaume-Uni', 'postalCode' => 'SW1A 1AA', 'address' => '987 Baker Street', 'profilePicture' => 'clientavatar-06.jpg'],
        ];

        foreach ($clients as $index => $data) {
            $client = new Client();
            $client->setFirstName($data['firstName']);
            $client->setLastName($data['lastName']);
            $client->setEmail($data['email']);
            $client->setCity($data['city']);
            $client->setState($data['state']);
            $client->setPostalCode($data['postalCode']);
            $client->setAddress($data['address']);
            $client->setJoinedAt(new \DateTimeImmutable());
            $client->setPassword('password123');

            $country = $countries[$index % count($countries)];
            $client->setCountry($country);

            // Slug
            $slug = $this->clientSlugService->generateUniqueSlug($client);
            $client->setSlug($slug);

            $manager->persist($client);
            $manager->flush(); // avoir l'id

            // Image de profil
            $url = $this->clientImageService->createClientImageStructure((int) $client->getId(), $data['profilePicture']);
            if ($url) {
                $client->setProfilePicture($url);
            }

            $manager->persist($client);
            $this->addReference('client_' . ($index + 1), $client);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CountryFixtures::class,
        ];
    }
}
