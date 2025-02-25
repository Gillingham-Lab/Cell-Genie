<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Privacy\OwnerTrait;
use App\Entity\Traits\TimestampTrait;
use App\Repository\ExperimentalRunRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentalRunRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ["name", "experiment"], message: "This name is already in use for this experiment.")]
#[ORM\UniqueConstraint(fields: ["experiment", "name"])]
#[Deprecated]
class ExperimentalRun
{
    use IdTrait;
    use TimestampTrait;
    use OwnerTrait;

    #[ORM\ManyToOne(targetEntity: Experiment::class, inversedBy: "experimentalRuns")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?Experiment $experiment = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(min: 3, max: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: "integer", nullable: false, options: ["default" => 1])]
    #[Assert\Range(min: 1, max: 32000)]
    private ?int $numberOfWells = 1;

    #[ORM\Column(type: "array", nullable: false, options: ["default" => "a:0:{}"])]
    private array $data = [];

    #[ORM\OneToMany(mappedBy: "experimentalRun", targetEntity: ExperimentalRunWell::class, cascade: ["persist", "remove"])]
    #[ORM\OrderBy(["wellNumber" => "ASC"])]
    private Collection $wells;

    public function __construct()
    {
        $this->wells = new ArrayCollection();
    }

    public function getExperiment(): ?Experiment
    {
        return $this->experiment;
    }

    public function setExperiment(?Experiment $experiment): self
    {
        $this->experiment = $experiment;

        return $this;
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

    public function getNumberOfWells(): int
    {
        return $this->numberOfWells ?? $this->experiment?->getNumberOfWells() ?? 1;
    }

    public function setNumberOfWells(int $numberOfWells): self
    {
        $this->numberOfWells = $numberOfWells;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data = []): self
    {
        $this->data = $data;

        return $this;
    }

    private function getDatum(string $type, string|Ulid $idBase58) {
        if ($idBase58 instanceof Ulid) {
            $idBase58 = $idBase58->toBase58();
        }

        if (!isset($this->data[$type])) {
            return null;
        }

        if (isset($this->data[$type][$idBase58])) {
            return $this->data[$type][$idBase58]["value"];
        } else {
            foreach ($this->data[$type] as $datum) {
                if ($datum["id"] === $idBase58) {
                    return $datum["value"];
                }
            }
        }

        return null;
    }

    public function getConditionDatum(string|Ulid $idBase58): mixed
    {
        return $this->getDatum("conditions", $idBase58);
    }

    /**
     * @return Collection<int, ExperimentalRunWell>
     */
    public function getWells(): Collection
    {
        return $this->wells;
    }

    public function addWell(ExperimentalRunWell $well): self
    {
        if (!$this->wells->contains($well)) {
            $this->wells[] = $well;
            $well->setExperimentalRun($this);
        }

        return $this;
    }

    public function removeWell(ExperimentalRunWell $well): self
    {
        if ($this->wells->removeElement($well)) {
            // set the owning side to null (unless already changed)
            if ($well->getExperimentalRun() === $this) {
                $well->setExperimentalRun(null);
            }
        }

        return $this;
    }
}