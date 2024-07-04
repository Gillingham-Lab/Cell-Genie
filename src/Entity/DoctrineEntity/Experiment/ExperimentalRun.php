<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Entity\Traits\TimestampTrait;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Experiment\ExperimentalRunRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentalRunRepository::class)]
#[ORM\Table("new_experimental_run")]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable()]
class ExperimentalRun implements PrivacyAwareInterface
{
    use IdTrait;
    use TimestampTrait;
    use LabJournalTrait;
    use CommentTrait;
    use PrivacyAwareTrait;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?ExperimentalDesign $design = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $scientist = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'experimentalRun', targetEntity: ExperimentalRunCondition::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $conditions;

    #[ORM\OneToMany(mappedBy: 'experiment', targetEntity: ExperimentalRunDataSet::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $dataSets;

    /**
     * @var Collection<string, ExperimentalDatum>
     */
    #[ORM\ManyToMany(targetEntity: ExperimentalDatum::class, cascade: ["persist", "remove"], indexBy: "name")]
    #[ORM\JoinTable("new_experimental_run_datum")]
    #[ORM\JoinColumn("experiment_id", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn("datum_id", onDelete: "CASCADE")]
    private Collection $data;

    #[ORM\Column(type: "smallint", nullable: false, enumType: PrivacyLevel::class, options: ["default" => PrivacyLevel::Group])]
    #[Assert\NotBlank]
    private PrivacyLevel $privacyLevel = PrivacyLevel::Group;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
        $this->dataSets = new ArrayCollection();
        $this->data = new ArrayCollection();
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

    /**
     * @return Collection<string, ExperimentalDatum>
     */
    public function getData(): Collection
    {
        return $this->data;
    }

    public function getDatum(string $name)
    {
        if (!$this->data->containsKey($name)) {
            throw new InvalidArgumentException("Datum with key {$name} does not exist in this collection.");
        }

        return $this->data[$name];
    }

    public function addData(ExperimentalDatum $data): static
    {
        $this->data[$data->getName()] = $data;

        return $this;
    }

    public function removeData(ExperimentalDatum $data): static
    {
        $this->data->remove($data->getName());

        return $this;
    }

    public function getDesign(): ?ExperimentalDesign
    {
        return $this->design;
    }

    public function setDesign(?ExperimentalDesign $design): static
    {
        if ($design === null) {
            if ($this->design !== null) {
                $this->design->removeRun($this);
            }
        } else {
            if ($this->design !== null) {
                $this->design->removeRun($this);
            }

            $design->addRun($this);
        }

        $this->design = $design;

        return $this;
    }
}
