<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SoftSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/soft-skills', name: 'api_soft_skills_')]
class SoftSkillController extends AbstractController
{
    public function __construct(
        private SoftSkillRepository $softSkillRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $softSkills = $this->softSkillRepository->findAll();

        $json = $this->serializer->serialize($softSkills, 'json', ['groups' => ['softskill:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true),
            'total' => count($softSkills)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $softSkill = $this->softSkillRepository->find($id);

        if (!$softSkill) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Soft skill not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $json = $this->serializer->serialize($softSkill, 'json', ['groups' => ['softskill:read']]);
        return new JsonResponse([
            'success' => true,
            'data' => json_decode($json, true)
        ]);
    }
}
