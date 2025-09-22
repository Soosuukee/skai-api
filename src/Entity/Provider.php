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
use App\Repository\ProviderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ProviderRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(
            security: "object == user"
        ),
        new Patch(
            denormalizationContext: ['groups' => ['provider:patch']],
            security: "object == user"
        ),
        new Delete(
            security: "object == user"
        ),
    ],
    normalizationContext: ['groups' => ['provider:read']],
    denormalizationContext: ['groups' => ['provider:write']]
)]
#[SearchFilter(['firstName' => 'partial', 'lastName' => 'partial', 'email' => 'partial', 'city' => 'partial', 'country.name' => 'partial', 'job.title' => 'partial', 'languages.name' => 'partial', 'hardSkills.title' => 'partial', 'softSkills.title' => 'partial'])]
#[OrderFilter(['firstName', 'lastName', 'email', 'joinedAt'])]
class Provider implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['provider:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Assert\Regex(pattern: '/^[\p{L} ]+$/u', message: 'Le prénom ne doit contenir que des lettres et des espaces')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Assert\Regex(pattern: '/^[\p{L} ]+$/u', message: 'Le nom ne doit contenir que des lettres et des espaces')]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:write', 'provider:patch'])]
    #[Assert\NotBlank(groups: ['provider:write'])]
    #[Assert\Length(min: 8, max: 255, minMessage: 'Le mot de passe doit contenir au moins 8 caractères')]
    #[Assert\Regex(pattern: '/^(?=.*[A-Za-z])(?=.*\d).+$/', message: 'Le mot de passe doit contenir au moins une lettre et un chiffre')]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['provider:read', 'provider:write'])]
    private ?string $profilePicture = null;

    #[ORM\Column]
    #[Groups(['provider:read'])]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'Le slug ne doit contenir que des lettres minuscules, des chiffres et des tirets (-)')]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: Job::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\NotNull]
    private ?Job $job = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\NotNull]
    private ?Country $country = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read', 'provider:write'])]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read', 'provider:write'])]
    private ?string $state = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read', 'provider:write'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read', 'provider:write'])]
    private ?string $address = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\LessThan('today', message: 'La date de naissance ne peut pas être dans le futur')]
    #[Assert\LessThanOrEqual(
        value: '-18 years',
        message: 'Vous devez avoir au moins 18 ans'
    )]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\ManyToMany(targetEntity: HardSkill::class, inversedBy: 'providers')]
    #[Groups(['provider:read', 'provider:write'])]
    private Collection $hardSkills;

    #[ORM\ManyToMany(targetEntity: SoftSkill::class, inversedBy: 'providers')]
    #[Groups(['provider:read', 'provider:write'])]
    private Collection $softSkills;

    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'providers')]
    #[Groups(['provider:read', 'provider:write'])]
    private Collection $languages;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Service::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: Article::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: AvailabilitySlot::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $availabilitySlots;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: SocialLink::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $socialLinks;

    public function __construct()
    {
        $this->hardSkills = new ArrayCollection();
        $this->softSkills = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->availabilitySlots = new ArrayCollection();
        $this->socialLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

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

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): static
    {
        $this->job = $job;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @return Collection<int, HardSkill>
     */
    public function getHardSkills(): Collection
    {
        return $this->hardSkills;
    }

    public function addHardSkill(HardSkill $hardSkill): static
    {
        if (!$this->hardSkills->contains($hardSkill)) {
            $this->hardSkills->add($hardSkill);
        }

        return $this;
    }

    public function removeHardSkill(HardSkill $hardSkill): static
    {
        $this->hardSkills->removeElement($hardSkill);

        return $this;
    }

    /**
     * @return Collection<int, SoftSkill>
     */
    public function getSoftSkills(): Collection
    {
        return $this->softSkills;
    }

    public function addSoftSkill(SoftSkill $softSkill): static
    {
        if (!$this->softSkills->contains($softSkill)) {
            $this->softSkills->add($softSkill);
        }

        return $this;
    }

    public function removeSoftSkill(SoftSkill $softSkill): static
    {
        $this->softSkills->removeElement($softSkill);

        return $this;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        $this->languages->removeElement($language);

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setProvider($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getProvider() === $this) {
                $service->setProvider(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setProvider($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getProvider() === $this) {
                $article->setProvider(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AvailabilitySlot>
     */
    public function getAvailabilitySlots(): Collection
    {
        return $this->availabilitySlots;
    }

    public function addAvailabilitySlot(AvailabilitySlot $availabilitySlot): static
    {
        if (!$this->availabilitySlots->contains($availabilitySlot)) {
            $this->availabilitySlots->add($availabilitySlot);
            $availabilitySlot->setProvider($this);
        }

        return $this;
    }

    public function removeAvailabilitySlot(AvailabilitySlot $availabilitySlot): static
    {
        if ($this->availabilitySlots->removeElement($availabilitySlot)) {
            // set the owning side to null (unless already changed)
            if ($availabilitySlot->getProvider() === $this) {
                $availabilitySlot->setProvider(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SocialLink>
     */
    public function getSocialLinks(): Collection
    {
        return $this->socialLinks;
    }

    public function addSocialLink(SocialLink $socialLink): static
    {
        if (!$this->socialLinks->contains($socialLink)) {
            $this->socialLinks->add($socialLink);
            $socialLink->setProvider($this);
        }

        return $this;
    }

    public function removeSocialLink(SocialLink $socialLink): static
    {
        if ($this->socialLinks->removeElement($socialLink)) {
            // set the owning side to null (unless already changed)
            if ($socialLink->getProvider() === $this) {
                $socialLink->setProvider(null);
            }
        }

        return $this;
    }


    #[Groups(['provider:read'])]
    public function getRole(): string
    {
        return 'provider';
    }

    public function getRoles(): array
    {
        return ['ROLE_PROVIDER'];
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données sensibles temporaires, les effacer ici
    }
}
