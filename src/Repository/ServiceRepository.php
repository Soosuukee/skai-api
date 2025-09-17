<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function findByProviderId(int $providerId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.provider = :providerId')
            ->setParameter('providerId', $providerId)
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Service
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveServices(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    public function findFeaturedServices(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isFeatured = :featured')
            ->setParameter('featured', true)
            ->getQuery()
            ->getResult();
    }

    public function findByPriceRange(float $minPrice, float $maxPrice): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.minPrice >= :minPrice AND s.maxPrice <= :maxPrice')
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice)
            ->getQuery()
            ->getResult();
    }

    public function searchServices(string $query): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.title LIKE :query OR s.summary LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Service
    {
        return $this->find($id);
    }

    public function findByProviderSlug(string $providerSlug): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.provider', 'p')
            ->andWhere('p.slug = :providerSlug')
            ->setParameter('providerSlug', $providerSlug)
            ->getQuery()
            ->getResult();
    }

    public function findByTagId(int $tagId): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.tags', 't')
            ->andWhere('t.id = :tagId')
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getResult();
    }

    public function findByTagSlug(string $tagSlug): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.tags', 't')
            ->andWhere('t.slug = :tagSlug')
            ->setParameter('tagSlug', $tagSlug)
            ->getQuery()
            ->getResult();
    }

    public function getServiceWithContent(int $serviceId): ?array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.sections', 'sec')
            ->leftJoin('sec.contents', 'c')
            ->leftJoin('c.images', 'i')
            ->addSelect('sec', 'c', 'i')
            ->andWhere('s.id = :serviceId')
            ->setParameter('serviceId', $serviceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function saveServiceWithContent(Service $service, array $sections): void
    {
        $em = $this->getEntityManager();
        $em->persist($service);

        foreach ($sections as $sectionData) {
            $section = $sectionData['section'];
            $contents = $sectionData['contents'] ?? [];

            $section->setService($service);
            $em->persist($section);

            foreach ($contents as $contentData) {
                $content = $contentData['content'];
                $images = $contentData['images'] ?? [];

                $content->setServiceSection($section);
                $em->persist($content);

                foreach ($images as $image) {
                    $image->setServiceContent($content);
                    $em->persist($image);
                }
            }
        }

        $em->flush();
    }

    public function findServiceImageById(int $imageId): ?array
    {
        return $this->getEntityManager()
            ->getRepository(\App\Entity\ServiceImage::class)
            ->createQueryBuilder('si')
            ->innerJoin('si.serviceContent', 'sc')
            ->innerJoin('sc.serviceSection', 'ss')
            ->innerJoin('ss.service', 's')
            ->addSelect('sc', 'ss', 's')
            ->andWhere('si.id = :imageId')
            ->setParameter('imageId', $imageId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteServiceImageById(int $imageId): void
    {
        $image = $this->getEntityManager()
            ->getRepository(\App\Entity\ServiceImage::class)
            ->find($imageId);

        if ($image) {
            $em = $this->getEntityManager();
            $em->remove($image);
            $em->flush();
        }
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function save(Service $entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function update(Service $entity): void
    {
        $em = $this->getEntityManager();
        $em->flush();
    }

    public function delete(int $id): void
    {
        $entity = $this->find($id);
        if ($entity) {
            $em = $this->getEntityManager();
            $em->remove($entity);
            $em->flush();
        }
    }
}
