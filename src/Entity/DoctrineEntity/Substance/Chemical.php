<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\DoctrineEntity\Lot;
use App\Entity\Traits\Fields\MolecularMassTrait;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\VendorTrait;
use App\Repository\Substance\ChemicalRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChemicalRepository::class)]
#[Gedmo\Loggable]
class Chemical extends Substance
{
    use VendorTrait;
    use LabJournalTrait;
    use MolecularMassTrait;

    #[ORM\Column(type: "text")]
    private string $smiles = "";

    #[ORM\Column(type: "float", nullable: true)]
    #[Assert\Range(min: 0)]
    private ?float $density = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $casNumber = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $iupacName = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return $this->getShortName() ?? "unknown";
    }

    public function getSmiles(): ?string
    {
        return $this->smiles;
    }

    public function setSmiles(string $smiles): self
    {
        $this->smiles = $smiles;

        return $this;
    }

    public function getDensity(): ?float
    {
        return $this->density;
    }

    public function setDensity(?float $density): self
    {
        $this->density = $density;

        return $this;
    }

    public function getCasNumber(): ?string
    {
        return $this->casNumber;
    }

    public function setCasNumber(?string $casNumber): self
    {
        $this->casNumber = $casNumber;

        return $this;
    }

    public function getIupacName(): ?string
    {
        return $this->iupacName;
    }

    public function setIupacName(?string $iupacName): self
    {
        $this->iupacName = $iupacName;

        return $this;
    }

    public function getCitation(?Lot $lot = null): string
    {
        $options = [];

        if ($this->casNumber) {
            $options[] = "[$this->casNumber]";
        }

        if ($lot) {
            if ($lot->getVendor()) {
                $options[] = $this->getVendor();
            }

            if ($lot->getVendorPn()) {
                $options[] = $this->getVendorPn();
            }
        }

        if (count($options)) {
            $options = implode(", ", $options);
            $options = " ($options)";
        } else {
            $options = "";
        }

        return $this->getLongName() . $options;
    }
}
