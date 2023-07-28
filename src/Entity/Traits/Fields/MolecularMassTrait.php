<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait MolecularMassTrait
{
    #[ORM\Column(type: "float", nullable: false, options: ["default" => 0.0])]
    #[Assert\Range(min: 0)]
    #[Assert\NotNull]
    #[Gedmo\Versioned]
    private float $molecularMass = 0.0;

    public function getMolecularMass(): float
    {
        return $this->molecularMass;
    }

    public function setMolecularMass(?float $molecularMass): self
    {
        $this->molecularMass = $molecularMass ?? 0.0;
        return $this;
    }
}