<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/services', name: 'api_services_')]
class ServiceController extends AbstractController
{
    public function __construct(
        private ServiceRepository $serviceRepository,
        private ProviderRepository $providerRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
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
}
