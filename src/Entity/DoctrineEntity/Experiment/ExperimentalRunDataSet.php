<?php

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\DoctrineEntity\Experiment\ExperimentalRunDataSetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExperimentalRunDataSetRepository::class)]
#[ORM\Table("new_experimental_run_data_set")]
class ExperimentalRunDataSet
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'dataSets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExperimentalRun $experiment = null;

    public function getExperiment(): ?ExperimentalRun
    {
        return $this->experiment;
    }

    public function setExperiment(?ExperimentalRun $experiment): static
    {
        $this->experiment = $experiment;

        return $this;
    }
}
