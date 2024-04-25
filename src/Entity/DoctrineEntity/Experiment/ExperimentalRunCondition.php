<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\DoctrineEntity\Experiment\ExperimentalRunConditionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExperimentalRunConditionRepository::class)]
#[ORM\Table("new_experimental_run_condition")]
class ExperimentalRunCondition
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExperimentalRun $experimentalRun = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $control = null;

    /**
     * @var Collection<string, ExperimentalDatum>
     */
    #[ORM\ManyToMany(targetEntity: ExperimentalDatum::class, indexBy: "name")]
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

    public function setControl(bool $control): static
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

    public function addData(ExperimentalDatum $data): static
    {
        if (!$this->data->contains($data)) {
            $this->data->add($data);
        }

        return $this;
    }

    public function removeData(ExperimentalDatum $data): static
    {
        $this->data->removeElement($data);

        return $this;
    }
}
