<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/v1/services', name: 'api_services_')]
class ServiceController extends AbstractController
{
    public function __construct(
        private ServiceRepository $serviceRepository,
        private ProviderRepository $providerRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $services = $this->serviceRepository->findAll();

        $data = json_decode($this->serializer->serialize($services, 'json', [
            'groups' => ['service:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($services)
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof \App\Entity\Provider)) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $service = new Service();
        $service->setProvider($user);
        $service->setTitle((string)($data['title'] ?? ''));
        $service->setSummary((string)($data['summary'] ?? ''));
        $service->setMinPrice((string)($data['minPrice'] ?? '0'));
        $service->setMaxPrice((string)($data['maxPrice'] ?? '0'));
        $service->setIsActive((bool)($data['isActive'] ?? true));
        $service->setIsFeatured((bool)($data['isFeatured'] ?? false));
        $service->setCreatedAt(new \DateTimeImmutable());
        if (!empty($data['cover'])) $service->setCover((string)$data['cover']);

        $errors = $this->validator->validate($service);
        if (count($errors) > 0) {
            return new JsonResponse(['success' => false, 'errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->persist($service);
        $this->entityManager->flush();

        $payload = json_decode($this->serializer->serialize($service, 'json', ['groups' => ['service:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $payload], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof \App\Entity\Provider)) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        $service = $this->serviceRepository->find($id);
        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }
        if ($service->getProvider()?->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if (isset($data['title'])) $service->setTitle((string)$data['title']);
        if (isset($data['summary'])) $service->setSummary((string)$data['summary']);
        if (isset($data['minPrice'])) $service->setMinPrice((string)$data['minPrice']);
        if (isset($data['maxPrice'])) $service->setMaxPrice((string)$data['maxPrice']);
        if (array_key_exists('isActive', $data)) $service->setIsActive((bool)$data['isActive']);
        if (array_key_exists('isFeatured', $data)) $service->setIsFeatured((bool)$data['isFeatured']);
        if (isset($data['cover'])) $service->setCover((string)$data['cover']);

        $errors = $this->validator->validate($service);
        if (count($errors) > 0) {
            return new JsonResponse(['success' => false, 'errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->flush();

        $payload = json_decode($this->serializer->serialize($service, 'json', ['groups' => ['service:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $payload]);
    }

    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    public function patch(int $id, Request $request): JsonResponse
    {
        return $this->update($id, $request);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user || !($user instanceof \App\Entity\Provider)) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        $service = $this->serviceRepository->find($id);
        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }
        if ($service->getProvider()?->getId() !== $user->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        $data = json_decode($request->getContent(), true) ?? [];
        $currentPassword = (string)($data['currentPassword'] ?? '');
        if ($currentPassword === '') {
            return new JsonResponse(['success' => false, 'error' => 'currentPassword is required'], Response::HTTP_BAD_REQUEST);
        }
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        $this->entityManager->remove($service);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true, 'message' => 'Service deleted']);
    }
}
