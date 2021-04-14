<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChemicalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ChemicalRepository::class)
 */
class Chemical
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $longName = "";

    /**
     * @ORM\Column(type="string", length=10)
     */
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 10,
        minMessage: "Must be at least {{ min }} character long.",
        maxMessage: "Only up to {{ max }} characters allowed.",
    )]
    private string $shortName;

    /**
     * @ORM\Column(type="text")
     */
    private string $smiles = "";

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[Assert\Url]
    private ?string $labjournal = null;

    /**
     * @ORM\ManyToMany(targetEntity=Experiment::class, mappedBy="chemicals")
     */
    private $experiments;

    /**
     * @ORM\ManyToOne(targetEntity=Vendor::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Vendor $vendor = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $vendorPN = null;

    public function __construct()
    {
        $this->experiments = new ArrayCollection();
    }

    #[Pure]
    public function __toString(): string
    {
        return $this->getShortName() ?? "unknown";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLongName(): ?string
    {
        return $this->longName;
    }

    public function setLongName(string $longName): self
    {
        $this->longName = $longName;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
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
     * @return Collection|Experiment[]
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

    public function getVendor(): ?Vendor
    {
        return $this->vendor;
    }

    public function setVendor(?Vendor $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getVendorPN(): ?string
    {
        return $this->vendorPN;
    }

    public function setVendorPN(?string $vendorPN): self
    {
        $this->vendorPN = $vendorPN;

        return $this;
    }
}
