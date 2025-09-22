<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use App\Entity\DoctrineEntity\File\File;
use Doctrine\ORM\Mapping as ORM;

trait VisualisationTrait
{
    #[ORM\ManyToOne(targetEntity: File::class, cascade: ["persist", "remove"], fetch: "EAGER")]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?File $visualisation = null;

    public function getVisualisation(): ?File
    {
        return $this->visualisation;
    }

    public function setVisualisation(?File $visualisation): self
    {
        $this->visualisation = $visualisation;
        return $this;
    }
}
