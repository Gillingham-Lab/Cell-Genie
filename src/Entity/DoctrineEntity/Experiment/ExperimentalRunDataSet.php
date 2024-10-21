<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\Experiment\ExperimentalRunDataSetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentalRunDataSetRepository::class)]
#[ORM\Table("new_experimental_run_data_set")]
class ExperimentalRunDataSet
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'dataSets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank()]
    private ?ExperimentalRun $experiment = null;

    #[ORM\ManyToOne(targetEntity: ExperimentalRunCondition::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Assert\NotBlank()]
    private ?ExperimentalRunCondition $condition = null;

    #[ORM\ManyToOne(targetEntity: ExperimentalRunCondition::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private ?ExperimentalRunCondition $controlCondition = null;

    /**
     * @var Collection<string, ExperimentalDatum>
     */
    #[ORM\ManyToMany(targetEntity: ExperimentalDatum::class, cascade: ["persist", "remove"], indexBy: "name")]
    #[ORM\JoinTable("new_experimental_run_data_set_datum")]
    #[ORM\JoinColumn("data_set_id", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn("datum_id", onDelete: "CASCADE")]
    private Collection $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
    }

    public function getExperiment(): ?ExperimentalRun
    {
        return $this->experiment;
    }

    public function setExperiment(?ExperimentalRun $experiment): static
    {
        $this->experiment = $experiment;
        $experiment?->addDataSet($this);

        return $this;
    }

    /**
     * @return Collection<string, ExperimentalDatum>
     */
    public function getData(): Collection
    {
        return $this->data;
    }

    public function getDatum(string $name): ExperimentalDatum
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

    public function getCondition(): ?ExperimentalRunCondition
    {
        return $this->condition;
    }

    public function setCondition(?ExperimentalRunCondition $condition): static
    {
        $this->condition = $condition;
        return $this;
    }

    public function getControlCondition(): ?ExperimentalRunCondition
    {
        return $this->controlCondition;
    }

    public function setControlCondition(?ExperimentalRunCondition $controlCondition): static
    {
        $this->controlCondition = $controlCondition;
        return $this;
    }
}
