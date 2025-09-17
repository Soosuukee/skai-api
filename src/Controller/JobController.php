<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/jobs', name: 'api_jobs_')]
class JobController extends AbstractController
{
    public function __construct(
        private JobRepository $jobRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $jobs = $this->jobRepository->findAll();
        $data = array_map(function ($j) {
            return [
                'id' => $j->getId(),
                'title' => $j->getTitle(),
                'slug' => $j->getSlug(),
            ];
        }, $jobs);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($jobs)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $job = $this->jobRepository->find($id);

        if (!$job) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Job not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $job->getId(),
            'title' => $job->getTitle(),
            'slug' => $job->getSlug(),
        ];
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/slug/{slug}', name: 'show_by_slug', methods: ['GET'])]
    public function showBySlug(string $slug): JsonResponse
    {
        $job = $this->jobRepository->findBySlug($slug);

        if (!$job) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Job not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $job->getId(),
            'title' => $job->getTitle(),
            'slug' => $job->getSlug(),
        ];
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }
}
