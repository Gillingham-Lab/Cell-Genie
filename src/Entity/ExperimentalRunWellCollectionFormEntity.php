<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\Validator\Constraints as Assert;

#[Deprecated]
class ExperimentalRunWellCollectionFormEntity extends AbstractExperimentalFormEntity
{
    private Experiment $experiment;

    #[Assert\Valid]
    public Collection $wells;

    public function __construct(Experiment $experiment)
    {
        $this->experiment = $experiment;
        $this->wells = new ArrayCollection();
    }

    public function updateFromEntity(ExperimentalRun $experimentalRun)
    {
        /** @var ExperimentalRunWell $well */
        foreach ($experimentalRun->getWells() as $well) {
            $formEntity = new ExperimentalRunWellFormEntity($this->experiment);
            $formEntity->updateFromEntity($well);

            $this->wells->set($well->getId()->toBase58(), $formEntity);
        }
    }

    public function getUpdatedEntity(ExperimentalRun $experimentalRun)
    {
        foreach ($experimentalRun->getWells() as $well) {
            $formEntity = $this->wells->get($well->getId()->toBase58());

            $formEntity->updateEntity($well);
        }
    }
}