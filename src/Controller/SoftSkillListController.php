<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SoftSkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/soft-skills', name: 'api_soft_skills_list_')]
class SoftSkillListController extends AbstractController
{
    public function __construct(
        private SoftSkillRepository $softSkillRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $softSkills = $this->softSkillRepository->findAll();
        $data = json_decode($this->serializer->serialize($softSkills, 'json', ['groups' => ['softskill:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }
}
