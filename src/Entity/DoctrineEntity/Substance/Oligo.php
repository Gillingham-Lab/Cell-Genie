<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\MolecularMassTrait;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\SequenceTrait;
use App\Repository\Substance\OligoRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: OligoRepository::class)]
#[Gedmo\Loggable]
class Oligo extends Substance
{
    use CommentTrait;
    use MolecularMassTrait;
    use LabJournalTrait;
    use SequenceTrait;

    // Must be in 1/(mM*cm)
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $extinctionCoefficient = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getExtinctionCoefficient(): ?float
    {
        return $this->extinctionCoefficient;
    }

    public function setExtinctionCoefficient(?float $extinctionCoefficient): self
    {
        $this->extinctionCoefficient = $extinctionCoefficient;
        return $this;
    }
}
