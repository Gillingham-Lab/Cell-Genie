<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Epitope;
use App\Entity\File;
use App\Entity\Lot;
use App\Entity\Traits\HasRRID;
use App\Entity\Traits\VendorTrait;
use App\Genie\Enums\AntibodyType;
use App\Genie\Enums\Availability;
use App\Repository\Substance\AntibodyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AntibodyRepository::class)]
#[UniqueEntity(fields: "shortName")]
#[UniqueEntity(fields: "number")]
#[Gedmo\Loggable]
class Antibody extends Substance
{
    use HasRRID;
    use VendorTrait;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $id = null;

    #[ORM\Column(type: "string", enumType: AntibodyType::class, options: ["default" => AntibodyType::Primary])]
    private ?AntibodyType $type;

    /** @var Collection<int, Epitope> */
    #[ORM\ManyToMany(targetEntity: Epitope::class, inversedBy: "antibodies")]
    #[ORM\JoinColumn(name: "antibody_ulid", referencedColumnName: "ulid", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "epitope_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private Collection $epitopeTargets;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?String $detection = null;

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

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $dilution = null;

    #[ORM\Column(type: "smallint", nullable: true, options: ["default" => 0])]
    #[Assert\NotBlank]
    #[Assert\Range(
        notInRangeMessage: "Storage temperature must between -200 °C and 25 °C.",
        min: -200,
        max: 25
    )]
    private ?int $storageTemperature = 0;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 250)]
    private ?string $clonality = "monoclonal";

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 250)]
    private ?string $usage = "Western blot";

    /** @var Collection<int, File> */
    #[ORM\ManyToMany(targetEntity: File::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinTable(name: "antibody_vendor_documentation_files")]
    #[ORM\JoinColumn(name: "antibody_ulid", referencedColumnName: "ulid")]
    #[ORM\InverseJoinColumn(name: "file_id", referencedColumnName: "id", unique: true)]
    #[Assert\Valid]
    private Collection $vendorDocumentation;

    protected ?bool $available = null;

    public function __construct()
    {
        parent::__construct();
        $this->epitopeTargets = new ArrayCollection();
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

    /**
     * @return Collection<int, Epitope>
     */
    public function getEpitopeTargets(): Collection
    {
        return $this->epitopeTargets;
    }

    public function addEpitopeTarget(Epitope $epitope): self
    {
        if (!$this->epitopeTargets->contains($epitope)) {
            $this->epitopeTargets[] = $epitope;
        }

        return $this;
    }

    public function removeEpitopeTarget(Epitope $epitope): self
    {
        $this->epitopeTargets->removeElement($epitope);

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
     * @return Collection<int, File>
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

    public function getType(): ?AntibodyType
    {
        return $this->type;
    }

    public function setType(?AntibodyType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getAvailable(): ?bool
    {
        // If availability is null, we determine it manually by checking all lots.
        // Can be set manually to prevent auto-checking.
        if ($this->available === null) {
            $available = false;
            /** @var Lot $lot */
            foreach ($this->getLots() as $lot) {
                if ($lot->getAvailability() === Availability::Available) {
                    $available = true;
                }
            }

            $this->available = $available;
        }
        return $this->available;
    }

    public function setAvailable(?bool $available): self
    {
        $this->available = $available;
        return $this;
    }

    public function getCitation(?Lot $lot=null): string
    {
        $other = [
            $this->getVendor()?->getName() ?? "??",
            $this->getVendorPn() ?? "??",
        ];

        if ($lot) {
            $other[] = "#Lot:{$lot->getLotNumber()}";
        }

        if ($this->rrid) {
            $other[] = "RRID:{$this->rrid}";
        }

        $other = implode(", ", $other);

        return "{$this->getLongName()} ($other)";
    }
}
