<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\Collections\HasAttachmentsTrait;
use App\Entity\Traits\Collections\HasLogsTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\NameTrait;
use App\Entity\Traits\Privacy\GroupOwnerTrait;
use App\Entity\Traits\Privacy\OwnerTrait;
use App\Entity\Traits\Privacy\PrivacyLevelTrait;
use App\Genie\Enums\InstrumentRole;
use App\Repository\Instrument\InstrumentRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstrumentRepository::class)]
#[ORM\UniqueConstraint(fields: ["instrumentNumber"])]
#[ORM\UniqueConstraint(fields: ["group", "shortName"])]
#[UniqueEntity(fields: ["instrumentNumber"])]
#[UniqueEntity(fields: ["group", "shortName"])]
#[Gedmo\Loggable]
#[Assert\Expression(
    "!(this.isModular() and this.isCollective())",
    message: 'A instrument can either be modular or collective, but not both.',
)]
#[Assert\Expression(
    "this.getChildren().count() === 0 or (this.getChildren().count() > 0 and (this.isModular() or this.isCollective()))",
    message: 'A instrument can only have children if its modular or a collective entry.',
)]
#[Assert\Expression(
    "!(this.getParent() and this.getChildren().count() > 0)",
    message: 'A instrument can either have children, or be a parent, but not both.',
)]
class Instrument implements PrivacyAwareInterface
{
    use IdTrait;
    use HasAttachmentsTrait;
    use NameTrait;
    use GroupOwnerTrait;
    use OwnerTrait;
    use PrivacyLevelTrait;
    use HasLogsTrait;

    #[ORM\Column(length: 20)]
    #[Assert\NotNull]
    #[Assert\Length(min: 2, max: 20)]
    private ?string $instrumentNumber = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    private ?string $modelNumber = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    private ?string $serialNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registrationNumber;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    private ?string $instrumentContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $requiresTraining = false;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $requiresReservation = false;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $modular = false;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $collective = false;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    private ?string $calendarId = null;

    #[ORM\ManyToOne(targetEntity: Instrument::class, cascade: ["persist", "remove"], inversedBy: "children")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Instrument $parent = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    /** @var Collection<int, Instrument> */
    #[ORM\OneToMany(mappedBy: "parent", targetEntity: Instrument::class)]
    #[ORM\OrderBy(["instrumentNumber" => "ASC"])]
    private Collection $children;

    /** @var Collection<int, InstrumentUser> */
    #[ORM\OneToMany(mappedBy: "instrument", targetEntity: InstrumentUser::class, cascade: ["persist", "remove"])]
    #[Assert\Valid]
    private Collection $users;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $lastMaintenance = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $acquiredOn = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $authString = null;

    #[ORM\Column(type: "float", options: ["default" => 1])]
    #[Assert\NotBlank]
    #[Assert\Range(minMessage: "Minimum reservation time should be 6 minutes; machines that are quicker should not need reservation.", min: 0.1)]
    private ?float $defaultReservationLength = 1;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $citationText = null;

