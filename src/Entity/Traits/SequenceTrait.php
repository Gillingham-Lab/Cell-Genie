<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use App\Genie\SequenceIterator;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait SequenceTrait
{
    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $sequence = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Gedmo\Versioned]
    private ?int $sequenceLength = null;

    public function getSequence(): ?string
    {
        return $this->sequence;
    }

    public function setSequence(?string $sequence): self
    {
        $this->sequence = $sequence;

        if ($sequence === null) {
            $this->sequenceLength = 0;
        } elseif (strlen($sequence) === 0) {
            $this->sequenceLength = 0;
        } else {
            // Also set sequence length
            $i = 0;
            foreach (new SequenceIterator($sequence) as $sequenceItem) {
                $i++;
            }
            $this->sequenceLength = $i;
        }

        return $this;
    }

    public function getFastaSequence(): ?string
    {
        return $this->sequence;
    }

    public function getSequenceLength(): ?int
    {
        return $this->sequenceLength;
    }
}