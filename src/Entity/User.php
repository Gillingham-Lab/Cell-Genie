<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "user_accounts")]
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
    private string $email;

    #[ORM\Column(type: "json")]
    private array $roles = [];

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
    private $cellCultures;

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

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
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

    public function setPlainPassword(?string $plainPassword): self
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
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin ?? false;
    }

    public function setIsAdmin(?bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive ?? true;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Experiment>
     */
    public function getExperiments(): Collection
    {
        return $this->experiments;
    }

    public function addExperiment(Experiment $experiment): self
    {
        if (!$this->experiments->contains($experiment)) {
            $this->experiments[] = $experiment;
            $experiment->setOwner($this);
        }

        return $this;
    }

    public function removeExperiment(Experiment $experiment): self
    {
        if ($this->experiments->removeElement($experiment)) {
            // set the owning side to null (unless already changed)
            if ($experiment->getOwner() === $this) {
                $experiment->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CellCulture>
     */
    public function getCellCultures(): Collection
    {
        return $this->cellCultures;
    }

    public function addCellCulture(CellCulture $cellCulture): self
    {
        if (!$this->cellCultures->contains($cellCulture)) {
            $this->cellCultures[] = $cellCulture;
            $cellCulture->setOwner($this);
        }

        return $this;
    }

    public function removeCellCulture(CellCulture $cellCulture): self
    {
        if ($this->cellCultures->removeElement($cellCulture)) {
            // set the owning side to null (unless already changed)
            if ($cellCulture->getOwner() === $this) {
                $cellCulture->setOwner(null);
            }
        }

        return $this;
    }
}
