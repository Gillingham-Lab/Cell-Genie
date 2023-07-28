<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Experiment;
use App\Entity\Traits\Fields\MolecularMassTrait;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\VendorTrait;
use App\Repository\Substance\ChemicalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToMany(targetEntity: Experiment::class, mappedBy: "chemicals")]
    private Collection $experiments;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $iupacName = null;

    public function __construct()
    {
        parent::__construct();
        $this->experiments = new ArrayCollection();
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

    /**
     * @return Collection<int, Experiment>
     */
    public function getExperiments(): Collection
    {
        return $this->experiments;
    }

    public function addExperiment(Experiment $experiment): self
    {
        if (!$this->experiments->contains($experiment)) {
            $this->experiments[] = $experiment;
            $experiment->addChemical($this);
        }

        return $this;
    }

    public function removeExperiment(Experiment $experiment): self
    {
        if ($this->experiments->removeElement($experiment)) {
            $experiment->removeChemical($this);
        }

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
}
