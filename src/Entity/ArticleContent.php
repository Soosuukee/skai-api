<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Repository\ArticleContentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ArticleContentRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            security: "user and object.getArticleSection().getArticle().getProvider() == user"
        ),
        new Put(
            security: "user and object.getArticleSection().getArticle().getProvider() == user"
        ),
        new Patch(
            security: "user and object.getArticleSection().getArticle().getProvider() == user"
        ),
        new Delete(
            security: "user and object.getArticleSection().getArticle().getProvider() == user"
        )
    ],
    normalizationContext: ['groups' => ['article:read']],
    denormalizationContext: ['groups' => ['article:write']]
)]
class ArticleContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['article:read', 'article:write', 'article:write:item'])]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: ArticleSection::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['article:write'])]
    private ?ArticleSection $articleSection = null;

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

    public function getArticleSection(): ?ArticleSection
    {
        return $this->articleSection;
    }

    public function setArticleSection(?ArticleSection $articleSection): static
    {
        $this->articleSection = $articleSection;

        return $this;
    }
}
