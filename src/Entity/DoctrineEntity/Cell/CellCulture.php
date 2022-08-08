<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\Traits\IdTrait;
use App\Entity\User;
use App\Repository\Cell\CellCultureRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CellCultureRepository::class)]
class CellCulture
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cellCultures')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: CellAliquot::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?CellAliquot $aliquot = null;

    #[ORM\Column(type: 'date')]
    private DateTimeInterface $unfrozenOn;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $trashedOn;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $incubator = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $flask = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subCellCultures')]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?CellCulture $parentCellCulture = null;

    #[ORM\OneToMany(mappedBy: 'parentCellCulture', targetEntity: self::class)]
    private Collection $subCellCultures;

    #[ORM\OneToMany(mappedBy: 'cellCulture', targetEntity: CellCultureEvent::class, fetch: "EAGER", orphanRemoval: true)]
    #[ORM\OrderBy(["date" => "ASC"])]
    private Collection $events;

    public function __toString(): string
    {
        if ($this->trashedOn) {
            return "Culture: " . ($this->aliquot ?? $this->parentCellCulture ?? "unknown") . " ({$this->unfrozenOn->format('d. m. Y')}*, {$this->trashedOn->format('d. m. Y')}†)";
        } else {
            return "Culture: " . ($this->aliquot ?? $this->parentCellCulture ?? "unknown") . " ({$this->unfrozenOn->format('d. m. Y')}*)";
        }
    }

    public function __construct()
    {
        $this->unfrozenOn = new DateTime("now");
        $this->subCellCultures = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getName(): string
    {
        if ($this->aliquot) {
            return (string)$this->aliquot;
        } else {
            if ($this->parentCellCulture) {
                return $this->parentCellCulture->getName();
            } else {
                return "Unknown";
            }
        }
    }

    public function getMycoplasmaStatus()
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

    public function getCurrentPassage(?DateTimeInterface $dateTime = null): int
    {
        if ($this->aliquot) {
            $currentPassage = $this->aliquot->getPassage();
        } else {
            $currentPassage = $this->parentCellCulture->getCurrentPassage($this->unfrozenOn);
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
