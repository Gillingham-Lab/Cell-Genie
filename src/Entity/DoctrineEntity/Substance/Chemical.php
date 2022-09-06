<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Experiment;
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

    #[ORM\Column(type: "text")]
    private string $smiles = "";

    #[ORM\Column(type: "text", nullable: true)]
    #[Assert\Url]
    private ?string $labjournal = null;

    #[ORM\Column(type: "float", nullable: false, options: ["default" => 0.0])]
    #[Assert\Range(min: 0)]
    #[Assert\NotNull]
    private float $molecularMass = 0.0;

    #[ORM\Column(type: "float", nullable: true)]
    #[Assert\Range(min: 0)]
    private ?float $density = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $casNumber = null;

    #[ORM\ManyToMany(targetEntity: Experiment::class, mappedBy: "chemicals")]
    private Collection $experiments;

    #[ORM\Column(type: 'text', nullable: true)]
    private $iupacName;

    public function __construct()
    {
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

    public function getLabjournal(): ?string
    {
        return $this->labjournal;
    }

    public function setLabjournal(?string $labjournal): self
    {
        $this->labjournal = $labjournal;

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

    public function getMolecularMass(): float
    {
        return $this->molecularMass;
    }

    public function setMolecularMass(float $molecularMass): self
    {
        $this->molecularMass = $molecularMass;

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
