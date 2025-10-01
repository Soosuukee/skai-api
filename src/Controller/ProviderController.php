<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Provider;
use App\Repository\ProviderRepository;
use App\Config\UploadConfig;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/api/v1/providers', name: 'api_providers_')]
class ProviderController extends AbstractController
{
    public function __construct(
        private ProviderRepository $providerRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private FileUploadService $fileUploadService,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $providers = $this->providerRepository->findAll();

        $data = json_decode($this->serializer->serialize($providers, 'json', [
            'groups' => ['provider:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($providers)
        ]);
    }

    #[Route('/country/{countrySlug}', name: 'list_by_country', methods: ['GET'])]
    public function listByCountry(string $countrySlug): JsonResponse
    {
        $providers = $this->providerRepository->findByCountrySlug($countrySlug);
        $data = json_decode($this->serializer->serialize($providers, 'json', ['groups' => ['provider:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    #[Route('/language/{languageSlug}', name: 'list_by_language', methods: ['GET'])]
    public function listByLanguage(string $languageSlug): JsonResponse
    {
        $providers = $this->providerRepository->findByLanguageSlug($languageSlug);
        $data = json_decode($this->serializer->serialize($providers, 'json', ['groups' => ['provider:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    #[Route('/job/{jobSlug}', name: 'list_by_job', methods: ['GET'])]
    public function listByJob(string $jobSlug): JsonResponse
    {
        $providers = $this->providerRepository->findByJobSlug($jobSlug);
        $data = json_decode($this->serializer->serialize($providers, 'json', ['groups' => ['provider:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    #[Route('/hard-skill/{hardSkillSlug}', name: 'list_by_hard_skill', methods: ['GET'])]
    public function listByHardSkill(string $hardSkillSlug): JsonResponse
    {
        $providers = $this->providerRepository->findByHardSkillSlug($hardSkillSlug);
        $data = json_decode($this->serializer->serialize($providers, 'json', ['groups' => ['provider:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    #[Route('/soft-skill/{softSkillSlug}', name: 'list_by_soft_skill', methods: ['GET'])]
    public function listBySoftSkill(string $softSkillSlug): JsonResponse
    {
        $providers = $this->providerRepository->findBySoftSkillSlug($softSkillSlug);
        $data = json_decode($this->serializer->serialize($providers, 'json', ['groups' => ['provider:read']]), true);
        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    #[Route('/filter', name: 'filter', methods: ['GET'])]
    public function filter(Request $request): JsonResponse
    {
        $jobId = $request->query->get('job_id');
        $countryId = $request->query->get('country_id');
        $countryName = $request->query->get('country_name');
        $hardSkillId = $request->query->get('hard_skill_id');
        $softSkillId = $request->query->get('soft_skill_id');
        $languageId = $request->query->get('language_id');
        $city = $request->query->get('city');
        $state = $request->query->get('state');

        // Construire la requête avec les filtres
        $qb = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Provider::class, 'p')
            ->leftJoin('p.job', 'j')
            ->leftJoin('p.country', 'c')
            ->leftJoin('p.hardSkills', 'hs')
            ->leftJoin('p.softSkills', 'ss')
            ->leftJoin('p.languages', 'l');

        $parameters = [];

        if ($jobId) {
            $qb->andWhere('j.id = :jobId');
            $parameters['jobId'] = $jobId;
        }

        if ($countryId) {
            $qb->andWhere('c.id = :countryId');
            $parameters['countryId'] = $countryId;
        }

        if ($countryName) {
            $qb->andWhere('LOWER(c.name) LIKE LOWER(:countryName)');
            $parameters['countryName'] = '%' . $countryName . '%';
        }

        if ($hardSkillId) {
            $qb->andWhere('hs.id = :hardSkillId');
            $parameters['hardSkillId'] = $hardSkillId;
        }

        if ($softSkillId) {
            $qb->andWhere('ss.id = :softSkillId');
            $parameters['softSkillId'] = $softSkillId;
        }

        if ($languageId) {
            $qb->andWhere('l.id = :languageId');
            $parameters['languageId'] = $languageId;
        }

        if ($city) {
            $qb->andWhere('p.city LIKE :city');
            $parameters['city'] = '%' . $city . '%';
        }

        if ($state) {
            $qb->andWhere('p.state LIKE :state');
            $parameters['state'] = '%' . $state . '%';
        }

        // Ajouter les paramètres
        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }

        // Éviter les doublons
        $qb->groupBy('p.id');

        $providers = $qb->getQuery()->getResult();

        $data = json_decode($this->serializer->serialize($providers, 'json', [
            'groups' => ['provider:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($data),
            'filters' => [
                'job_id' => $jobId,
                'country_id' => $countryId,
                'country_name' => $countryName,
                'hard_skill_id' => $hardSkillId,
                'soft_skill_id' => $softSkillId,
                'language_id' => $languageId,
                'city' => $city,
                'state' => $state,
            ]
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $provider->getId(),
            'firstName' => $provider->getFirstName(),
            'lastName' => $provider->getLastName(),
            'email' => $provider->getEmail(),
            'profilePicture' => $provider->getProfilePicture(),
            'joinedAt' => $provider->getJoinedAt()?->format(DATE_ATOM),
            'slug' => $provider->getSlug(),
            'jobId' => $provider->getJob()?->getId(),
            'countryId' => $provider->getCountry()?->getId(),
            'city' => $provider->getCity(),
            'state' => $provider->getState(),
            'postalCode' => $provider->getPostalCode(),
            'address' => $provider->getAddress(),
            'hardSkills' => array_map(fn($s) => $s->getTitle(), $provider->getHardSkills()->toArray()),
            'softSkills' => array_map(fn($s) => $s->getTitle(), $provider->getSoftSkills()->toArray()),
            'languages' => array_map(fn($l) => $l->getName(), $provider->getLanguages()->toArray()),
            'role' => $provider->getRole(),
        ];
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/{slug}', name: 'show_by_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\\-]*'])]
    public function showBySlug(string $slug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($slug);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($this->serializer->serialize($provider, 'json', [
            'groups' => ['provider:read']
        ]), true);
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

            $provider = new Provider();
            $provider->setFirstName($data['firstName'] ?? '');
            $provider->setLastName($data['lastName'] ?? '');
            $provider->setEmail($data['email'] ?? '');
            $provider->setPassword($data['password'] ?? ''); // TODO: Hash password
            $provider->setCity($data['city'] ?? '');
            $provider->setState($data['state'] ?? '');
            $provider->setPostalCode($data['postalCode'] ?? '');
            $provider->setAddress($data['address'] ?? '');
            $provider->setDescription($data['description'] ?? null);
            $provider->setJoinedAt(new \DateTimeImmutable());

            // Set relations if provided
            if (isset($data['jobId'])) {
                // TODO: Set job relation
            }
            if (isset($data['countryId'])) {
                // TODO: Set country relation
            }

            // Validate entity
            $errors = $this->validator->validate($provider);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($provider);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $provider,
                'message' => 'Provider created successfully'
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
        return new JsonResponse([
            'success' => false,
            'error' => 'Deprecated endpoint. Use PATCH /api/v1/auth/complete-provider-profile'
        ], Response::HTTP_GONE);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => 'Deprecated endpoint. Use appropriate authenticated profile routes'
        ], Response::HTTP_GONE);
    }

    #[Route('/{id}/password', name: 'change_password', methods: ['PATCH'], requirements: ['id' => '\\d+'])]
    public function changePassword(int $id, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
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

        if (!$this->passwordHasher->isPasswordValid($provider, $currentPassword)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Mot de passe actuel invalide'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $hashed = $this->passwordHasher->hashPassword($provider, $newPassword);
        $provider->setPassword($hashed);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Mot de passe mis à jour'
        ]);
    }

    #[Route('/{id}/profile-image', name: 'upload_profile_image', methods: ['POST'])]
    public function uploadProfileImage(int $id, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
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
            $fileUrl = $this->fileUploadService->uploadProviderProfilePicture(
                $uploadedFile->getPathname(),
                $provider->getId()
            );

            $provider->setProfilePicture($fileUrl);
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

    #[Route('/{id}/services', name: 'get_services', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function getServices(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // TODO: Get services from ServiceRepository
        $services = []; // $this->serviceRepository->findByProviderId($id);

        return new JsonResponse([
            'success' => true,
            'data' => $services,
            'total' => count($services)
        ]);
    }

    #[Route('/{slug}/services', name: 'get_services_by_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function getServicesByProviderSlug(string $slug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($slug);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Récupérer les services du provider via repository
        $serviceRepo = $this->entityManager->getRepository(\App\Entity\Service::class);
        $services = $serviceRepo->findBy(['provider' => $provider]);

        $data = json_decode($this->serializer->serialize($services, 'json', [
            'groups' => ['service:read']
        ]), true);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ]);
    }

    #[Route('/{providerSlug}/services/{serviceSlug}/cover', name: 'upload_service_cover', methods: ['POST'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'serviceSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function uploadServiceCover(string $providerSlug, string $serviceSlug, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $uploadedFile = $request->files->get('service_cover');
        if (!$uploadedFile) {
            return new JsonResponse(['success' => false, 'error' => 'No file uploaded (expected field: service_cover)'], Response::HTTP_BAD_REQUEST);
        }

        $serviceRepo = $this->entityManager->getRepository(\App\Entity\Service::class);
        $service = $serviceRepo->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->andWhere('p = :provider AND s.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $serviceSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }

        $dir = UploadConfig::getServiceCoverPath((int)$provider->getId(), (int)$service->getId());
        $abs = UploadConfig::getUploadPath($dir);
        if (!is_dir($abs)) {
            @mkdir($abs, 0755, true);
        }
        $unique = UploadConfig::generateUniqueFilename($uploadedFile->getClientOriginalName(), $dir);
        $uploadedFile->move($abs, $unique);
        $url = UploadConfig::getRelativeUrl($dir, $unique);
        $service->setCover($url);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'data' => ['cover_url' => $url]]);
    }

    #[Route('/{providerSlug}/services/{serviceSlug}', name: 'get_service_by_provider_slug_and_service_slug', methods: ['GET'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'serviceSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function getServiceByProviderAndServiceSlug(string $providerSlug, string $serviceSlug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $serviceRepo = $this->entityManager->getRepository(\App\Entity\Service::class);
        $service = $serviceRepo->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->andWhere('p = :provider AND s.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $serviceSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($this->serializer->serialize($service, 'json', [
            'groups' => ['service:read']
        ]), true);

        return new JsonResponse(['success' => true, 'data' => $data]);
    }

    #[Route('/{providerSlug}/services/{serviceSlug}', name: 'delete_service_by_provider_slug_and_service_slug', methods: ['DELETE'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'serviceSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function deleteServiceByProviderAndServiceSlug(string $providerSlug, string $serviceSlug, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $currentPassword = (string)($data['currentPassword'] ?? '');
        if ($currentPassword === '') {
            return new JsonResponse(['success' => false, 'error' => 'currentPassword is required'], Response::HTTP_BAD_REQUEST);
        }
        if (!$this->passwordHasher->isPasswordValid($provider, $currentPassword)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        $serviceRepo = $this->entityManager->getRepository(\App\Entity\Service::class);
        $service = $serviceRepo->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->andWhere('p = :provider AND s.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $serviceSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($service);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true, 'message' => 'Service deleted']);
    }

    #[Route('/{providerSlug}/services/{serviceSlug}', name: 'update_service_by_provider_slug_and_service_slug', methods: ['PUT'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'serviceSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function updateServiceByProviderAndServiceSlug(string $providerSlug, string $serviceSlug, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $serviceRepo = $this->entityManager->getRepository(\App\Entity\Service::class);
        $service = $serviceRepo->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->andWhere('p = :provider AND s.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $serviceSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$service) {
            return new JsonResponse(['success' => false, 'error' => 'Service not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) $service->setTitle($data['title']);
        if (isset($data['summary'])) $service->setSummary($data['summary']);
        if (isset($data['minPrice'])) $service->setMinPrice($data['minPrice']);
        if (isset($data['maxPrice'])) $service->setMaxPrice($data['maxPrice']);
        if (isset($data['slug'])) $service->setSlug($data['slug']);

        $errors = $this->validator->validate($service);
        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'errors' => (string) $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Service updated successfully']);
    }

    #[Route('/{providerSlug}/services/{serviceSlug}', name: 'patch_service_by_provider_slug_and_service_slug', methods: ['PATCH'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'serviceSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function patchServiceByProviderAndServiceSlug(string $providerSlug, string $serviceSlug, Request $request): JsonResponse
    {
        // même logique que PUT (modif partielle déjà gérée par isset)
        return $this->updateServiceByProviderAndServiceSlug($providerSlug, $serviceSlug, $request);
    }

    #[Route('/{slug}/articles', name: 'get_articles_by_provider_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function getArticlesByProviderSlug(string $slug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($slug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $articleRepo = $this->entityManager->getRepository(\App\Entity\Article::class);
        $articles = $articleRepo->findBy(['provider' => $provider]);

        $data = json_decode($this->serializer->serialize($articles, 'json', [
            'groups' => ['article:read']
        ]), true);

        return new JsonResponse(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    #[Route('/{providerSlug}/articles/{articleSlug}', name: 'get_article_by_provider_slug_and_article_slug', methods: ['GET'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'articleSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function getArticleByProviderAndArticleSlug(string $providerSlug, string $articleSlug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $articleRepo = $this->entityManager->getRepository(\App\Entity\Article::class);
        $article = $articleRepo->createQueryBuilder('a')
            ->innerJoin('a.provider', 'p')
            ->andWhere('p = :provider AND a.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $articleSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($this->serializer->serialize($article, 'json', [
            'groups' => ['article:read']
        ]), true);

        return new JsonResponse(['success' => true, 'data' => $data]);
    }

    #[Route('/{providerSlug}/articles/{articleSlug}/cover', name: 'upload_article_cover', methods: ['POST'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'articleSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function uploadArticleCover(string $providerSlug, string $articleSlug, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }
        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $uploadedFile = $request->files->get('article_cover');
        if (!$uploadedFile) {
            return new JsonResponse(['success' => false, 'error' => 'No file uploaded (expected field: article_cover)'], Response::HTTP_BAD_REQUEST);
        }

        $articleRepo = $this->entityManager->getRepository(\App\Entity\Article::class);
        $article = $articleRepo->createQueryBuilder('a')
            ->innerJoin('a.provider', 'p')
            ->andWhere('p = :provider AND a.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $articleSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $dir = UploadConfig::getArticleCoverPath((int)$provider->getId(), (int)$article->getId());
        $abs = UploadConfig::getUploadPath($dir);
        if (!is_dir($abs)) {
            @mkdir($abs, 0755, true);
        }
        $unique = UploadConfig::generateUniqueFilename($uploadedFile->getClientOriginalName(), $dir);
        $uploadedFile->move($abs, $unique);
        $url = UploadConfig::getRelativeUrl($dir, $unique);
        $article->setCover($url);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'data' => ['cover_url' => $url]]);
    }

    #[Route('/{providerSlug}/experiences/{experienceId}/logo', name: 'upload_experience_logo', methods: ['POST'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'experienceId' => '\\d+'])]
    public function uploadExperienceLogo(string $providerSlug, int $experienceId, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }
        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        $uploadedFile = $request->files->get('experience_logo');
        if (!$uploadedFile) {
            return new JsonResponse(['success' => false, 'error' => 'No file uploaded (expected field: experience_logo)'], Response::HTTP_BAD_REQUEST);
        }

        $expRepo = $this->entityManager->getRepository(\App\Entity\Experience::class);
        $experience = $expRepo->createQueryBuilder('e')
            ->innerJoin('e.provider', 'p')
            ->andWhere('p = :provider AND e.id = :id')
            ->setParameter('provider', $provider)
            ->setParameter('id', $experienceId)
            ->getQuery()->getOneOrNullResult();
        if (!$experience) {
            return new JsonResponse(['success' => false, 'error' => 'Experience not found'], Response::HTTP_NOT_FOUND);
        }

        $dir = UploadConfig::getExperiencePath((int)$provider->getId(), (int)$experience->getId());
        $abs = UploadConfig::getUploadPath($dir);
        if (!is_dir($abs)) {
            @mkdir($abs, 0755, true);
        }
        $unique = UploadConfig::generateUniqueFilename($uploadedFile->getClientOriginalName(), $dir);
        $uploadedFile->move($abs, $unique);
        $url = UploadConfig::getRelativeUrl($dir, $unique);
        $experience->setCompanyLogo($url);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true, 'data' => ['logo_url' => $url]]);
    }

    #[Route('/{providerSlug}/educations/{educationId}/logo', name: 'upload_education_logo', methods: ['POST'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'educationId' => '\\d+'])]
    public function uploadEducationLogo(string $providerSlug, int $educationId, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }
        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        $uploadedFile = $request->files->get('education_logo');
        if (!$uploadedFile) {
            return new JsonResponse(['success' => false, 'error' => 'No file uploaded (expected field: education_logo)'], Response::HTTP_BAD_REQUEST);
        }

        $eduRepo = $this->entityManager->getRepository(\App\Entity\Education::class);
        $education = $eduRepo->createQueryBuilder('e')
            ->innerJoin('e.provider', 'p')
            ->andWhere('p = :provider AND e.id = :id')
            ->setParameter('provider', $provider)
            ->setParameter('id', $educationId)
            ->getQuery()->getOneOrNullResult();
        if (!$education) {
            return new JsonResponse(['success' => false, 'error' => 'Education not found'], Response::HTTP_NOT_FOUND);
        }

        $dir = UploadConfig::getEducationPath((int)$provider->getId(), (int)$education->getId());
        $abs = UploadConfig::getUploadPath($dir);
        if (!is_dir($abs)) {
            @mkdir($abs, 0755, true);
        }
        $unique = UploadConfig::generateUniqueFilename($uploadedFile->getClientOriginalName(), $dir);
        $uploadedFile->move($abs, $unique);
        $url = UploadConfig::getRelativeUrl($dir, $unique);
        $education->setInstitutionImage($url);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true, 'data' => ['logo_url' => $url]]);
    }

    #[Route('/{providerSlug}/articles/{articleSlug}', name: 'delete_article_by_provider_slug_and_article_slug', methods: ['DELETE'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'articleSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function deleteArticleByProviderAndArticleSlug(string $providerSlug, string $articleSlug, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $currentPassword = (string)($data['currentPassword'] ?? '');
        if ($currentPassword === '') {
            return new JsonResponse(['success' => false, 'error' => 'currentPassword is required'], Response::HTTP_BAD_REQUEST);
        }
        if (!$this->passwordHasher->isPasswordValid($provider, $currentPassword)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        $articleRepo = $this->entityManager->getRepository(\App\Entity\Article::class);
        $article = $articleRepo->createQueryBuilder('a')
            ->innerJoin('a.provider', 'p')
            ->andWhere('p = :provider AND a.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $articleSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true, 'message' => 'Article deleted']);
    }
    #[Route('/{providerSlug}/articles/{articleSlug}', name: 'update_article_by_provider_slug_and_article_slug', methods: ['PUT'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'articleSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function updateArticleByProviderAndArticleSlug(string $providerSlug, string $articleSlug, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $articleRepo = $this->entityManager->getRepository(\App\Entity\Article::class);
        $article = $articleRepo->createQueryBuilder('a')
            ->innerJoin('a.provider', 'p')
            ->andWhere('p = :provider AND a.slug = :slug')
            ->setParameter('provider', $provider)
            ->setParameter('slug', $articleSlug)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) $article->setTitle($data['title']);
        if (isset($data['summary'])) $article->setSummary($data['summary']);
        if (isset($data['isPublished'])) {
            $article->setIsPublished($data['isPublished']);
            if ($data['isPublished'] && !$article->getPublishedAt()) {
                $article->setPublishedAt(new \DateTimeImmutable());
            }
        }
        if (isset($data['isFeatured'])) $article->setIsFeatured($data['isFeatured']);
        if (isset($data['slug'])) $article->setSlug($data['slug']);
        $article->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($article);
        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'errors' => (string) $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Article updated successfully']);
    }

    #[Route('/{providerSlug}/articles/{articleSlug}', name: 'patch_article_by_provider_slug_and_article_slug', methods: ['PATCH'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'articleSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function patchArticleByProviderAndArticleSlug(string $providerSlug, string $articleSlug, Request $request): JsonResponse
    {
        // même logique que PUT (modif partielle déjà gérée par isset)
        return $this->updateArticleByProviderAndArticleSlug($providerSlug, $articleSlug, $request);
    }

    #[Route('/{id}/articles', name: 'get_articles', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function getArticles(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // TODO: Get articles from ArticleRepository
        $articles = []; // $this->articleRepository->findByProviderId($id);

        return new JsonResponse([
            'success' => true,
            'data' => $articles,
            'total' => count($articles)
        ]);
    }

    #[Route('/{id}/reviews', name: 'get_reviews', methods: ['GET'])]
    public function getReviews(int $id): JsonResponse
    {
        $provider = $this->providerRepository->findById($id);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // TODO: Get reviews from ReviewRepository
        $reviews = []; // $this->reviewRepository->findByProviderId($id);

        return new JsonResponse([
            'success' => true,
            'data' => $reviews,
            'total' => count($reviews)
        ]);
    }

    #[Route('/{slug}/experiences', name: 'get_experiences_by_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function getExperiencesBySlug(string $slug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($slug);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Récupérer les expériences du provider
        $experienceRepo = $this->entityManager->getRepository(\App\Entity\Experience::class);
        $experiences = $experienceRepo->findBy(['provider' => $provider]);

        $data = [];
        foreach ($experiences as $exp) {
            $data[] = [
                'id' => $exp->getId(),
                'title' => $exp->getTitle(),
                'companyName' => $exp->getCompanyName(),
                'firstTask' => $exp->getFirstTask(),
                'secondTask' => $exp->getSecondTask(),
                'thirdTask' => $exp->getThirdTask(),
                'startDate' => $exp->getStartedAt()?->format('Y-m-d'),
                'endDate' => $exp->getEndedAt()?->format('Y-m-d'),
                'companyLogo' => $exp->getCompanyLogo(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ]);
    }

    #[Route('/{slug}/educations', name: 'get_educations_by_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function getEducationsBySlug(string $slug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($slug);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Récupérer les éducations du provider
        $educationRepo = $this->entityManager->getRepository(\App\Entity\Education::class);
        $educations = $educationRepo->findBy(['provider' => $provider]);

        $data = [];
        foreach ($educations as $edu) {
            $data[] = [
                'id' => $edu->getId(),
                'title' => $edu->getTitle(),
                'institutionName' => $edu->getInstitutionName(),
                'description' => $edu->getDescription(),
                'startDate' => $edu->getStartedAt()?->format('Y-m-d'),
                'endDate' => $edu->getEndedAt()?->format('Y-m-d'),
                'institutionImage' => $edu->getInstitutionImage(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($data)
        ]);
    }

    #[Route('/{slug}/reviews', name: 'get_reviews_by_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\-]*'])]
    public function getReviewsBySlug(string $slug): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($slug);

        if (!$provider) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Provider not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // TODO: Get reviews from ReviewRepository
        $reviews = []; // $this->reviewRepository->findByProvider($provider);

        return new JsonResponse([
            'success' => true,
            'data' => $reviews,
            'total' => count($reviews)
        ]);
    }

    #[Route('/{providerSlug}/experiences/{experienceId}', name: 'update_experience_by_provider_slug_and_experience_id', methods: ['PUT'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'experienceId' => '\\d+'])]
    public function updateExperienceByProviderSlugAndExperienceId(string $providerSlug, int $experienceId, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $experienceRepo = $this->entityManager->getRepository(\App\Entity\Experience::class);
        $experience = $experienceRepo->createQueryBuilder('e')
            ->innerJoin('e.provider', 'p')
            ->andWhere('p = :provider AND e.id = :id')
            ->setParameter('provider', $provider)
            ->setParameter('id', $experienceId)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$experience) {
            return new JsonResponse(['success' => false, 'error' => 'Experience not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) $experience->setTitle($data['title']);
        if (isset($data['companyName'])) $experience->setCompanyName($data['companyName']);
        if (isset($data['firstTask'])) $experience->setFirstTask($data['firstTask']);
        if (isset($data['secondTask'])) $experience->setSecondTask($data['secondTask']);
        if (isset($data['thirdTask'])) $experience->setThirdTask($data['thirdTask']);
        if (isset($data['startDate'])) $experience->setStartedAt(new \DateTimeImmutable($data['startDate']));
        if (isset($data['endDate'])) $experience->setEndedAt(new \DateTimeImmutable($data['endDate']));
        if (isset($data['companyLogo'])) $experience->setCompanyLogo($data['companyLogo']);

        $errors = $this->validator->validate($experience);
        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'errors' => (string) $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Experience updated successfully']);
    }

    #[Route('/{providerSlug}/experiences/{experienceId}', name: 'patch_experience_by_provider_slug_and_experience_id', methods: ['PATCH'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'experienceId' => '\\d+'])]
    public function patchExperienceByProviderSlugAndExperienceId(string $providerSlug, int $experienceId, Request $request): JsonResponse
    {
        return $this->updateExperienceByProviderSlugAndExperienceId($providerSlug, $experienceId, $request);
    }

    #[Route('/{providerSlug}/educations/{educationId}', name: 'update_education_by_provider_slug_and_education_id', methods: ['PUT'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'educationId' => '\\d+'])]
    public function updateEducationByProviderSlugAndEducationId(string $providerSlug, int $educationId, Request $request): JsonResponse
    {
        $provider = $this->providerRepository->findBySlug($providerSlug);
        if (!$provider) {
            return new JsonResponse(['success' => false, 'error' => 'Provider not found'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        if (!$currentUser || !($currentUser instanceof \App\Entity\Provider) || $currentUser->getId() !== $provider->getId()) {
            return new JsonResponse(['success' => false, 'error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $educationRepo = $this->entityManager->getRepository(\App\Entity\Education::class);
        $education = $educationRepo->createQueryBuilder('e')
            ->innerJoin('e.provider', 'p')
            ->andWhere('p = :provider AND e.id = :id')
            ->setParameter('provider', $provider)
            ->setParameter('id', $educationId)
            ->getQuery()
            ->getOneOrNullResult();
        if (!$education) {
            return new JsonResponse(['success' => false, 'error' => 'Education not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) $education->setTitle($data['title']);
        if (isset($data['institutionName'])) $education->setInstitutionName($data['institutionName']);
        if (isset($data['description'])) $education->setDescription($data['description']);
        if (isset($data['startDate'])) $education->setStartedAt(new \DateTimeImmutable($data['startDate']));
        if (isset($data['endDate'])) $education->setEndedAt(new \DateTimeImmutable($data['endDate']));
        if (isset($data['institutionImage'])) $education->setInstitutionImage($data['institutionImage']);

        $errors = $this->validator->validate($education);
        if (count($errors) > 0) {
            return new JsonResponse([
                'success' => false,
                'errors' => (string) $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Education updated successfully']);
    }

    #[Route('/{providerSlug}/educations/{educationId}', name: 'patch_education_by_provider_slug_and_education_id', methods: ['PATCH'], requirements: ['providerSlug' => '[A-Za-z0-9][A-Za-z0-9\-]*', 'educationId' => '\\d+'])]
    public function patchEducationByProviderSlugAndEducationId(string $providerSlug, int $educationId, Request $request): JsonResponse
    {
        return $this->updateEducationByProviderSlugAndEducationId($providerSlug, $educationId, $request);
    }

    #[Route('/{id}/permissions', name: 'check_permissions', methods: ['GET'])]
    public function checkPermissions(Provider $provider): JsonResponse
    {
        $permissions = [
            'can_view' => $this->authorizationChecker->isGranted('VIEW', $provider),
            'can_edit' => $this->authorizationChecker->isGranted('EDIT', $provider),
            'can_delete' => $this->authorizationChecker->isGranted('DELETE', $provider),
            'can_view_contact' => $this->authorizationChecker->isGranted('VIEW_CONTACT', $provider),
            'can_view_stats' => $this->authorizationChecker->isGranted('VIEW_STATS', $provider),
            'can_manage_services' => $this->authorizationChecker->isGranted('MANAGE_SERVICES', $provider),
            'can_manage_articles' => $this->authorizationChecker->isGranted('MANAGE_ARTICLES', $provider),
        ];

        return new JsonResponse([
            'provider_id' => $provider->getId(),
            'provider_name' => $provider->getFirstName() . ' ' . $provider->getLastName(),
            'provider_email' => $provider->getEmail(),
            'permissions' => $permissions
        ]);
    }
}
