<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\TimestampTrait;
use App\Repository\DoctrineEntity\Experiment\ExperimentalRunRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ExperimentalRunRepository::class)]
#[ORM\Table("new_experimental_run")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable()]
class ExperimentalRun
{
    use IdTrait;
    use TimestampTrait;
    use LabJournalTrait;
    use CommentTrait;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $scientist = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'experimentalRun', targetEntity: ExperimentalRunCondition::class, orphanRemoval: true)]
    private Collection $conditions;

    #[ORM\OneToMany(mappedBy: 'experiment', targetEntity: ExperimentalRunDataSet::class, orphanRemoval: true)]
    private Collection $dataSets;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
        $this->dataSets = new ArrayCollection();
    }

    public function getScientist(): ?User
    {
        return $this->scientist;
    }

    public function setScientist(?User $scientist): static
    {
        $this->scientist = $scientist;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ExperimentalRunCondition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(ExperimentalRunCondition $condition): static
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
            $condition->setExperimentalRun($this);
        }

        return $this;
    }

    public function removeCondition(ExperimentalRunCondition $condition): static
    {
        if ($this->conditions->removeElement($condition)) {
            // set the owning side to null (unless already changed)
            if ($condition->getExperimentalRun() === $this) {
                $condition->setExperimentalRun(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExperimentalRunDataSet>
     */
    public function getDataSets(): Collection
    {
        return $this->dataSets;
    }

    public function addDataSet(ExperimentalRunDataSet $dataSet): static
    {
        if (!$this->dataSets->contains($dataSet)) {
            $this->dataSets->add($dataSet);
            $dataSet->setExperiment($this);
        }

        return $this;
    }

    public function removeDataSet(ExperimentalRunDataSet $dataSet): static
    {
        if ($this->dataSets->removeElement($dataSet)) {
            // set the owning side to null (unless already changed)
            if ($dataSet->getExperiment() === $this) {
                $dataSet->setExperiment(null);
            }
        }

        return $this;
    }
}
