<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1', name: 'api_me_')]
class MeController extends AbstractController
{
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
                'job' => $user->getJob()?->getTitle(),
                'country' => $user->getCountry()?->getName(),
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
                'country' => $user->getCountry()?->getName(),
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
