<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\SocialLinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SocialLinkRepository::class)]
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
    normalizationContext: ['groups' => ['social_link:read']],
    denormalizationContext: ['groups' => ['social_link:write']]
)]
class SocialLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['social_link:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['social_link:read', 'social_link:write', 'social_link:write:item', 'provider:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $platform = null;

    #[ORM\Column(length: 255)]
    #[Groups(['social_link:read', 'social_link:write', 'social_link:write:item', 'provider:read'])]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: Provider::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['social_link:write'])]
    private ?Provider $provider = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): static
    {
        $this->platform = $platform;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

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
