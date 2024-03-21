<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\User;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\Experiment;
use App\Repository\Substance\UserRepository;
use App\Service\Doctrine\Generator\UlidGenerator;
use App\Service\Doctrine\Type\Ulid;
use App\Validator\Constraint\OrcId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user_accounts")]
#[UniqueEntity(fields: ["fullName"], message: "This name is already in use.")]
#[UniqueEntity(fields: ["email"], message: "This email address is already in use.")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "ulid", unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private ?string $fullName = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Assert\NotBlank]
    private string $email;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $personalAddress = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $suffix = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\NotBlank]
    private ?string $office = null;

    #[ORM\Column(length: 19, nullable: true)]
    #[OrcId]
    private ?string $orcid = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: "string")]
    private string $password;

    /**
     * @var string|null temporary field for plain password.
     */
    private ?string $plainPassword = null;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $isAdmin = false;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $isActive = false;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: CellCulture::class)]
    private Collection $cellCultures;

    #[ORM\ManyToOne(UserGroup::class, cascade: ["persist"], inversedBy: "users")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?UserGroup $group = null;

    public function __construct()
    {
        $this->cellCultures = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getFullName() ?? "unknown";
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

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

    /**
     * @deprecated
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * Public representation of the user
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        if ($this->getIsAdmin()) {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function hasPlainPassword(): bool
    {
        return $this->plainPassword !== null;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin ?? false;
    }

    public function setIsAdmin(?bool $isAdmin): static
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive ?? true;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, CellCulture>
     */
    public function getCellCultures(): Collection
    {
        return $this->cellCultures;
    }

    public function addCellCulture(CellCulture $cellCulture): static
    {
        if (!$this->cellCultures->contains($cellCulture)) {
            $this->cellCultures[] = $cellCulture;
            $cellCulture->setOwner($this);
        }

        return $this;
    }

    public function removeCellCulture(CellCulture $cellCulture): static
    {
        if ($this->cellCultures->removeElement($cellCulture)) {
            // set the owning side to null (unless already changed)
            if ($cellCulture->getOwner() === $this) {
                $cellCulture->setOwner(null);
            }
        }

        return $this;
    }

    public function getGroup(): ?UserGroup
    {
        return $this->group;
    }

    public function setGroup(?UserGroup $group): static
    {
        if ($group === null and $this->group !== null) {
            $this->group->removeUser($this);
            $this->group = null;
        } elseif ($this->group !== null and $group !== $this->group) {
            $this->group->removeUser($this);
            $group->addUser($this);
            $this->group = $group;
        } else {
            $group->addUser($this);
            $this->group = $group;
        }

        return $this;
    }

    public function getPersonalAddress(): ?string
    {
        return $this->personalAddress;
    }

    public function setPersonalAddress(?string $personalAddress): static
    {
        $this->personalAddress = $personalAddress;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(?string $suffix): static
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getOffice(): ?string
    {
        return $this->office;
    }

    public function setOffice(?string $office): static
    {
        $this->office = $office;
        return $this;
    }

    public function getCompleteName(): string
    {
        $completeName = $this->getFullName();

        if ($this->title) {
            $completeName = "{$this->title} {$completeName}";
        }

        if ($this->suffix) {
            $completeName = "{$completeName}, {$this->suffix}";
        }

        return $completeName;
    }

    public function getOrcid(): ?string
    {
        return $this->orcid;
    }

    public function setOrcid(?string $orcid): static
    {
        $this->orcid = $orcid;
        return $this;
    }
}
