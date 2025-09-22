<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Provider;
use App\Entity\Job;
use App\Entity\Experience;
use App\Entity\Education;
use App\Repository\JobRepository;
use App\Repository\CountryRepository;
use App\Repository\LanguageRepository;
use App\Repository\HardSkillRepository;
use App\Repository\SoftSkillRepository;
use App\Service\ProviderImageService;
use App\Service\ProviderSlugService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProviderFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private CountryRepository $countryRepository,
        private ProviderSlugService $providerSlugService,
        private ProviderImageService $providerImageService,
        private JobRepository $jobRepository,
        private LanguageRepository $languageRepository,
        private HardSkillRepository $hardSkillRepository,
        private SoftSkillRepository $softSkillRepository,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $countries = $this->countryRepository->findAll();
        $jobs = $this->jobRepository->findAll();
        $languages = $this->languageRepository->findAll();
        $hardSkills = $this->hardSkillRepository->findAll();
        $softSkills = $this->softSkillRepository->findAll();

        $providers = [
            ['firstName' => 'Jensen', 'lastName' => 'Huang', 'email' => 'jensen.huang@example.com', 'city' => 'Taipei', 'state' => 'Taïwan', 'postalCode' => '100', 'address' => '123 Tech Street', 'profilePicture' => 'avatar-jh.jpg'],
            ['firstName' => 'Marie', 'lastName' => 'Dubois', 'email' => 'marie.dubois@example.com', 'city' => 'Paris', 'state' => 'Île-de-France', 'postalCode' => '75001', 'address' => '456 Avenue des Champs', 'profilePicture' => 'avatar-md.webp'],
            ['firstName' => 'Carlos', 'lastName' => 'Garcia', 'email' => 'carlos.garcia@example.com', 'city' => 'Mexico', 'state' => 'Mexique', 'postalCode' => '06000', 'address' => '789 Boulevard Central', 'profilePicture' => 'avatar-gc.jpg'],
            ['firstName' => 'Akira', 'lastName' => 'Tanaka', 'email' => 'akira.tanaka@example.com', 'city' => 'Tokyo', 'state' => 'Japon', 'postalCode' => '100-0001', 'address' => '321 Shibuya Street', 'profilePicture' => 'avatar-at.jpg'],
            ['firstName' => 'Elena', 'lastName' => 'Rossi', 'email' => 'elena.rossi@example.com', 'city' => 'Rome', 'state' => 'Italie', 'postalCode' => '00100', 'address' => '654 Via Roma', 'profilePicture' => 'avatar-er.webp'],
            ['firstName' => 'Raj', 'lastName' => 'Patel', 'email' => 'raj.patel@example.com', 'city' => 'Mumbai', 'state' => 'Inde', 'postalCode' => '400001', 'address' => '987 Marine Drive', 'profilePicture' => 'avatar-rp.webp'],
        ];

        foreach ($providers as $index => $data) {
            $provider = new Provider();
            $provider->setFirstName($data['firstName']);
            $provider->setLastName($data['lastName']);
            $provider->setEmail($data['email']);
            $provider->setCity($data['city']);
            $provider->setState($data['state']);
            $provider->setPostalCode($data['postalCode']);
            $provider->setAddress($data['address']);
            $provider->setJoinedAt(new \DateTimeImmutable());
            $provider->setPassword('password123');

            // Générer une date de naissance aléatoire (18-65 ans)
            $minAge = 18;
            $maxAge = 65;
            $randomAge = random_int($minAge, $maxAge);
            $birthDate = new \DateTimeImmutable('-' . $randomAge . ' years');
            $provider->setBirthDate($birthDate);

            $country = $countries[$index % count($countries)];
            $provider->setCountry($country);

            // Assigner un Job non nul
            if (!empty($jobs)) {
                $job = $jobs[$index % count($jobs)];
                $provider->setJob($job);
            }

            // Slug
            $slug = $this->providerSlugService->generateSlugForNewProvider($provider->getFirstName(), $provider->getLastName());
            $provider->setSlug($slug);

            $manager->persist($provider);
            $manager->flush(); // avoir l'id pour la structure d'images

            // Image de profil depuis fixtures_images
            $url = $this->providerImageService->createProviderImageStructure((int) $provider->getId(), $data['profilePicture']);
            if ($url) {
                $provider->setProfilePicture($url);
            }

            $manager->persist($provider);

            // Assigner 1-3 langues aléatoires
            if (!empty($languages)) {
                $num = min(3, max(1, random_int(1, 3)));
                $picked = array_rand($languages, $num);
                foreach ((array) $picked as $idx) {
                    $language = $languages[$idx];
                    if (method_exists($provider, 'addLanguage')) {
                        $provider->addLanguage($language);
                    }
                }
            }

            // Assigner 2-4 hard skills
            if (!empty($hardSkills)) {
                $num = min(4, max(2, random_int(2, 4)));
                $picked = array_rand($hardSkills, $num);
                foreach ((array) $picked as $idx) {
                    $skill = $hardSkills[$idx];
                    if (method_exists($provider, 'addHardSkill')) {
                        $provider->addHardSkill($skill);
                    }
                }
            }

            // Assigner 2-4 soft skills
            if (!empty($softSkills)) {
                $num = min(4, max(2, random_int(2, 4)));
                $picked = array_rand($softSkills, $num);
                foreach ((array) $picked as $idx) {
                    $skill = $softSkills[$idx];
                    if (method_exists($provider, 'addSoftSkill')) {
                        $provider->addSoftSkill($skill);
                    }
                }
            }

            // Créer 1-2 expériences avec logos copiés depuis fixtures_images
            $expFixtureFiles = ['nvidia-logo.png', 'lsi-logic-logo.png', 'deeplearning-ai-logo.jpg', 'baidu-logo.png', 'coursera-logo.png'];
            $expCount = max(1, random_int(1, 2));
            for ($e = 0; $e < $expCount; $e++) {
                $experience = new Experience();
                $experience->setProvider($provider);
                $experience->setTitle('Experience ' . ($e + 1));
                $experience->setCompanyName('Company ' . ($e + 1));
                $experience->setFirstTask('Responsabilité principale');
                $experience->setSecondTask('Tâche secondaire');
                $experience->setThirdTask('Autre tâche');
                $experience->setStartedAt(new \DateTimeImmutable('-' . random_int(2, 10) . ' years'));
                $experience->setEndedAt(new \DateTimeImmutable('-' . random_int(0, 1) . ' years'));
                $manager->persist($experience);
                $manager->flush(); // besoin de l'ID pour copier l'image

                $fixtureLogo = $expFixtureFiles[array_rand($expFixtureFiles)];
                $logoUrl = $this->providerImageService->copyFixtureExperienceLogo((int) $provider->getId(), (int) $experience->getId(), $fixtureLogo);
                if ($logoUrl) {
                    $experience->setCompanyLogo($logoUrl);
                    $manager->persist($experience);
                }
            }

            // Créer 1 formation avec logo copié depuis fixtures_images
            $eduFixtureFiles = ['oregon-state-logo.png', 'stanford-logo.png', 'berkeley-logo.png', 'mit-logo.png'];
            $education = new Education();
            $education->setProvider($provider);
            $education->setTitle('Diplôme ' . ($index + 1));
            $education->setInstitutionName('Université Exemple');
            $education->setDescription('Programme académique pour ' . $provider->getFirstName());
            $education->setStartedAt(new \DateTimeImmutable('-' . random_int(6, 12) . ' years'));
            $education->setEndedAt(new \DateTimeImmutable('-' . random_int(1, 5) . ' years'));
            $manager->persist($education);
            $manager->flush(); // besoin de l'ID pour copier l'image

            $eduLogo = $eduFixtureFiles[array_rand($eduFixtureFiles)];
            $eduUrl = $this->providerImageService->copyFixtureEducationLogo((int) $provider->getId(), (int) $education->getId(), $eduLogo);
            if ($eduUrl) {
                $education->setInstitutionImage($eduUrl);
                $manager->persist($education);
            }

            $this->addReference('provider_' . ($index + 1), $provider);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            JobFixtures::class,
            CountryFixtures::class,
            LanguageFixtures::class,
            HardSkillFixtures::class,
            SoftSkillFixtures::class,
        ];
    }
}
