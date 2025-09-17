<?php

declare(strict_types=1);

namespace Soosuuke\IaPlatform\Fixtures;

use Soosuuke\IaPlatform\Config\Database;
use Soosuuke\IaPlatform\Entity\Provider;
use Soosuuke\IaPlatform\Repository\ProviderRepository;
use Soosuuke\IaPlatform\Service\ProviderSlugificationService;
use Soosuuke\IaPlatform\Service\ProviderImageService;
use Soosuuke\IaPlatform\Repository\ProviderSoftSkillRepository;
use Soosuuke\IaPlatform\Repository\ProviderHardSkillRepository;
use Soosuuke\IaPlatform\Repository\ProviderLanguageRepository;

class ProviderFixtures
{
    private \PDO $pdo;
    private ProviderRepository $providerRepository;
    private ProviderSlugificationService $slugificationService;
    private ProviderImageService $imageService;
    private ProviderSoftSkillRepository $providerSoftSkillRepository;
    private ProviderHardSkillRepository $providerHardSkillRepository;
    private ProviderLanguageRepository $providerLanguageRepository;

    public function __construct()
    {
        $this->pdo = Database::connect();
        $this->providerRepository = new ProviderRepository();
        $this->slugificationService = new ProviderSlugificationService();
        $this->imageService = new ProviderImageService();
        $this->providerSoftSkillRepository = new ProviderSoftSkillRepository();
        $this->providerHardSkillRepository = new ProviderHardSkillRepository();
        $this->providerLanguageRepository = new ProviderLanguageRepository();
    }

    public function load(): void
    {
        echo "Chargement des fixtures Provider...\n";

        // Données des providers depuis le JSON
        $providersData = [
            [
                'firstName' => 'Jensen',
                'lastName' => 'Huang',
                'email' => 'jensen.huang@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'jobId' => 7, // Expert en Optimisation GPU
                'countryId' => 9, // Taïwan
                'city' => 'Taipei',
                'profilePicture' => 'avatar-jh.jpg'
            ],
            [
                'firstName' => 'Marie',
                'lastName' => 'Dubois',
                'email' => 'marie.dubois@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'jobId' => 2, // Ingénieure Machine Learning
                'countryId' => 1, // France
                'city' => 'Paris',
                'profilePicture' => 'avatar-md.webp'
            ],
            [
                'firstName' => 'Carlos',
                'lastName' => 'Garcia',
                'email' => 'carlos.garcia@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'jobId' => 3, // Data Scientist
                'countryId' => 10, // Mexique
                'city' => 'Mexico',
                'profilePicture' => 'avatar-gc.jpg'
            ],
            [
                'firstName' => 'Akira',
                'lastName' => 'Tanaka',
                'email' => 'akira.tanaka@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'jobId' => 5, // Ingénieure Vision par Ordinateur
                'countryId' => 3, // Japon
                'city' => 'Tokyo',
                'profilePicture' => 'avatar-at.jpg'
            ],
            [
                'firstName' => 'Elena',
                'lastName' => 'Rossi',
                'email' => 'elena.rossi@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'jobId' => 10, // Spécialiste en Traitement du Langage Naturel
                'countryId' => 4, // Italie
                'city' => 'Rome',
                'profilePicture' => 'avatar-er.webp'
            ],
            [
                'firstName' => 'Raj',
                'lastName' => 'Patel',
                'email' => 'raj.patel@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'jobId' => 6, // Spécialiste Deep Learning
                'countryId' => 5, // Inde
                'city' => 'Mumbai',
                'profilePicture' => 'avatar-rp.webp'
            ]
        ];

        foreach ($providersData as $providerData) {
            $provider = new Provider(
                $providerData['firstName'],
                $providerData['lastName'],
                $providerData['email'],
                $providerData['password'],
                $providerData['jobId'],
                $providerData['countryId'],
                $providerData['city'],
                $providerData['profilePicture']
            );

            // Générer le slug automatiquement
            $slug = $this->slugificationService->generateProviderSlug(
                $providerData['firstName'],
                $providerData['lastName'],
                function ($slug) {
                    return $this->providerRepository->findBySlug($slug) !== null;
                }
            );
            $provider->setSlug($slug);

            $this->providerRepository->save($provider);

            // Créer la structure de dossiers et copier les images, puis stocker l'URL finale
            $publicUrl = $this->imageService->createProviderImageStructure($provider->getId(), $providerData['profilePicture']);
            if ($publicUrl) {
                $provider->setProfilePicture($publicUrl);
                $this->providerRepository->update($provider);
            }

            // Nettoyer avant de rattacher pour éviter les doublons
            $this->providerSoftSkillRepository->removeAllSkillsFromProvider($provider->getId());
            $this->providerHardSkillRepository->removeAllSkillsFromProvider($provider->getId());
            $this->providerLanguageRepository->removeAllLanguagesFromProvider($provider->getId());

            // Sélections aléatoires (hasard) de compétences et langues
            $allSoftIds = $this->getAllIds('soft_skill');
            $allHardIds = $this->getAllIds('hard_skill');
            $allLangIds = $this->getAllIds('language');

            $softCount = empty($allSoftIds) ? 0 : random_int(2, min(5, count($allSoftIds)));
            $hardCount = empty($allHardIds) ? 0 : random_int(3, min(6, count($allHardIds)));
            $langCount = empty($allLangIds) ? 0 : random_int(1, min(3, count($allLangIds)));

            foreach ($this->pickRandomIds($allSoftIds, $softCount) as $softId) {
                $this->providerSoftSkillRepository->addSkillToProvider($provider->getId(), $softId);
            }

            foreach ($this->pickRandomIds($allHardIds, $hardCount) as $hardId) {
                $this->providerHardSkillRepository->addSkillToProvider($provider->getId(), $hardId);
            }

            foreach ($this->pickRandomIds($allLangIds, $langCount) as $langId) {
                $this->providerLanguageRepository->addLanguageToProvider($provider->getId(), $langId);
            }

            echo "Provider créé : {$providerData['firstName']} {$providerData['lastName']} (slug: $slug)\n";
        }

        echo "✅ Fixtures Provider chargées avec succès.\n";
    }

    private function findIdByColumn(string $table, string $column, string $value): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    private function getAllIds(string $table): array
    {
        $stmt = $this->pdo->query("SELECT id FROM {$table}");
        $ids = [];
        while ($row = $stmt->fetch()) {
            $ids[] = (int)$row['id'];
        }
        return $ids;
    }

    private function pickRandomIds(array $ids, int $count): array
    {
        if ($count <= 0 || empty($ids)) {
            return [];
        }
        $count = min($count, count($ids));
        shuffle($ids);
        return array_slice($ids, 0, $count);
    }
}
