<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/countries', name: 'api_countries_')]
class CountryController extends AbstractController
{
    public function __construct(
        private CountryRepository $countryRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $countries = $this->countryRepository->findAll();

        $json = $this->serializer->serialize($countries, 'json', ['groups' => ['country:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true),
            'total' => count($countries)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $country = $this->countryRepository->find($id);

        if (!$country) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Country not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($country, 'json', ['groups' => ['country:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true)
        ]);
    }
}
