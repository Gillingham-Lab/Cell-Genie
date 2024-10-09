<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\Experiment\ExperimentalRunConditionRepository;
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
     * @var Collection<string, ExperimentalDatum>
     */
    #[ORM\ManyToMany(targetEntity: ExperimentalDatum::class, cascade: ["persist", "remove"], orphanRemoval: true, indexBy: "name")]
    #[ORM\JoinTable("new_experimental_run_condition_datum")]
    #[ORM\JoinColumn("condition_id", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn("datum_id", onDelete: "CASCADE")]
    private Collection $data;

    public function __construct()
    {
        $this->data = new ArrayCollection();
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
}
