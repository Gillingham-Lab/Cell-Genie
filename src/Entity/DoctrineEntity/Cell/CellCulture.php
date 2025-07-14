<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\NumberTrait;
use App\Entity\Traits\Privacy\GroupOwnerTrait;
use App\Entity\Traits\Privacy\PrivacyLevelTrait;
use App\Repository\Cell\CellCultureRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CellCultureRepository::class)]
class CellCulture implements PrivacyAwareInterface
{
    use IdTrait;
    use NumberTrait;
    use GroupOwnerTrait;
    use PrivacyLevelTrait;

    #[Groups([
        "twig",
    ])]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cellCultures')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: CellAliquot::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?CellAliquot $aliquot = null;

    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'date')]
    private DateTimeInterface $unfrozenOn;

    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $trashedOn;

    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $incubator = null;

    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $flask = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subCellCultures')]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?CellCulture $parentCellCulture = null;

    /** @var Collection<int, self>  */
    #[ORM\OneToMany(mappedBy: 'parentCellCulture', targetEntity: self::class)]
    private Collection $subCellCultures;

    /** @var Collection<int, CellCultureEvent>  */
    #[Groups([
        "twig",
    ])]
    #[ORM\OneToMany(mappedBy: 'cellCulture', targetEntity: CellCultureEvent::class, fetch: "EAGER", orphanRemoval: true)]
    #[ORM\OrderBy(["date" => "ASC"])]
    private Collection $events;

    public function __toString(): string
    {
        if ($this->trashedOn) {
            return ($this->getName()) . " ({$this->unfrozenOn->format('d. m. Y')}*, {$this->trashedOn->format('d. m. Y')}†)";
        } else {
            return ($this->getName()) . " ({$this->unfrozenOn->format('d. m. Y')}*)";
        }
    }

    public function __construct()
    {
        $this->unfrozenOn = new DateTime("now");
        $this->subCellCultures = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    #[Groups([
        "twig",
    ])]
    public function getName(): string
    {
        if ($this->aliquot) {
            return "{$this->getNumber()} ({$this->aliquot->getCell()})";
        } else {
            if ($this->parentCellCulture) {
                $parentNumber = strlen($this->parentCellCulture->getNumber());
                $parentName = substr($this->parentCellCulture->getName(), $parentNumber+2, -1);
                return "{$this->getNumber()} ({$parentName})";
            } else {
                return $this->getNumber();
            }
        }
    }

    #[Groups([
        "twig",
    ])]
    public function getMycoplasmaStatus(): ?string
    {
        $status = "unclear";
        foreach ($this->events as $event) {
            if (!$event instanceof CellCultureTestEvent) {
                continue;
            }

            $status = $event->getResult();
        }

        return $status;
    }

    #[Groups([
        "twig",
    ])]
    public function getStartPassage(): int
    {
        return $this->getCurrentPassage($this->unfrozenOn);
    }

    #[Groups([
        "twig",
    ])]
    public function getEndPassage(): ?int
    {
        if (is_null($this->trashedOn)){
            return null;
        }

        return $this->getCurrentPassage($this->getTrashedOn());
    }

    #[Groups([
        "twig",
    ])]
    public function getCurrentPassage(?DateTimeInterface $dateTime = null): int
    {
        if ($this->aliquot) {
            $currentPassage = $this->aliquot->getPassage() ?? 0;
        } else {
            $currentPassage = $this->parentCellCulture?->getCurrentPassage($this->unfrozenOn) ?? 0;
        }

        foreach ($this->events as $event) {
            if (!$event instanceof CellCultureSplittingEvent) {
                continue;
            }

            if ($event->getDate() > $dateTime) {
                continue;
            }

            $currentPassage++;
        }

        return $currentPassage;
    }

    #[Groups([
        "twig",
    ])]
    public function isAbandoned(): bool
    {
        // Trashed cells cannot be abandoned
        if ($this->getTrashedOn()) {
            return false;
        }

        $currentDate = new DateTime();
        /** @var DateTimeInterface $lastChange */
        $lastChange = $this->getUnfrozenOn();

        foreach ($this->getEvents() as $event) {
            if ($event->getDate() > $lastChange) {
                $lastChange = $event->getDate();
            }
        }

        return ($lastChange->diff($currentDate))->days >= 7;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getAliquot(): ?CellAliquot
    {
        return $this->aliquot;
    }

    public function setAliquot(?CellAliquot $aliquot): self
    {
        $this->aliquot = $aliquot;

        return $this;
    }

    public function getUnfrozenOn(): ?DateTimeInterface
    {
        return $this->unfrozenOn;
    }

    public function setUnfrozenOn(DateTimeInterface $unfrozenOn): self
    {
        $this->unfrozenOn = $unfrozenOn;

        return $this;
    }

    public function getTrashedOn(): ?DateTimeInterface
    {
        return $this->trashedOn;
    }

    public function setTrashedOn(?DateTimeInterface $trashedOn): self
    {
        $this->trashedOn = $trashedOn;

        return $this;
    }

    public function getIncubator(): ?string
    {
        return $this->incubator;
    }

    public function setIncubator(string $incubator): self
    {
        $this->incubator = $incubator;

        return $this;
    }

    public function getFlask(): ?string
    {
        return $this->flask;
    }

    public function setFlask(string $flask): self
    {
        $this->flask = $flask;

        return $this;
    }

    public function getParentCellCulture(): ?self
    {
        return $this->parentCellCulture;
    }

    public function setParentCellCulture(?self $parentCellCulture): self
    {
        if ($parentCellCulture !== $this) {
            $this->parentCellCulture = $parentCellCulture;
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubCellCultures(): Collection
    {
        return $this->subCellCultures;
    }

    public function addSubCellCulture(self $subCellCulture): self
    {
        if (!$this->subCellCultures->contains($subCellCulture)) {
            $this->subCellCultures[] = $subCellCulture;
            $subCellCulture->setParentCellCulture($this);
        }

        return $this;
    }

    public function removeSubCellCulture(self $subCellCulture): self
    {
        if ($this->subCellCultures->removeElement($subCellCulture)) {
            // set the owning side to null (unless already changed)
            if ($subCellCulture->getParentCellCulture() === $this) {
                $subCellCulture->setParentCellCulture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CellCultureEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(CellCultureEvent $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setCellCulture($this);
        }

        return $this;
    }

    public function removeEvent(CellCultureEvent $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getCellCulture() === $this) {
                $event->setCellCulture(null);
            }
        }

        return $this;
    }
}
