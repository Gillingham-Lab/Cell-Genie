<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Enums\DatumEnum;
use App\Repository\Experiment\ExperimentalRunConditionRepository;
use App\Validator\Constraint\UniqueCollectionField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentalRunConditionRepository::class)]
#[ORM\Table("new_experimental_run_condition")]
class ExperimentalRunCondition
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank()]
    private ?ExperimentalRun $experimentalRun = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?bool $control = false;

    /**
     * @var Collection<string, ExperimentalDatum<DatumEnum>>
     */
    #[ORM\ManyToMany(targetEntity: ExperimentalDatum::class, cascade: ["persist", "remove"], orphanRemoval: true, indexBy: "name")]
    #[ORM\JoinTable("new_experimental_run_condition_datum")]
    #[ORM\JoinColumn("condition_id", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn("datum_id", onDelete: "CASCADE")]
    private Collection $data;  // @phpstan-ignore doctrine.associationType

    /** @var Collection<int, ExperimentalModel> */
    #[ORM\ManyToMany(targetEntity: ExperimentalModel::class, cascade: ["persist", "remove"], fetch: "EAGER", orphanRemoval: true)]
    #[ORM\JoinTable(name: "new_experimental_run_condition_model")]
    #[ORM\JoinColumn(name: "condition_id", referencedColumnName: "id", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "model_id", referencedColumnName: "id", unique: true, onDelete: "CASCADE")]
    #[Assert\Valid]
    #[UniqueCollectionField(field: "name")]
    private Collection $models;

    public function __construct()
    {
        $this->data = new ArrayCollection();
        $this->models = new ArrayCollection();
    }

    public function __toString(): string
    {
        $name = $this->name ?? "New Condition";
        return "{$this->experimentalRun->getName()}/{$name}";
    }

    public function __clone(): void
    {
        if ($this->id) {
            $this->id = null;

            $oldData = $this->data;
            $oldModels = $this->models;

            $this->data = new ArrayCollection();
            foreach ($oldData as $datum) {
                $datum = clone $datum;
                $this->data->add($datum);
            }

            $this->models = new ArrayCollection();
            foreach ($oldModels as $model) {
                $model = clone $model;
                $this->models->add($model);
            }
        }
    }

    public function getExperimentalRun(): ?ExperimentalRun
    {
        return $this->experimentalRun;
    }

    public function setExperimentalRun(?ExperimentalRun $experimentalRun): static
    {
        $this->experimentalRun = $experimentalRun;
        $experimentalRun?->addCondition($this);

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

    public function isControl(): ?bool
    {
        return $this->control;
    }

    public function setControl(?bool $control): static
    {
        $this->control = $control;

        return $this;
    }

    /**
     * @return Collection<string, ExperimentalDatum<DatumEnum>>
     */
    public function getData(): Collection
    {
        return $this->data;
    }

    /**
     * @return ExperimentalDatum<DatumEnum>
     */
    public function getDatum(string $name): ExperimentalDatum
    {
        if (!$this->data->containsKey($name)) {
            throw new InvalidArgumentException("Datum with key {$name} does not exist in this collection.");
        }

        return $this->data[$name];
    }

    /**
     * @param ExperimentalDatum<DatumEnum> $data
     */
    public function addData(ExperimentalDatum $data): static
    {
        $this->data[$data->getName()] = $data;

        return $this;
    }

    /**
     * @param ExperimentalDatum<DatumEnum> $data
     */
    public function removeData(ExperimentalDatum $data): static
    {
        $this->data->remove($data->getName());

        return $this;
    }

    /**
     * @return Collection<int, ExperimentalModel>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(ExperimentalModel $model): self
    {
        if (!$this->models->contains($model)) {
            $this->models->add($model);
        }

        return $this;
    }

    public function removeModel(ExperimentalModel $model): self
    {
        if ($this->models->contains($model)) {
            $this->models->removeElement($model);
        }

        return $this;
    }
}
