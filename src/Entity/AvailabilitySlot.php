<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\AvailabilitySlotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvailabilitySlotRepository::class)]
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
    normalizationContext: ['groups' => ['availability_slot:read']],
    denormalizationContext: ['groups' => ['availability_slot:write']]
)]
class AvailabilitySlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['availability_slot:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['availability_slot:read', 'availability_slot:write', 'availability_slot:write:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $startTime = null;

    #[ORM\Column(length: 255)]
    #[Groups(['availability_slot:read', 'availability_slot:write', 'availability_slot:write:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $endTime = null;

    #[ORM\ManyToOne(targetEntity: Provider::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['availability_slot:read', 'availability_slot:write'])]
    private ?Provider $provider = null;

    #[ORM\Column]
    #[Groups(['availability_slot:read', 'availability_slot:write:item'])]
    private ?bool $isBooked = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(string $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function isBooked(): ?bool
    {
        return $this->isBooked;
    }

    public function setIsBooked(bool $isBooked): static
    {
        $this->isBooked = $isBooked;

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
}
