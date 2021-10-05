<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasRRID;
use App\Entity\Traits\VendorTrait;
use App\Repository\AntibodyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AntibodyRepository::class)]
#[UniqueEntity(fields: "shortName")]
#[UniqueEntity(fields: "number")]
class Antibody
{
    use VendorTrait;
    use HasRRID;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private string $shortName = "";

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    private string $longName = "";

    #[ORM\ManyToMany(targetEntity: Protein::class, inversedBy: "antibodies")]
    private Collection $proteinTarget;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?String $detection = null;

    #[ORM\OneToMany(mappedBy: "antibody", targetEntity: AntibodyDilution::class, orphanRemoval: true)]
    private Collection $antibodyDilutions;

    #[ORM\Column(type: "string", length: 10, unique: true, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 10)]
    private ?string $number = null;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $validatedInternally = false;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $validatedExternally = false;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $externalReference = null;

    #[ORM\ManyToOne(targetEntity: AntibodyHost::class, inversedBy: "primaries")]
    private ?AntibodyHost $hostOrganism = null;

    #[ORM\ManyToOne(targetEntity: AntibodyHost::class, inversedBy: "secondaries")]
    private ?AntibodyHost $hostTarget = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $dilution = null;

    #[ORM\Column(type: "smallint", nullable: true, options: ["default" => 0])]
    #[Assert\NotBlank]
    #[Assert\Range(
        minMessage: "Storage below -200 °C is not possible.",
        maxMessage: "Storage above 25 °C does not make sense.",
        min: -200,
        max: 25
    )]
    private int $storageTemperature = 0;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 250)]
    private ?string $clonality = "monoclonal";

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 250)]
    private ?string $usage = "Western blot";

    #[ORM\ManyToMany(targetEntity: Lot::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable(name: "antibody_lots")]
    #[ORM\JoinColumn(name: "antibody_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "lot_id", referencedColumnName: "id", unique: true)]
    #[ORM\OrderBy(["lotNumber" => "ASC"])]
    #[Assert\Valid]
    private Collection $lots;

    #[ORM\ManyToMany(targetEntity: File::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable(name: "antibody_vendor_documentation_files")]
    #[ORM\JoinColumn(name: "antibody_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "file_id", referencedColumnName: "id", unique: true)]
    #[Assert\Valid]
    private Collection $vendorDocumentation;

    public function __construct()
    {
        $this->proteinTarget = new ArrayCollection();
        $this->antibodyDilutions = new ArrayCollection();
        $this->lots = new ArrayCollection();
        $this->vendorDocumentation = new ArrayCollection();
    }

    public function __toString(): string
    {
        return ($this->getNumber() ? $this->getNumber() . " | " : "") . ($this->getShortName() ?? "unknown");
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLongName(): ?string
    {
        return $this->longName;
    }

    public function setLongName(string $longName): self
    {
        $this->longName = $longName;

        return $this;
    }

    /**
     * @return Collection<int, Protein>
     */
    public function getProteinTarget(): Collection
    {
        return $this->proteinTarget;
    }

    public function addProteinTarget(Protein $proteinTarget): self
    {
        if (!$this->proteinTarget->contains($proteinTarget)) {
            $this->proteinTarget[] = $proteinTarget;
        }

        return $this;
    }

    public function removeProteinTarget(Protein $proteinTarget): self
    {
        $this->proteinTarget->removeElement($proteinTarget);

        return $this;
    }

    public function getDetection(): ?string
    {
        return $this->detection;
    }

    public function setDetection(?string $detection): self
    {
        $this->detection = $detection;

        return $this;
    }

    /**
     * @return Collection<int, AntibodyDilution>
     */
    public function getAntibodyDilutions(): Collection
    {
        return $this->antibodyDilutions;
    }

    public function addAntibodyDilution(AntibodyDilution $antibodyDilution): self
    {
        if (!$this->antibodyDilutions->contains($antibodyDilution)) {
            $this->antibodyDilutions[] = $antibodyDilution;
            $antibodyDilution->setAntibody($this);
        }

        return $this;
    }

    public function removeAntibodyDilution(AntibodyDilution $antibodyDilution): self
    {
        if ($this->antibodyDilutions->removeElement($antibodyDilution)) {
            // set the owning side to null (unless already changed)
            if ($antibodyDilution->getAntibody() === $this) {
                $antibodyDilution->setAntibody(null);
            }
        }

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getValidatedInternally(): ?bool
    {
        return $this->validatedInternally;
    }

    public function setValidatedInternally(bool $validatedInternally): self
    {
        $this->validatedInternally = $validatedInternally;

        return $this;
    }

    public function getValidatedExternally(): ?bool
    {
        return $this->validatedExternally;
    }

    public function setValidatedExternally(bool $validatedExternally): self
    {
        $this->validatedExternally = $validatedExternally;

        return $this;
    }

    public function getExternalReference(): ?string
    {
        return $this->externalReference;
    }

    public function setExternalReference(?string $externalReference): self
    {
        $this->externalReference = $externalReference;

        return $this;
    }

    public function getHostOrganism(): ?AntibodyHost
    {
        return $this->hostOrganism;
    }

    public function setHostOrganism(?AntibodyHost $hostOrganism): self
    {
        $this->hostOrganism = $hostOrganism;

        return $this;
    }

    public function getHostTarget(): ?AntibodyHost
    {
        return $this->hostTarget;
    }

    public function setHostTarget(?AntibodyHost $hostTarget): self
    {
        $this->hostTarget = $hostTarget;

        return $this;
    }

    public function getDilution(): ?string
    {
        return $this->dilution;
    }

    public function setDilution(?string $dilution): self
    {
        $this->dilution = $dilution;

        return $this;
    }

    public function getStorageTemperature(): ?int
    {
        return $this->storageTemperature;
    }

    public function setStorageTemperature(?int $storageTemperature): self
    {
        $this->storageTemperature = $storageTemperature;

        return $this;
    }

    public function getClonality(): ?string
    {
        return $this->clonality;
    }

    public function setClonality(?string $clonality): self
    {
        $this->clonality = $clonality;

        return $this;
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function setUsage(?string $usage): self
    {
        $this->usage = $usage;

        return $this;
    }

    /**
     * @return Collection<int, Lot>
     */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): self
    {
        if (!$this->lots->contains($lot)) {
            $this->lots[] = $lot;
        }

        return $this;
    }

    public function removeLot(Lot $lot): self
    {
        $this->lots->removeElement($lot);

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getVendorDocumentation(): Collection
    {
        return $this->vendorDocumentation;
    }

    public function addVendorDocumentation(File $vendorDocumentation): self
    {
        if (!$this->vendorDocumentation->contains($vendorDocumentation)) {
            $this->vendorDocumentation[] = $vendorDocumentation;
        }

        return $this;
    }

    public function removeVendorDocumentation(File $vendorDocumentation): self
    {
        $this->vendorDocumentation->removeElement($vendorDocumentation);

        return $this;
    }
}
