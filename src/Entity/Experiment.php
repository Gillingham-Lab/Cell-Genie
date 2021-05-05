<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExperimentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ExperimentRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Experiment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="experiments")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    #[Assert\NotNull]
    private ?User $owner = null;

    /**
     * @ORM\ManyToOne(targetEntity=ExperimentType::class, inversedBy="experiments", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    #[Assert\NotNull]
    private ?ExperimentType $experimentType = null;

    /**
     * @ORM\ManyToOne(targetEntity=CultureFlask::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?CultureFlask $wellplate = null;

    /**
     * @ORM\ManyToMany(targetEntity=Protein::class, inversedBy="experiments")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @var Collection|Protein[]
     */
    private Collection $proteinTargets;

    /**
     * @ORM\ManyToMany(targetEntity=Chemical::class, inversedBy="experiments")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @var Collection|Chemical[]
     */
    private Collection $chemicals;

    /**
     * @ORM\ManyToMany(targetEntity=Cell::class, inversedBy="experiments")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @var Collection|Cell[]
     */
    private Collection $cells;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $lysing = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $seeding = null;

    /**
     * @ORM\OneToMany(targetEntity=AntibodyDilution::class, mappedBy="experiment", cascade={"persist"})
     */
    private Collection $antibodyDilutions;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $modifiedAt = null;

    public function __construct()
    {
        $this->proteinTargets = new ArrayCollection();
        $this->chemicals = new ArrayCollection();
        $this->cells = new ArrayCollection();
        $this->antibodyDilution = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new DateTime("now"));
        }

        $this->setModifiedAt(new DateTime("now"));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getExperimentType(): ?ExperimentType
    {
        return $this->experimentType;
    }

    public function setExperimentType(?ExperimentType $experimentType): self
    {
        $this->experimentType = $experimentType;

        return $this;
    }

    public function getWellplate(): ?CultureFlask
    {
        return $this->wellplate;
    }

    public function setWellplate(?CultureFlask $wellplate): self
    {
        $this->wellplate = $wellplate;

        return $this;
    }

    /**
     * @return Collection|Protein[]
     */
    public function getProteinTargets(): Collection
    {
        return $this->proteinTargets;
    }

    public function addProteinTarget(Protein $proteinTarget): self
    {
        if (!$this->proteinTargets->contains($proteinTarget)) {
            $this->proteinTargets[] = $proteinTarget;
        }

        return $this;
    }

    public function removeProteinTarget(Protein $proteinTarget): self
    {
        $this->proteinTargets->removeElement($proteinTarget);

        return $this;
    }

    /**
     * @return Collection|Chemical[]
     */
    public function getChemicals(): Collection
    {
        return $this->chemicals;
    }

    public function addChemical(Chemical $chemical): self
    {
        if (!$this->chemicals->contains($chemical)) {
            $this->chemicals[] = $chemical;
        }

        return $this;
    }

    public function removeChemical(Chemical $chemical): self
    {
        $this->chemicals->removeElement($chemical);

        return $this;
    }

    /**
     * @return Collection|Cell[]
     */
    public function getCells(): Collection
    {
        return $this->cells;
    }

    public function addCell(Cell $cell): self
    {
        if (!$this->cells->contains($cell)) {
            $this->cells[] = $cell;
        }

        return $this;
    }

    public function removeCell(Cell $cell): self
    {
        $this->cells->removeElement($cell);

        return $this;
    }

    public function getLysing(): ?string
    {
        return $this->lysing ?? $this->experimentType?->getLysing();
    }

    public function setLysing(?string $lysing): self
    {
        $this->lysing = $lysing;

        return $this;
    }

    public function getSeeding(): ?string
    {
        return $this->seeding ?? $this->experimentType?->getSeeding();
    }

    public function setSeeding(?string $seeding): self
    {
        $this->seeding = $seeding;

        return $this;
    }

    /**
     * @return Collection|AntibodyDilution[]
     */
    public function getAntibodyDilutions(): Collection
    {
        return $this->antibodyDilutions;
    }

    public function addAntibodyDilution(AntibodyDilution $antibodyDilution): self
    {
        if (!$this->antibodyDilutions->contains($antibodyDilution)) {
            $this->antibodyDilutions[] = $antibodyDilution;
            $antibodyDilution->setExperiment($this);
        }

        return $this;
    }

    public function removeAntibodyDilution(AntibodyDilution $antibodyDilution): self
    {
        if ($this->antibodyDilutions->removeElement($antibodyDilution)) {
            // set the owning side to null (unless already changed)
            if ($antibodyDilution->getExperiment() === $this) {
                $antibodyDilution->setExperiment(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }
}