    /** @var Collection<int, Consumable> */
    #[ORM\ManyToMany(targetEntity: Consumable::class, inversedBy: 'instruments')]
    private Collection $consumables;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->consumables = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "{$this->instrumentNumber} {$this->shortName}";
    }

    public function getInstrumentNumber(): ?string
    {
        return $this->instrumentNumber;
    }

    public function setInstrumentNumber(?string $instrumentNumber): self
    {
        $this->instrumentNumber = $instrumentNumber;
        return $this;
    }

    public function getModelNumber(): ?string
    {
        return $this->modelNumber;
    }

    public function setModelNumber(?string $modelNumber): self
    {
        $this->modelNumber = $modelNumber;
        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): self
    {
        $this->registrationNumber = $registrationNumber;
        return $this;
    }

    public function getInstrumentContact(): ?string
    {
        return $this->instrumentContact;
    }

    public function setInstrumentContact(?string $instrumentContact): self
    {
        $this->instrumentContact = $instrumentContact;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getRequiresTraining(): bool
    {
        return $this->requiresTraining === true;
    }

    public function setRequiresTraining(bool $requiresTraining): self
    {
        $this->requiresTraining = $requiresTraining;
        return $this;
    }

    public function isModular(): bool
    {
        return $this->modular === true;
    }

    public function setModular(bool $modular): self
    {
        $this->modular = $modular;
        return $this;
    }

    public function isCollective(): bool
    {
        return $this->collective === true;
    }

    public function setCollective(bool $collective): self
    {
        $this->collective = $collective;
        return $this;
    }

    public function getParent(): ?Instrument
    {
        return $this->parent;
    }

    public function setParent(?Instrument $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Instrument>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Instrument $child): self
    {
        if (!$this->children->contains($child) and $child !== $this) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Instrument $child): self
    {
        if ($this->children->removeElement($child)) {
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, InstrumentUser>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUserRole(User $user, InstrumentRole $role): self
    {
        $found = false;

        /** @var InstrumentUser $instrumentUser */
        foreach ($this->users as $instrumentUser) {
            if ($instrumentUser->getUser() === $user) {
                $instrumentUser->setRole($role);
                $found = true;
            }
        }

        if (!$found) {
            $instrumentUser = new InstrumentUser();
            $instrumentUser->setInstrument($this);
            $instrumentUser->setUser($user);
            $instrumentUser->setRole($role);

            $this->addUser($instrumentUser);
        }

        return $this;
    }

    public function addUser(InstrumentUser $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setInstrument($this);
        }

        return $this;
    }

    public function removeUser(InstrumentUser $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->setInstrument(null);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isRequiresReservation(): bool
    {
        return $this->requiresReservation;
    }

    public function setRequiresReservation(bool $requiresReservation): self
    {
        $this->requiresReservation = $requiresReservation;
        return $this;
    }

    public function getCalendarId(): ?string
    {
        return $this->calendarId;
    }

    public function setCalendarId(?string $calendarId): self
    {
        $this->calendarId = $calendarId;
        return $this;
    }

    public function getLastMaintenance(): ?DateTimeInterface
    {
        return $this->lastMaintenance;
    }

    public function setLastMaintenance(?DateTimeInterface $lastMaintenance): self
    {
        $this->lastMaintenance = $lastMaintenance;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getAcquiredOn(): ?DateTimeInterface
    {
        return $this->acquiredOn;
    }

    public function setAcquiredOn(?DateTimeInterface $acquiredOn): self
    {
        $this->acquiredOn = $acquiredOn;
        return $this;
    }

    public function isBookable(): bool
    {
        if ($this->authString !== null and json_decode($this->authString, true) and $this->getCalendarId()) {
            return true;
        } else {
            return false;
        }
    }

    public function getAuthString(): ?string
    {
        return $this->authString;
    }

    public function setAuthString(?string $authString): self
    {
        $this->authString = $authString;
        return $this;
    }

    public function getDefaultReservationLength(): ?float
    {
        return $this->defaultReservationLength;
    }

    public function setDefaultReservationLength(?float $defaultReservationLength): self
    {
        $this->defaultReservationLength = $defaultReservationLength;
        return $this;
    }

    public function getCitationText(): ?string
    {
        return $this->citationText;
    }

    public function setCitationText(?string $citationText): self
    {
        $this->citationText = $citationText;
        return $this;
    }

    /**
     * @return Collection<int, Consumable>
     */
    public function getConsumables(): Collection
    {
        return $this->consumables;
    }

    public function addConsumable(Consumable $consumable): static
    {
        if (!$this->consumables->contains($consumable)) {
            $this->consumables->add($consumable);
        }

        return $this;
    }

    public function removeConsumable(Consumable $consumable): static
    {
        $this->consumables->removeElement($consumable);

        return $this;
    }
}