<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\VendorTrait;
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
    use VendorTrait;

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

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private bool $validatedInternally = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private bool $validatedExternally = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $externalReference = null;

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

    /**
     * @return Collection<int, self>
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
     * @return Collection<int, self>
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
}
