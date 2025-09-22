<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProviderRepository;
use App\Repository\ClientRepository;
use App\Repository\CountryRepository;
use App\Repository\JobRepository;
use App\Service\AuthService;
use App\Entity\Provider;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ClientRepository $clientRepository,
        private CountryRepository $countryRepository,
        private JobRepository $jobRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthService $authService,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
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
                $user->setFirstName($data['firstName'] ?? '');
                $user->setLastName($data['lastName'] ?? '');
                $user->setSlug(strtolower($data['firstName'] ?? '') . '-' . strtolower($data['lastName'] ?? ''));
                $user->setJoinedAt(new \DateTimeImmutable());
                $user->setCountry($defaultCountry);
                $user->setJob($defaultJob);
            } else {
                $user = new Client();
                $user->setEmail($email);
                $user->setFirstName($data['firstName'] ?? '');
                $user->setLastName($data['lastName'] ?? '');
                $user->setSlug(strtolower($data['firstName'] ?? '') . '-' . strtolower($data['lastName'] ?? ''));
                $user->setJoinedAt(new \DateTimeImmutable());
                $user->setCountry($defaultCountry);
            }

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            // Sauvegarder l'utilisateur en base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();

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
            if (isset($data['country'])) {
                $country = $this->countryRepository->find($data['country']);
                if ($country) {
                    $user->setCountry($country);
                }
            }
            if (isset($data['job'])) {
                $job = $this->jobRepository->find($data['job']);
                if ($job) {
                    $user->setJob($job);
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
}
