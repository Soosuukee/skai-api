<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\ExperienceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
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
    normalizationContext: ['groups' => ['experience:read']],
    denormalizationContext: ['groups' => ['experience:write']]
)]
class Experience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['experience:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: Provider::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['experience:write'])]
    private ?Provider $provider = null;

    #[ORM\Column(length: 255)]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $firstTask = null;

    #[ORM\Column(length: 255)]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $secondTask = null;

    #[ORM\Column(length: 255)]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $thirdTask = null;

    #[ORM\Column]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['experience:read', 'experience:write', 'experience:write:item'])]
    #[Assert\Length(max: 255)]
    private ?string $companyLogo = null;

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

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getFirstTask(): ?string
    {
        return $this->firstTask;
    }

    public function setFirstTask(string $firstTask): static
    {
        $this->firstTask = $firstTask;

        return $this;
    }

    public function getSecondTask(): ?string
    {
        return $this->secondTask;
    }

    public function setSecondTask(string $secondTask): static
    {
        $this->secondTask = $secondTask;

        return $this;
    }

    public function getThirdTask(): ?string
    {
        return $this->thirdTask;
    }

    public function setThirdTask(string $thirdTask): static
    {
        $this->thirdTask = $thirdTask;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getCompanyLogo(): ?string
    {
        return $this->companyLogo;
    }

    public function setCompanyLogo(string $companyLogo): static
    {
        $this->companyLogo = $companyLogo;

        return $this;
    }
}
