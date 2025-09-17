<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/services', name: 'api_services_')]
class ServiceController extends AbstractController
{
    public function __construct(
        private ServiceRepository $serviceRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private FileUploadService $fileUploadService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $services = $this->serviceRepository->findAll();

        $data = array_map(function (Service $s) {
            $sections = [];
            foreach ($s->getSections() as $section) {
                $contents = [];
                foreach ($section->getContents() as $content) {
                    $images = [];
                    foreach ($content->getImages() as $image) {
                        $images[] = [
                            'id' => $image->getId(),
                            'serviceContentId' => $content->getId(),
                            'url' => $image->getUrl(),
                        ];
                    }
                    $contents[] = [
                        'id' => $content->getId(),
                        'serviceSectionId' => $section->getId(),
                        'content' => (string) $content->getContent(),
                        'images' => $images,
                    ];
                }
                $sections[] = [
                    'id' => $section->getId(),
                    'serviceId' => $s->getId(),
                    'title' => (string) $section->getTitle(),
                    'content' => $contents,
                ];
            }

            return [
                'id' => $s->getId(),
                'title' => $s->getTitle(),
                'providerId' => $s->getProvider()?->getId(),
                'summary' => $s->getSummary(),
                'slug' => $s->getSlug(),
                'minPrice' => $s->getMinPrice(),
                'maxPrice' => $s->getMaxPrice(),
                'cover' => $s->getCover(),
                'createdAt' => $s->getCreatedAt()?->format(DATE_ATOM),
                'tags' => array_map(fn($t) => $t->getTitle(), $s->getTags()->toArray()),
                'sections' => $sections,
            ];
        }, $services);

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'total' => count($services)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id): JsonResponse
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $sections = [];
        foreach ($service->getSections() as $section) {
            $contents = [];
            foreach ($section->getContents() as $content) {
                $images = [];
                foreach ($content->getImages() as $image) {
                    $images[] = [
                        'id' => $image->getId(),
                        'serviceContentId' => $content->getId(),
                        'url' => $image->getUrl(),
                    ];
                }
                $contents[] = [
                    'id' => $content->getId(),
                    'serviceSectionId' => $section->getId(),
                    'content' => (string) $content->getContent(),
                    'images' => $images,
                ];
            }
            $sections[] = [
                'id' => $section->getId(),
                'serviceId' => $service->getId(),
                'title' => (string) $section->getTitle(),
                'content' => $contents,
            ];
        }

        $data = [
            'id' => $service->getId(),
            'title' => $service->getTitle(),
            'providerId' => $service->getProvider()?->getId(),
            'summary' => $service->getSummary(),
            'slug' => $service->getSlug(),
            'minPrice' => $service->getMinPrice(),
            'maxPrice' => $service->getMaxPrice(),
            'cover' => $service->getCover(),
            'createdAt' => $service->getCreatedAt()?->format(DATE_ATOM),
            'tags' => array_map(fn($t) => $t->getTitle(), $service->getTags()->toArray()),
            'sections' => $sections,
        ];
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/{slug}', name: 'show_by_slug', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9][A-Za-z0-9\\-]*'])]
    public function showBySlug(string $slug): JsonResponse
    {
        $service = $this->serviceRepository->findBySlug($slug);

        if (!$service) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $sections = [];
        foreach ($service->getSections() as $section) {
            $contents = [];
            foreach ($section->getContents() as $content) {
                $images = [];
                foreach ($content->getImages() as $image) {
                    $images[] = [
                        'id' => $image->getId(),
                        'serviceContentId' => $content->getId(),
                        'url' => $image->getUrl(),
                    ];
                }
                $contents[] = [
                    'id' => $content->getId(),
                    'serviceSectionId' => $section->getId(),
                    'content' => (string) $content->getContent(),
                    'images' => $images,
                ];
            }
            $sections[] = [
                'id' => $section->getId(),
                'serviceId' => $service->getId(),
                'title' => (string) $section->getTitle(),
                'content' => $contents,
            ];
        }

        $data = [
            'id' => $service->getId(),
            'title' => $service->getTitle(),
            'providerId' => $service->getProvider()?->getId(),
            'summary' => $service->getSummary(),
            'slug' => $service->getSlug(),
            'minPrice' => $service->getMinPrice(),
            'maxPrice' => $service->getMaxPrice(),
            'cover' => $service->getCover(),
            'createdAt' => $service->getCreatedAt()?->format(DATE_ATOM),
            'tags' => array_map(fn($t) => $t->getTitle(), $service->getTags()->toArray()),
            'sections' => $sections,
        ];
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

            $service = new Service();
            $service->setTitle($data['title'] ?? '');
            $service->setSummary($data['summary'] ?? '');
            $service->setMinPrice($data['minPrice'] ?? 0);
            $service->setMaxPrice($data['maxPrice'] ?? 0);
            $service->setIsActive($data['isActive'] ?? true);
            $service->setIsFeatured($data['isFeatured'] ?? false);
            $service->setCreatedAt(new \DateTimeImmutable());

            // Set relations if provided
            if (isset($data['providerId'])) {
                // TODO: Set provider relation
            }

            // Validate entity
            $errors = $this->validator->validate($service);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($service);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $service,
                'message' => 'Service created successfully'
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
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (isset($data['title'])) $service->setTitle($data['title']);
            if (isset($data['summary'])) $service->setSummary($data['summary']);
            if (isset($data['minPrice'])) $service->setMinPrice($data['minPrice']);
            if (isset($data['maxPrice'])) $service->setMaxPrice($data['maxPrice']);
            if (isset($data['isActive'])) $service->setIsActive($data['isActive']);
            if (isset($data['isFeatured'])) $service->setIsFeatured($data['isFeatured']);

            // Validate entity
            $errors = $this->validator->validate($service);
            if (count($errors) > 0) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => $service,
                'message' => 'Service updated successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($service);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/images', name: 'upload_images', methods: ['POST'])]
    public function uploadImages(int $id, Request $request): JsonResponse
    {
        $service = $this->serviceRepository->findById($id);

        if (!$service) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $uploadedFiles = $request->files->all();

        if (empty($uploadedFiles)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No files uploaded'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $uploadedUrls = [];
            $providerId = $service->getProvider()?->getId();

            if (!$providerId) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Service provider not found'
                ], Response::HTTP_BAD_REQUEST);
            }

            foreach ($uploadedFiles as $key => $file) {
                // Determine if it's a cover image or content image
                if (strpos($key, 'cover') !== false) {
                    $fileUrl = $this->fileUploadService->uploadServiceCover(
                        $file->getPathname(),
                        $providerId,
                        $service->getId()
                    );
                    $service->setCover($fileUrl);
                } else {
                    // For content images, we need section and content IDs
                    // This would typically come from the request data
                    $sectionId = $request->request->get('sectionId', 1);
                    $contentId = $request->request->get('contentId', 1);

                    $fileUrl = $this->fileUploadService->uploadServiceImage(
                        $file->getPathname(),
                        $providerId,
                        $service->getId(),
                        (int) $sectionId,
                        (int) $contentId
                    );
                }

                $uploadedUrls[] = $fileUrl;
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'uploaded_urls' => $uploadedUrls
                ],
                'message' => 'Images uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
