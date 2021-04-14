<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\AntibodyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AntibodyRepository::class)
 */
class Antibody
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private string $shortName = "";

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    private string $longName = "";

    /**
     * @ORM\ManyToOne(targetEntity=Vendor::class, inversedBy="antibodies")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Vendor $vendor = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $vendorPN = null;

    /**
     * @ORM\ManyToMany(targetEntity=Protein::class, inversedBy="antibodies")
     * @var Collection|Protein[]
     */
    private Collection $proteinTarget;

    /**
     * @ORM\ManyToMany(targetEntity=Antibody::class, inversedBy="antibodies")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @var Collection|self[]
     */
    private Collection $secondaryAntibody;

    /**
     * @ORM\ManyToMany(targetEntity=Antibody::class, mappedBy="secondaryAntibody")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @var Collection|self[]
     */
    private Collection $antibodies;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?String $detection = null;

    /**
     * @ORM\OneToMany(targetEntity=AntibodyDilution::class, mappedBy="antibody", orphanRemoval=true)
     */
    private Collection $antibodyDilutions;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $number = null;

    public function __construct()
    {
        $this->proteinTarget = new ArrayCollection();
        $this->secondaryAntibody = new ArrayCollection();
        $this->antibodies = new ArrayCollection();
        $this->antibodyDilutions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return ($this->number ? $this->number . " | " : "") . ($this->getShortName() ?? "unknown");
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

    /**
     * @return Collection|Protein[]
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

    /**
     * @return Collection|self[]
     */
    public function getSecondaryAntibody(): Collection
    {
        return $this->secondaryAntibody;
    }

    public function addSecondaryAntibody(self $secondaryAntibody): self
    {
        if (!$this->secondaryAntibody->contains($secondaryAntibody)) {
            $this->secondaryAntibody[] = $secondaryAntibody;
        }

        return $this;
    }

    public function removeSecondaryAntibody(self $secondaryAntibody): self
    {
        $this->secondaryAntibody->removeElement($secondaryAntibody);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getAntibodies(): Collection
    {
        return $this->antibodies;
    }

    public function addAntibody(self $antibody): self
    {
        if (!$this->antibodies->contains($antibody)) {
            $this->antibodies[] = $antibody;
            $antibody->addSecondaryAntibody($this);
        }

        return $this;
    }

    public function removeAntibody(self $antibody): self
    {
        if ($this->antibodies->removeElement($antibody)) {
            $antibody->removeSecondaryAntibody($this);
        }

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
     * @return Collection|AntibodyDilution[]
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
}
