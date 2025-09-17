<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HardSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/hard-skills', name: 'api_hard_skills_')]
class HardSkillController extends AbstractController
{
    public function __construct(
        private HardSkillRepository $hardSkillRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $hardSkills = $this->hardSkillRepository->findAll();

        return new JsonResponse([
            'success' => true,
            'data' => $hardSkills,
            'total' => count($hardSkills)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $hardSkill = $this->hardSkillRepository->find($id);

        if (!$hardSkill) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Hard skill not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'data' => $hardSkill
        ]);
    }
}
