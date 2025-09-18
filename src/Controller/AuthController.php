<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProviderRepository;
use App\Repository\ClientRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/v1/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private ClientRepository $clientRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthService $authService
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $userType = $data['userType'] ?? 'provider'; // 'provider' or 'client'

            // Find user based on type
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

            // Check if user already exists
            $existingProvider = $this->providerRepository->findByEmail($email);
            $existingClient = $this->clientRepository->findByEmail($email);

            if ($existingProvider || $existingClient) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'User already exists with this email'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create user based on type
            if ($userType === 'provider') {
                // TODO: Create provider entity and save
                $user = null; // Placeholder
            } else {
                // TODO: Create client entity and save
                $user = null; // Placeholder
            }

            // TODO: Generate JWT token or session
            $token = 'jwt_token_here'; // This should be generated properly

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'userType' => $userType
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
        // TODO: Invalidate JWT token or session
        return new JsonResponse([
            'success' => true,
            'message' => 'Logout successful'
        ]);
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
                'jobId' => $user->getJobId(),
                'countryId' => $user->getCountryId(),
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
                'countryId' => $user->getCountryIdForRead(),
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
