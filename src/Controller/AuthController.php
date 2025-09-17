<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProviderRepository;
use App\Repository\ClientRepository;
use App\Repository\JobRepository;
use App\Repository\CountryRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ClientRepository $clientRepository,
        private JobRepository $jobRepository,
        private CountryRepository $countryRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthService $authService,
        private ValidatorInterface $validator
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';

            // Find user in both providers and clients
            $user = $this->providerRepository->findByEmail($email);
            if (!$user) {
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
            $userType = $user instanceof \App\Entity\Provider ? 'provider' : 'client';

            $response = new JsonResponse([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                        'type' => $userType
                    ]
                ]
            ]);

            // Set HttpOnly cookie (authToken)
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

            $userType = $data['userType'] ?? 'provider'; // 'provider' or 'client'
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $firstName = $data['firstName'] ?? '';
            $lastName = $data['lastName'] ?? '';

            // Validation des champs requis
            if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Tous les champs sont requis (email, password, firstName, lastName)'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier si l'utilisateur existe déjà
            $existingProvider = $this->providerRepository->findByEmail($email);
            $existingClient = $this->clientRepository->findByEmail($email);

            if ($existingProvider || $existingClient) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Un utilisateur avec cet email existe déjà'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Créer l'utilisateur selon le type
            if ($userType === 'provider') {
                $user = new \App\Entity\Provider();
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setEmail($email);

                // Hasher le mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                // Générer un slug unique
                $slug = strtolower($firstName . '-' . $lastName . '-' . uniqid());
                $user->setSlug($slug);

                // Définir la date d'inscription
                $user->setJoinedAt(new \DateTimeImmutable());

                // Renseigner les champs requis NOT NULL avec des valeurs par défaut
                // Country & Job requis
                $defaultCountry = $this->countryRepository->findOneBy([]);
                if ($defaultCountry) {
                    $user->setCountry($defaultCountry);
                }
                $defaultJob = $this->jobRepository->findOneBy([]);
                if ($defaultJob) {
                    $user->setJob($defaultJob);
                }
                // Autres champs NOT NULL
                $user->setProfilePicture('');
                $user->setCity('');
                $user->setState('');
                $user->setPostalCode('');
                $user->setAddress('');

                // Valider l'entité Provider
                $errors = $this->validator->validate($user);
                if (count($errors) > 0) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => (string) $errors
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Sauvegarder le provider
                $this->providerRepository->save($user);
            } else {
                $user = new \App\Entity\Client();
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setEmail($email);

                // Hasher le mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                // Générer un slug unique
                $slug = strtolower($firstName . '-' . $lastName . '-' . uniqid());
                $user->setSlug($slug);

                // Définir la date d'inscription
                $user->setJoinedAt(new \DateTimeImmutable());

                // Renseigner les champs requis NOT NULL avec des valeurs par défaut
                $defaultCountry = $this->countryRepository->findOneBy([]);
                if ($defaultCountry) {
                    $user->setCountry($defaultCountry);
                }
                $user->setProfilePicture('');
                $user->setCity('');
                $user->setState('');
                $user->setPostalCode('');
                $user->setAddress('');

                // Valider l'entité Client
                $errors = $this->validator->validate($user);
                if (count($errors) > 0) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => (string) $errors
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Sauvegarder le client
                $this->clientRepository->save($user);
            }

            // Générer le token JWT
            $token = $this->authService->generateToken($user);

            return new JsonResponse([
                'success' => true,
                'message' => 'Inscription réussie',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                        'type' => $userType
                    ]
                ]
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
        // Déconnecter l'utilisateur actuel
        $this->authService->logout();

        $response = new JsonResponse([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);

        // Supprimer le cookie d'authentification
        $response->headers->clearCookie('authToken', '/', null, true, true, 'None');

        return $response;
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

        // Retourner les infos complètes selon le type d'utilisateur
        if ($user instanceof \App\Entity\Provider) {
            $userData = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'profilePicture' => $user->getProfilePicture(),
                'joinedAt' => $user->getJoinedAt()?->format('c'),
                'slug' => $user->getSlug(),
                'job' => $user->getJob()?->getTitle(),
                'country' => $user->getCountry()?->getName(),
                'city' => $user->getCity(),
                'state' => $user->getState(),
                'postalCode' => $user->getPostalCode(),
                'address' => $user->getAddress(),
                'hardSkills' => $user->getHardSkills()->map(fn($s) => $s->getTitle())->toArray(),
                'softSkills' => $user->getSoftSkills()->map(fn($s) => $s->getTitle())->toArray(),
                'languages' => $user->getLanguages()->map(fn($l) => $l->getName())->toArray(),
                'role' => 'provider'
            ];
        } elseif ($user instanceof \App\Entity\Client) {
            $userData = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'profilePicture' => $user->getProfilePicture(),
                'joinedAt' => $user->getJoinedAt()?->format('c'),
                'slug' => $user->getSlug(),
                'country' => $user->getCountry()?->getName(),
                'city' => $user->getCity(),
                'state' => $user->getState(),
                'postalCode' => $user->getPostalCode(),
                'address' => $user->getAddress(),
                'role' => 'client'
            ];
        } else {
            return new JsonResponse([
                'success' => false,
                'error' => 'Unknown user type'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'success' => true,
            'data' => $userData
        ]);
    }
}
