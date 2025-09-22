<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            security: "user and object.getProvider() == user"
        ),
        new Put(
            security: "user and object.getProvider() == user"
        ),
        new Patch(
            security: "user and object.getProvider() == user"
        ),
        new Delete(
            security: "user and object.getProvider() == user"
        ),
    ],
    normalizationContext: ['groups' => ['service:read']],
    denormalizationContext: ['groups' => ['service:write']]
)]
#[SearchFilter(['title' => 'partial', 'summary' => 'partial', 'provider.firstName' => 'partial', 'provider.lastName' => 'partial'])]
#[OrderFilter(['title', 'createdAt'])]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['service:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['service:read', 'service:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: Provider::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['service:write'])]
    #[Assert\NotNull]
    private ?Provider $provider = null;

    #[ORM\Column(length: 255)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $summary = null;

    #[ORM\Column(length: 255)]
    #[Groups(['service:read'])]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $minPrice = null;

    #[ORM\Column(length: 255)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $maxPrice = null;

    #[ORM\Column]
    #[Groups(['service:read', 'service:write', 'service:write:item'])]
    private ?bool $isActive = null;

    #[ORM\Column]
    #[Groups(['service:read', 'service:write', 'service:write:item'])]
    private ?bool $isFeatured = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['service:read', 'service:write'])]
    private ?string $cover = null;

    #[ORM\Column]
    #[Groups(['service:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'services')]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: ServiceSection::class, orphanRemoval: true)]
    #[Groups(['service:read'])]
    private Collection $sections;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMinPrice(): ?string
    {
        return $this->minPrice;
    }

    public function setMinPrice(string $minPrice): static
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function getMaxPrice(): ?string
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(string $maxPrice): static
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    #[Groups(['service:read'])]
    #[SerializedName('providerId')]
    public function getProviderIdForRead(): ?int
    {
        return $this->provider?->getId();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addService($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeService($this);
        }

        return $this;
    }

    #[Groups(['service:read'])]
    #[SerializedName('tags')]
    public function getTagNamesForRead(): array
    {
        $names = [];
        foreach ($this->tags as $tag) {
            $names[] = $tag->getTitle();
        }
        return $names;
    }

    /**
     * @return Collection<int, ServiceSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(ServiceSection $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setService($this);
        }

        return $this;
    }

    public function removeSection(ServiceSection $section): static
    {
        if ($this->sections->removeElement($section)) {
            if ($section->getService() === $this) {
                $section->setService(null);
            }
        }

        return $this;
    }
}
