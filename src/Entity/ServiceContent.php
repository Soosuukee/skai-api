<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\ServiceContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ServiceContentRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['service:read']],
    denormalizationContext: ['groups' => ['service:write']]
)]
class ServiceContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['service:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['service:read'])]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: ServiceSection::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceSection $serviceSection = null;

    #[ORM\OneToMany(mappedBy: 'serviceContent', targetEntity: ServiceImage::class, orphanRemoval: true)]
    #[Groups(['service:read'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @return Collection<int, ServiceImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ServiceImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setServiceContent($this);
        }

        return $this;
    }

    public function removeImage(ServiceImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getServiceContent() === $this) {
                $image->setServiceContent(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getServiceSection(): ?ServiceSection
    {
        return $this->serviceSection;
    }

    public function setServiceSection(?ServiceSection $serviceSection): static
    {
        $this->serviceSection = $serviceSection;

        return $this;
    }
}
