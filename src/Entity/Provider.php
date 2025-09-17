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
use Symfony\Component\Serializer\Annotation\SerializedName;
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
            security: "is_granted('ROLE_ADMIN') or object == user"
        ),
        new Patch(
            denormalizationContext: ['groups' => ['provider:patch']],
            security: "is_granted('ROLE_ADMIN') or object == user"
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object == user"
        ),
    ],
    normalizationContext: ['groups' => ['provider:read'], 'iri_only' => false],
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
    #[Assert\Url]
    private ?string $profilePicture = null;

    #[ORM\Column]
    #[Groups(['provider:read'])]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['provider:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'Le slug ne doit contenir que des lettres minuscules, des chiffres et des tirets (-)')]
    private ?string $slug = null;

    #[ORM\ManyToOne(targetEntity: Job::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['provider:write'])]
    #[Assert\NotNull]
    private ?Job $job = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['provider:write'])]
    #[Assert\NotNull]
    private ?Country $country = null;

    #[ORM\Column(length: 255)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\Length(max: 255)]
    private ?string $state = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\Length(max: 20)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['provider:read', 'provider:write'])]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[ORM\ManyToMany(targetEntity: HardSkill::class, mappedBy: 'providers')]
    #[Groups(['provider:read', 'provider:write'])]
    private Collection $hardSkills;

    #[ORM\ManyToMany(targetEntity: SoftSkill::class, mappedBy: 'providers')]
    #[Groups(['provider:read', 'provider:write'])]
    private Collection $softSkills;

    #[ORM\ManyToMany(targetEntity: Language::class, mappedBy: 'providers')]
    #[Groups(['provider:read', 'provider:write'])]
    private Collection $languages;

    public function __construct()
    {
        $this->hardSkills = new ArrayCollection();
        $this->softSkills = new ArrayCollection();
        $this->languages = new ArrayCollection();
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

    #[Groups(['provider:read'])]
    #[SerializedName('job')]
    public function getJobForRead(): ?string
    {
        return $this->job?->getTitle();
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

    #[Groups(['provider:read'])]
    #[SerializedName('country')]
    public function getCountryForRead(): ?string
    {
        return $this->country?->getName();
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
            $hardSkill->addProvider($this);
        }

        return $this;
    }

    public function removeHardSkill(HardSkill $hardSkill): static
    {
        if ($this->hardSkills->removeElement($hardSkill)) {
            $hardSkill->removeProvider($this);
        }

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
            $softSkill->addProvider($this);
        }

        return $this;
    }

    public function removeSoftSkill(SoftSkill $softSkill): static
    {
        if ($this->softSkills->removeElement($softSkill)) {
            $softSkill->removeProvider($this);
        }

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
            $language->addProvider($this);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        if ($this->languages->removeElement($language)) {
            $language->removeProvider($this);
        }

        return $this;
    }

    public function getRole(): string
    {
        return 'provider';
    }

    // Méthodes requises par UserInterface
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
