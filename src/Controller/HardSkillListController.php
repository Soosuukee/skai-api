<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\HardSkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/hard-skills', name: 'api_hard_skills_list_')]
class HardSkillListController extends AbstractController
{
    public function __construct(
        private HardSkillRepository $hardSkillRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $hardSkills = $this->hardSkillRepository->findAll();
        $data = json_decode($this->serializer->serialize($hardSkills, 'json', ['groups' => ['hardskill:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }
}
