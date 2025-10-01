<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProviderRepository;
use App\Repository\ClientRepository;
use App\Repository\CountryRepository;
use App\Repository\JobRepository;
use App\Repository\LanguageRepository;
use App\Repository\HardSkillRepository;
use App\Repository\SoftSkillRepository;
use App\Service\AuthService;
use App\Service\FileUploadService;
use App\Service\SlugService;
use App\Config\UploadConfig;
use App\Entity\Provider as ProviderEntity;
use App\Entity\Client as ClientEntity;
use App\Entity\Provider;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ClientRepository $clientRepository,
        private CountryRepository $countryRepository,
        private JobRepository $jobRepository,
        private LanguageRepository $languageRepository,
        private HardSkillRepository $hardSkillRepository,
        private SoftSkillRepository $softSkillRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthService $authService,
        private SlugService $slugService,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private FileUploadService $fileUploadService
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $userType = $data['userType'] ?? 'provider'; // 'provider' ou 'client'

            // Trouver l'utilisateur selon le type
            if ($userType === 'provider') {
                $user = $this->providerRepository->findByEmail($email);
            } else {
                $user = $this->clientRepository->findByEmail($email);
            }

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Identifiants invalides'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Valider le mot de passe
            if (!$this->passwordHasher->isPasswordValid($user, $password)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Identifiants invalides'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Générer le token JWT
            $token = $this->authService->generateToken($user);
            $userRole = $user instanceof Provider ? 'provider' : 'client';

            $userData = json_decode($this->serializer->serialize($user, 'json', [
                'groups' => $userRole === 'provider' ? ['provider:read'] : ['client:read']
            ]), true);

            $response = new JsonResponse([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'token' => $token,
                    'user' => $userData,
                    'role' => $userRole
                ]
            ]);

            // Définir le cookie HttpOnly (authToken)
            $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
                name: 'authToken',
                value: $token,
                expire: time() + 3600,
                path: '/',
                domain: null,
                secure: true,
                httpOnly: true,
                raw: false,
                sameSite: 'None'
            ));

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $userType = $data['userType'] ?? 'provider'; // 'provider' ou 'client'
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';

            // Vérifier si l'utilisateur existe déjà
            $existingProvider = $this->providerRepository->findByEmail($email);
            $existingClient = $this->clientRepository->findByEmail($email);

            if ($existingProvider || $existingClient) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'User already exists with this email'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Obtenir le pays et le métier par défaut
            $defaultCountry = $this->countryRepository->find(1); // Premier pays
            $defaultJob = $this->jobRepository->find(1); // Premier métier

            // Créer l'utilisateur selon le type (champs de base uniquement)
            if ($userType === 'provider') {
                $user = new Provider();
                $user->setEmail($email);
                $firstName = (string)($data['firstName'] ?? '');
                $lastName = (string)($data['lastName'] ?? '');
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                // Utiliser le SlugService pour un slug propre et unique-ish
                $user->setSlug($this->slugService->slugifyUser($firstName, $lastName));
                $user->setJoinedAt(new \DateTimeImmutable());
                $user->setCountry($defaultCountry);
                $user->setJob($defaultJob);
                // Set required non-nullable fields with safe defaults
                $user->setCity((string)($data['city'] ?? ''));
                $user->setState((string)($data['state'] ?? ''));
                $user->setPostalCode((string)($data['postalCode'] ?? ''));
                $user->setAddress((string)($data['address'] ?? ''));
            } else {
                $user = new Client();
                $user->setEmail($email);
                $firstName = (string)($data['firstName'] ?? '');
                $lastName = (string)($data['lastName'] ?? '');
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setSlug($this->slugService->slugifyUser($firstName, $lastName));
                $user->setJoinedAt(new \DateTimeImmutable());
                $user->setCountry($defaultCountry);
                // Set required non-nullable fields with safe defaults
                $user->setCity((string)($data['city'] ?? ''));
                $user->setState((string)($data['state'] ?? ''));
                $user->setPostalCode((string)($data['postalCode'] ?? ''));
                $user->setAddress((string)($data['address'] ?? ''));
            }

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            // Sauvegarder l'utilisateur en base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Bootstrap des dossiers d'upload dès l'inscription (miroir des fixtures)
            if ($user instanceof ProviderEntity) {
                $providerId = (int) $user->getId();
                $base = "images/providers/{$providerId}/";
                foreach (
                    [
                        $base . 'profile/',
                        $base . 'services/',
                        $base . 'articles/',
                        $base . 'experiences/',
                        $base . 'education/',
                        $base . 'completed-work/'
                    ] as $relDir
                ) {
                    $abs = \App\Config\UploadConfig::getUploadPath($relDir);
                    if (!is_dir($abs)) {
                        @mkdir($abs, 0755, true);
                    }
                }
            } elseif ($user instanceof ClientEntity) {
                $clientId = (int) $user->getId();
                $relDir = \App\Config\UploadConfig::getClientProfilePicturePath($clientId);
                $abs = \App\Config\UploadConfig::getUploadPath($relDir);
                if (!is_dir($abs)) {
                    @mkdir($abs, 0755, true);
                }
            }

            // Générer le token JWT
            $token = $this->authService->generateToken($user);

            $userData = json_decode($this->serializer->serialize($user, 'json', [
                'groups' => $userType === 'provider' ? ['provider:read'] : ['client:read']
            ]), true);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                    'role' => $userType
                ],
                'message' => 'Registration successful'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // TODO: Invalider le token JWT ou la session
        return new JsonResponse([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    #[Route('/complete-provider-profile', name: 'complete_provider_profile', methods: ['PATCH'])]
    public function completeProviderProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Not authenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Vérifier que c'est bien un provider
            if (!$user instanceof Provider) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'This endpoint is only for providers'
                ], Response::HTTP_FORBIDDEN);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs fournis
            if (isset($data['city'])) {
                $user->setCity($data['city']);
            }
            if (isset($data['state'])) {
                $user->setState($data['state']);
            }
            if (isset($data['postalCode'])) {
                $user->setPostalCode($data['postalCode']);
            }
            if (isset($data['address'])) {
                $user->setAddress($data['address']);
            }
            if (isset($data['country']) || isset($data['countryId']) || isset($data['countrySlug'])) {
                $country = null;
                if (isset($data['country'])) {
                    $country = $this->countryRepository->find((int)$data['country']);
                } elseif (isset($data['countryId'])) {
                    $country = $this->countryRepository->find((int)$data['countryId']);
                } elseif (isset($data['countrySlug'])) {
                    $country = $this->countryRepository->findOneBy(['slug' => (string)$data['countrySlug']]);
                }
                if ($country) {
                    $user->setCountry($country);
                }
            }

            if (isset($data['job']) || isset($data['jobId']) || isset($data['jobSlug'])) {
                $job = null;
                if (isset($data['job'])) {
                    $job = $this->jobRepository->find((int)$data['job']);
                } elseif (isset($data['jobId'])) {
                    $job = $this->jobRepository->find((int)$data['jobId']);
                } elseif (isset($data['jobSlug'])) {
                    $job = $this->jobRepository->findOneBy(['slug' => (string)$data['jobSlug']]);
                }
                if ($job) {
                    $user->setJob($job);
                }
            }

            // Languages (replace set if provided)
            if (isset($data['languageIds']) || isset($data['languageSlugs'])) {
                $languages = [];
                if (isset($data['languageIds']) && is_array($data['languageIds'])) {
                    foreach ($data['languageIds'] as $lid) {
                        $lang = $this->languageRepository->find((int)$lid);
                        if ($lang) {
                            $languages[] = $lang;
                        }
                    }
                }
                if (isset($data['languageSlugs']) && is_array($data['languageSlugs'])) {
                    foreach ($data['languageSlugs'] as $ls) {
                        $lang = $this->languageRepository->findOneBy(['slug' => (string)$ls]);
                        if ($lang) {
                            $languages[] = $lang;
                        }
                    }
                }
                // clear and set
                foreach ($user->getLanguages() as $existing) {
                    $user->getLanguages()->removeElement($existing);
                }
                foreach ($languages as $l) {
                    $user->addLanguage($l);
                }
            }

            // Hard skills
            if (isset($data['hardSkillIds']) || isset($data['hardSkillSlugs'])) {
                $hardSkills = [];
                if (isset($data['hardSkillIds']) && is_array($data['hardSkillIds'])) {
                    foreach ($data['hardSkillIds'] as $hid) {
                        $hs = $this->hardSkillRepository->find((int)$hid);
                        if ($hs) {
                            $hardSkills[] = $hs;
                        }
                    }
                }
                if (isset($data['hardSkillSlugs']) && is_array($data['hardSkillSlugs'])) {
                    foreach ($data['hardSkillSlugs'] as $hss) {
                        $hs = $this->hardSkillRepository->findOneBy(['slug' => (string)$hss]);
                        if ($hs) {
                            $hardSkills[] = $hs;
                        }
                    }
                }
                foreach ($user->getHardSkills() as $existing) {
                    $user->getHardSkills()->removeElement($existing);
                }
                foreach ($hardSkills as $hs) {
                    $user->addHardSkill($hs);
                }
            }

            // Soft skills
            if (isset($data['softSkillIds']) || isset($data['softSkillSlugs'])) {
                $softSkills = [];
                if (isset($data['softSkillIds']) && is_array($data['softSkillIds'])) {
                    foreach ($data['softSkillIds'] as $sid) {
                        $ss = $this->softSkillRepository->find((int)$sid);
                        if ($ss) {
                            $softSkills[] = $ss;
                        }
                    }
                }
                if (isset($data['softSkillSlugs']) && is_array($data['softSkillSlugs'])) {
                    foreach ($data['softSkillSlugs'] as $sss) {
                        $ss = $this->softSkillRepository->findOneBy(['slug' => (string)$sss]);
                        if ($ss) {
                            $softSkills[] = $ss;
                        }
                    }
                }
                foreach ($user->getSoftSkills() as $existing) {
                    $user->getSoftSkills()->removeElement($existing);
                }
                foreach ($softSkills as $ss) {
                    $user->addSoftSkill($ss);
                }
            }
            if (isset($data['birthDate'])) {
                $user->setBirthDate(new \DateTime($data['birthDate']));
            }

            $this->entityManager->flush();

            $userData = json_decode($this->serializer->serialize($user, 'json', ['groups' => ['provider:read']]), true);

            return new JsonResponse([
                'success' => true,
                'message' => 'Provider profile completed successfully',
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/complete-client-profile', name: 'complete_client_profile', methods: ['PATCH'])]
    public function completeClientProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Not authenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Vérifier que c'est bien un client
            if (!$user instanceof Client) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'This endpoint is only for clients'
                ], Response::HTTP_FORBIDDEN);
            }

            $data = json_decode($request->getContent(), true);

            // Mettre à jour les champs fournis (sans job)
            if (isset($data['city'])) {
                $user->setCity($data['city']);
            }
            if (isset($data['state'])) {
                $user->setState($data['state']);
            }
            if (isset($data['postalCode'])) {
                $user->setPostalCode($data['postalCode']);
            }
            if (isset($data['address'])) {
                $user->setAddress($data['address']);
            }
            if (isset($data['country'])) {
                $country = $this->countryRepository->find($data['country']);
                if ($country) {
                    $user->setCountry($country);
                }
            }
            if (isset($data['birthDate'])) {
                $user->setBirthDate(new \DateTime($data['birthDate']));
            }

            $this->entityManager->flush();

            $userData = json_decode($this->serializer->serialize($user, 'json', ['groups' => ['client:read']]), true);

            return new JsonResponse([
                'success' => true,
                'message' => 'Client profile completed successfully',
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Utiliser la sérialisation automatique selon le type d'utilisateur
        $userRole = $user instanceof Provider ? 'provider' : 'client';
        $groups = $userRole === 'provider' ? ['provider:read'] : ['client:read'];

        $userData = json_decode($this->serializer->serialize($user, 'json', ['groups' => $groups]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $userData
        ]);
    }


    #[Route('/change-email', name: 'change_email', methods: ['PATCH'])]
    public function changeEmail(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $newEmail = (string)($data['newEmail'] ?? '');
        $currentPassword = (string)($data['currentPassword'] ?? '');

        if ($newEmail === '' || $currentPassword === '') {
            return new JsonResponse(['success' => false, 'error' => 'newEmail and currentPassword are required'], Response::HTTP_BAD_REQUEST);
        }

        if (!($user instanceof \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface)) {
            return new JsonResponse(['success' => false, 'error' => 'Unsupported user type'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        // Check uniqueness across providers and clients
        $existingProvider = $this->providerRepository->findByEmail($newEmail);
        $existingClient = $this->clientRepository->findByEmail($newEmail);
        $conflict = false;
        $userId = null;
        if ($user instanceof ProviderEntity || $user instanceof ClientEntity) {
            $userId = $user->getId();
        }
        if ($existingProvider && ($userId === null || $existingProvider->getId() !== $userId)) {
            $conflict = true;
        }
        if (!$conflict && $existingClient && ($userId === null || $existingClient->getId() !== $userId)) {
            $conflict = true;
        }
        if ($conflict) {
            return new JsonResponse(['success' => false, 'error' => 'Email already in use'], Response::HTTP_BAD_REQUEST);
        }

        if ($user instanceof ProviderEntity || $user instanceof ClientEntity) {
            $user->setEmail($newEmail);
            $this->entityManager->flush();
        } else {
            return new JsonResponse(['success' => false, 'error' => 'Unsupported user type'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['success' => true, 'message' => 'Email updated']);
    }

    #[Route('/change-password', name: 'change_password', methods: ['PATCH'])]
    public function changePassword(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $currentPassword = (string)($data['currentPassword'] ?? '');
        $newPassword = (string)($data['newPassword'] ?? '');

        if ($currentPassword === '' || $newPassword === '') {
            return new JsonResponse(['success' => false, 'error' => 'currentPassword and newPassword are required'], Response::HTTP_BAD_REQUEST);
        }

        if (!($user instanceof \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface)) {
            return new JsonResponse(['success' => false, 'error' => 'Unsupported user type'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        if (strlen($newPassword) < 8) {
            return new JsonResponse(['success' => false, 'error' => 'newPassword must be at least 8 characters'], Response::HTTP_BAD_REQUEST);
        }

        $hashed = $this->passwordHasher->hashPassword($user, $newPassword);
        if ($user instanceof ProviderEntity || $user instanceof ClientEntity) {
            $user->setPassword($hashed);
            $this->entityManager->flush();
        } else {
            return new JsonResponse(['success' => false, 'error' => 'Unsupported user type'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['success' => true, 'message' => 'Password updated']);
    }

    #[Route('/account', name: 'delete_account', methods: ['DELETE'])]
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true) ?? [];
            $currentPassword = (string)($data['currentPassword'] ?? '');
            if ($currentPassword === '') {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'currentPassword is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (!($user instanceof PasswordAuthenticatedUserInterface)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Unsupported user type'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid password'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();

            $response = new JsonResponse([
                'success' => true,
                'message' => 'Account deleted'
            ]);

            // Clear auth cookie if used
            $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
                name: 'authToken',
                value: '',
                expire: time() - 3600,
                path: '/',
                domain: null,
                secure: true,
                httpOnly: true,
                raw: false,
                sameSite: 'None'
            ));

            return $response;
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/avatar', name: 'delete_avatar', methods: ['DELETE'])]
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $data = json_decode($request->getContent(), true) ?? [];
            $currentPassword = (string)($data['currentPassword'] ?? '');
            if ($currentPassword === '') {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'currentPassword is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (!($user instanceof \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface)) {
                return new JsonResponse(['success' => false, 'error' => 'Unsupported user type'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                return new JsonResponse(['success' => false, 'error' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
            }

            if ($user instanceof \App\Entity\Provider || $user instanceof \App\Entity\Client) {
                $current = (string)($user->getProfilePicture() ?? '');
                if ($current !== '') {
                    // Supprimer le fichier sur disque
                    try {
                        $this->fileUploadService->deleteFile($current);
                    } catch (\Throwable $t) {
                        // on ignore l'erreur de suppression fichier pour ne pas bloquer la requête
                    }
                    // Nettoyer le champ en base
                    $user->setProfilePicture('');
                    $this->entityManager->flush();
                }
            } else {
                return new JsonResponse(['success' => false, 'error' => 'Unsupported user type'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse(['success' => true, 'message' => 'Avatar deleted']);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/avatar', name: 'upload_avatar', methods: ['POST'])]
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $uploadedFile = $request->files->get('profile_image');
        if (!$uploadedFile) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No file uploaded (expected field: profile_image)'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($user instanceof ProviderEntity) {
                $providerId = (int) $user->getId();
                // Créer l'arborescence complète comme pour les fixtures
                $base = "images/providers/{$providerId}/";
                foreach (
                    [
                        $base . 'profile/',
                        $base . 'services/',
                        $base . 'articles/',
                        $base . 'experiences/',
                        $base . 'education/',
                        $base . 'completed-work/'
                    ] as $relDir
                ) {
                    $abs = UploadConfig::getUploadPath($relDir);
                    if (!is_dir($abs)) {
                        @mkdir($abs, 0755, true);
                    }
                }
                $directory = UploadConfig::getProviderProfilePicturePath($providerId);
            } elseif ($user instanceof ClientEntity) {
                $clientId = (int) $user->getId();
                $relDir = UploadConfig::getClientProfilePicturePath($clientId);
                $abs = UploadConfig::getUploadPath($relDir);
                if (!is_dir($abs)) {
                    @mkdir($abs, 0755, true);
                }
                $directory = $relDir;
            } else {
                return new JsonResponse(['success' => false, 'error' => 'Unsupported user type'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $uploadPath = UploadConfig::getUploadPath($directory);
            if (!is_dir($uploadPath)) {
                @mkdir($uploadPath, 0755, true);
            }

            $unique = UploadConfig::generateUniqueFilename($uploadedFile->getClientOriginalName(), $directory);
            $uploadedFile->move($uploadPath, $unique);

            $url = UploadConfig::getRelativeUrl($directory, $unique);
            $user->setProfilePicture($url);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => ['profile_picture_url' => $url]
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
