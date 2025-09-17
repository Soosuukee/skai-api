<?php

namespace App\Entity;

use App\Repository\CompletedWorkMediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompletedWorkMediaRepository::class)]
class CompletedWorkMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $mediaType = null;

    #[ORM\Column(length: 255)]
    private ?string $mediaUrl = null;

    #[ORM\ManyToOne(targetEntity: CompletedWork::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CompletedWork $work = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMediaType(): ?string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    public function getWork(): ?CompletedWork
    {
        return $this->work;
    }

    public function setWork(?CompletedWork $work): static
    {
        $this->work = $work;

        return $this;
    }
}
