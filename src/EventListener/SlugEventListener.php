<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Article;
use App\Entity\Country;
use App\Entity\Client;
use App\Entity\HardSkill;
use App\Entity\Language;
use App\Entity\Provider;
use App\Entity\Job;
use App\Entity\Service;
use App\Service\ProviderSlugService;
use App\Service\ClientSlugService;
use App\Service\ArticleSlugService;
use App\Service\ServiceSlugService;
use App\Service\CountrySlugService;
use App\Service\LanguageSlugService;
use App\Service\JobSlugService;
use App\Service\HardSkillSlugService;
use App\Service\SoftSkillSlugService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class SlugEventListener
{
    public function __construct(
        private ProviderSlugService $providerSlugService,
        private ClientSlugService $clientSlugService,
        private ArticleSlugService $articleSlugService,
        private ServiceSlugService $serviceSlugService,
        private CountrySlugService $countrySlugService,
        private LanguageSlugService $languageSlugService,
        private JobSlugService $jobSlugService,
        private HardSkillSlugService $hardSkillSlugService,
        private SoftSkillSlugService $softSkillSlugService
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Provider && !$entity->getSlug()) {
            $slug = $this->providerSlugService->generateSlugForNewProvider(
                $entity->getFirstName(),
                $entity->getLastName()
            );
            $entity->setSlug($slug);
        }

        if ($entity instanceof Client && !$entity->getSlug()) {
            $slug = $this->clientSlugService->generateSlugForNewClient(
                $entity->getFirstName(),
                $entity->getLastName()
            );
            $entity->setSlug($slug);
        }

        if ($entity instanceof Article && !$entity->getSlug()) {
            $slug = $this->articleSlugService->generateSlugForNewArticle($entity->getTitle());
            $entity->setSlug($slug);
        }

        if ($entity instanceof Service && !$entity->getSlug()) {
            $slug = $this->serviceSlugService->generateSlugForNewService($entity->getTitle());
            $entity->setSlug($slug);
        }

        if ($entity instanceof Country && !$entity->getSlug()) {
            $entity->setSlug($this->countrySlugService->generateSlugForNewCountry((string)$entity->getName()));
        }

        if ($entity instanceof Language && !$entity->getSlug()) {
            $entity->setSlug($this->languageSlugService->generateSlugForNewLanguage((string)$entity->getName()));
        }

        if ($entity instanceof Job && !$entity->getSlug()) {
            $entity->setSlug($this->jobSlugService->generateSlugForNewJob((string)$entity->getTitle()));
        }

        if ($entity instanceof HardSkill && !$entity->getSlug()) {
            $entity->setSlug($this->hardSkillSlugService->generateSlugForNewHardSkill((string)$entity->getTitle()));
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Provider) {
            if ($args->hasChangedField('firstName') || $args->hasChangedField('lastName')) {
                $this->providerSlugService->updateProviderSlug($entity);
            }
        }

        if ($entity instanceof Client) {
            if ($args->hasChangedField('firstName') || $args->hasChangedField('lastName')) {
                $this->clientSlugService->updateClientSlug($entity);
            }
        }

        if ($entity instanceof Article) {
            if ($args->hasChangedField('title')) {
                $this->articleSlugService->updateArticleSlug($entity);
            }
        }

        if ($entity instanceof Service) {
            if ($args->hasChangedField('title')) {
                $this->serviceSlugService->updateServiceSlug($entity);
            }
        }

        if ($entity instanceof Country) {
            if ($args->hasChangedField('name')) {
                $this->countrySlugService->updateCountrySlug($entity);
            }
        }

        if ($entity instanceof Language) {
            if ($args->hasChangedField('name')) {
                $this->languageSlugService->updateLanguageSlug($entity);
            }
        }

        if ($entity instanceof Job) {
            if ($args->hasChangedField('title')) {
                $this->jobSlugService->updateJobSlug($entity);
            }
        }

        if ($entity instanceof HardSkill) {
            if ($args->hasChangedField('title')) {
                $this->hardSkillSlugService->updateHardSkillSlug($entity);
            }
        }
    }
}
