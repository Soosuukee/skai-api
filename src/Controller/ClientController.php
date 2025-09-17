<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\FileUploadService;
use App\Service\SlugManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/v1/clients', name: 'api_clients_')]
class ClientController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private FileUploadService $fileUploadService,
        private SlugManager $slugManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $clients = $this->clientRepository->findAll();

        $data = array_map(function (Client $c) {
            return [
                'id' => $c->getId(),
                'firstName' => $c->getFirstName(),
                'lastName' => $c->getLastName(),
                'email' => $c->getEmail(),
                'profilePicture' => $c->getProfilePicture(),
                'joinedAt' => $c->getJoinedAt()?->format(DATE_ATOM),
                'slug' => $c->getSlug(),
                'countryId' => $c->getCountry()?->getId(),
                'city' => $c->getCity(),
                'state' => $c->getState(),
                'postalCode' => $c->getPostalCode(),
                'address' => $c->getAddress(),
                'role' => 'client',
            ];
        }, $clients);
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($clients)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $client->getId(),
            'firstName' => $client->getFirstName(),
            'lastName' => $client->getLastName(),
            'email' => $client->getEmail(),
            'profilePicture' => $client->getProfilePicture(),
            'joinedAt' => $client->getJoinedAt()?->format(DATE_ATOM),
            'slug' => $client->getSlug(),
            'countryId' => $client->getCountry()?->getId(),
            'city' => $client->getCity(),
            'state' => $client->getState(),
            'postalCode' => $client->getPostalCode(),
            'address' => $client->getAddress(),
            'role' => 'client',
        ];
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/{slug}', name: 'show_by_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\\-]*'])]
    public function showBySlug(string $slug): JsonResponse
    {
        $client = $this->clientRepository->findBySlug($slug);

        if (!$client) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $client->getId(),
            'firstName' => $client->getFirstName(),
            'lastName' => $client->getLastName(),
            'email' => $client->getEmail(),
            'profilePicture' => $client->getProfilePicture(),
            'joinedAt' => $client->getJoinedAt()?->format(DATE_ATOM),
            'slug' => $client->getSlug(),
            'countryId' => $client->getCountry()?->getId(),
            'city' => $client->getCity(),
            'state' => $client->getState(),
            'postalCode' => $client->getPostalCode(),
            'address' => $client->getAddress(),
            'role' => 'client',
        ];
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $client = new Client();
            $client->setFirstName($data['firstName'] ?? '');
            $client->setLastName($data['lastName'] ?? '');
            $client->setEmail($data['email'] ?? '');
            $client->setPassword($data['password'] ?? ''); // TODO: Hash password
            $client->setCity($data['city'] ?? '');
            $client->setState($data['state'] ?? '');
            $client->setPostalCode($data['postalCode'] ?? '');
            $client->setAddress($data['address'] ?? '');
            $client->setJoinedAt(new \DateTimeImmutable());

            // Set relations if provided
            if (isset($data['countryId'])) {
                // TODO: Set country relation
            }

            // Validate entity
            $errors = $this->validator->validate($client);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $client,
                'message' => 'Client created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['firstName'])) $client->setFirstName($data['firstName']);
            if (isset($data['lastName'])) $client->setLastName($data['lastName']);
            if (isset($data['email'])) $client->setEmail($data['email']);
            if (isset($data['password'])) $client->setPassword($data['password']); // TODO: Hash password
            if (isset($data['city'])) $client->setCity($data['city']);
            if (isset($data['state'])) $client->setState($data['state']);
            if (isset($data['postalCode'])) $client->setPostalCode($data['postalCode']);
            if (isset($data['address'])) $client->setAddress($data['address']);

            // Validate entity
            $errors = $this->validator->validate($client);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $client,
                'message' => 'Client updated successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($client);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Client deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/profile-image', name: 'upload_profile_image', methods: ['POST'])]
    public function uploadProfileImage(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $uploadedFile = $request->files->get('profile_image');

        if (!$uploadedFile) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No file uploaded'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $fileUrl = $this->fileUploadService->uploadClientProfilePicture(
                $uploadedFile->getPathname(),
                $client->getId()
            );

            $client->setProfilePicture($fileUrl);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'profile_picture_url' => $fileUrl
                ],
                'message' => 'Profile image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/password', name: 'change_password', methods: ['PATCH'], requirements: ['id' => '\\d+'])]
    public function changePassword(int $id, Request $request): JsonResponse
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Client not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $currentPassword = (string) ($request->headers->get('X-Current-Password') ?? '');
        $newPassword = (string) ($request->headers->get('X-New-Password') ?? '');

        if ($currentPassword === '' || $newPassword === '') {
            return new JsonResponse([
                'success' => false,
                'error' => 'X-Current-Password et X-New-Password requis dans les en-têtes'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->passwordHasher->isPasswordValid($client, $currentPassword)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Mot de passe actuel invalide'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $hashed = $this->passwordHasher->hashPassword($client, $newPassword);
        $client->setPassword($hashed);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Mot de passe mis à jour'
        ]);
    }
}
