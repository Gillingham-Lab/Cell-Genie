<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use App\Entity\File;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait VisualisationTrait
{
    #[ORM\ManyToOne(targetEntity: File::class, cascade: ["persist", "remove"], fetch: "EAGER", )]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?File $visualisation;

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