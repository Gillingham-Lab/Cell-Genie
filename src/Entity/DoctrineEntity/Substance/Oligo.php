<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\MolecularMassTrait;
use App\Genie\SequenceIterator;
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

    #[ORM\Column(type: "text")]
    private ?string $sequence = null;

    #[ORM\Column(type: "integer")]
    private ?int $sequenceLength = null;

    // Must be in 1/(mM*cm)
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $extinctionCoefficient = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getSequence(): ?string
    {
        return $this->sequence;
    }

    public function setSequence(string $sequence): self
    {
        $this->sequence = $sequence;

        // Also set sequence length
        $i = 0;
        foreach (new SequenceIterator($sequence) as $sequenceItem) {
            $i++;
        }
        $this->sequenceLength = $i;

        return $this;
    }

    public function getFastaSequence(): string
    {
        return $this->sequence;
    }

    public function getSequenceLength(): ?int
    {
        return $this->sequenceLength;
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
