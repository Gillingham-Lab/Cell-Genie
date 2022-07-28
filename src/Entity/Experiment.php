<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\OwnerTrait;
use App\Entity\Traits\TimestampTrait;
use App\Repository\ExperimentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ["name", "experimentType"])]
class Experiment
{
    use IdTrait;
    use TimestampTrait;
    use OwnerTrait;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: ExperimentType::class, fetch: "LAZY", inversedBy: "experiments")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Assert\NotNull]
    private ?ExperimentType $experimentType = null;

    #[ORM\OneToMany(mappedBy: "experiment", targetEntity: ExperimentalCondition::class, cascade: ["persist"], fetch: "LAZY")]
    #[ORM\OrderBy(["order" =>"ASC"])]
    #[Assert\Valid]
    private Collection $conditions;

    #[ORM\OneToMany(mappedBy: "experiment", targetEntity: ExperimentalMeasurement::class, cascade: ["persist"], fetch: "LAZY")]
    #[ORM\OrderBy(["order" =>"ASC"])]
    #[Assert\Valid]
    private Collection $measurements;

    #[ORM\ManyToOne(targetEntity: CultureFlask::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?CultureFlask $wellplate = null;

    #[ORM\ManyToMany(targetEntity: Protein::class, inversedBy: "experiments")]
    #[ORM\JoinColumn(name: "experiment_id", nullable: true, onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "protein_ulid", referencedColumnName: "ulid")]
    private Collection $proteinTargets;

    #[ORM\ManyToMany(targetEntity: Chemical::class, inversedBy: "experiments")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(referencedColumnName: "ulid")]
    private Collection $chemicals;

    #[ORM\ManyToMany(targetEntity: Cell::class, inversedBy: "experiments")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private Collection $cells;

    #[ORM\OneToMany(mappedBy: "experiment", targetEntity: AntibodyDilution::class, cascade: ["persist"])]
    private Collection $antibodyDilutions;

    #[ORM\Column(type: "integer", nullable: false, options: ["default" => 1])]
    #[Assert\Range(min: 1, max: 32000)]
    private ?int $numberOfWells = 1;

    #[ORM\OneToMany(mappedBy: "experiment", targetEntity: ExperimentalRun::class, cascade: ["persist"], fetch: "EXTRA_LAZY")]
    #[ORM\OrderBy(["createdAt" => "DESC"])]
    private Collection $experimentalRuns;

    public function __construct()
    {
        $this->proteinTargets = new ArrayCollection();
        $this->chemicals = new ArrayCollection();
        $this->cells = new ArrayCollection();
        $this->antibodyDilutions = new ArrayCollection();
        $this->conditions = new ArrayCollection();
        $this->measurements = new ArrayCollection();
        $this->experimentalRuns = new ArrayCollection();
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
     * @return Collection<int, Protein>
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
     * @return Collection<int, Chemical>
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
     * @return Collection<int, Cell>
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

    /**
     * @return Collection<int, AntibodyDilution>
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

    /**
     * @return Collection<int, ExperimentalCondition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(ExperimentalCondition $condition): self
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions[] = $condition;
            $condition->setExperiment($this);
        }

        return $this;
    }

    public function removeCondition(ExperimentalCondition $condition): self
    {
        if ($this->conditions->removeElement($condition)) {
            // set the owning side to null (unless already changed)
            if ($condition->getExperiment() === $this) {
                $condition->setExperiment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExperimentalMeasurement>
     */
    public function getMeasurements(): Collection
    {
        return $this->measurements;
    }

    public function addMeasurement(ExperimentalMeasurement $measurement): self
    {
        if (!$this->measurements->contains($measurement)) {
            $this->measurements[] = $measurement;
            $measurement->setExperiment($this);
        }

        return $this;
    }

    public function removeMeasurement(ExperimentalMeasurement $measurement): self
    {
        if ($this->measurements->removeElement($measurement)) {
            // set the owning side to null (unless already changed)
            if ($measurement->getExperiment() === $this) {
                $measurement->setExperiment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExperimentalRun>
     */
    public function getExperimentalRuns(): Collection
    {
        return $this->experimentalRuns;
    }

    public function addExperimentalRun(ExperimentalRun $experimentalRun): self
    {
        if (!$this->experimentalRuns->contains($experimentalRun)) {
            $this->experimentalRuns[] = $experimentalRun;
            $experimentalRun->setExperiment($this);
        }

        return $this;
    }

    public function removeExperimentalRun(ExperimentalRun $experimentalRun): self
    {
        if ($this->experimentalRuns->removeElement($experimentalRun)) {
            // set the owning side to null (unless already changed)
            if ($experimentalRun->getExperiment() === $this) {
                $experimentalRun->setExperiment(null);
            }
        }

        return $this;
    }

    public function getNumberOfWells(): ?int
    {
        return $this->numberOfWells;
    }

    public function setNumberOfWells(int $numberOfWells): self
    {
        $this->numberOfWells = $numberOfWells;

        return $this;
    }
}
