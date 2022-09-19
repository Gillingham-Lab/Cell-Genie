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

    // Must be μM
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $concentration = null;

    // Must be in nmol
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $amountOrdered = null;

    // Must be in nmol.
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $amountLeft = null;

    // Must be in 1/(mM*cm)
    #[ORM\Column(type: "float", nullable: true)]
    private ?float $extinctionCoefficient = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $purification = null;

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

    public function getConcentration(): ?float
    {
        return $this->concentration;
    }

    public function setConcentration(?float $concentration): self
    {
        $this->concentration = $concentration;
        return $this;
    }

    public function getAmountOrdered(): ?float
    {
        return $this->amountOrdered;
    }

    public function setAmountOrdered(?float $amountOrdered): self
    {
        $this->amountOrdered = $amountOrdered;
        if (empty($this->amountLeft)) {
            $this->amountLeft = $amountOrdered;
        }
        return $this;
    }

    public function getAmountLeft(): ?float
    {
        return $this->amountLeft;
    }

    public function setAmountLeft(?float $amountLeft): self
    {
        $this->amountLeft = $amountLeft;
        return $this;
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

    public function getPurification(): ?string
    {
        return $this->purification;
    }

    public function setPurification(?string $purification): self
    {
        $this->purification = $purification;
        return $this;
    }
}
