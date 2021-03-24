<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CellRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity(repositoryClass=CellRepository::class)
 */
class Cell
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="ulid", unique=True)
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     */
    private ?Ulid $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $age;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $cultureType;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isCancer = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isEngineered = false;

    /**
     * @ORM\OneToMany(targetEntity=Cell::class, mappedBy="parent")
     */
    private Collection $children;

    /**
     * @ORM\ManyToOne(targetEntity=Cell::class, inversedBy="children")
     */
    private ?Cell $parent = null;

    /**
     * @ORM\ManyToOne(targetEntity=Organism::class)
     */
    private Morphology $morphology;

    /**
     * @ORM\ManyToOne(targetEntity=Organism::class)
     */
    private Organism $organism;

    public function __construct(
        string $name,
        Morphology $morphology,
        Organism $organism,
        Cell $parent = null
    ) {
        $this->name = $name;
        $this->morphology = $morphology;
        $this->organism = $organism;
        $this->parent = $parent;

        $this->children = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMorphology(): Morphology
    {
        return $this->morphology;
    }

    public function setMorphology(Morphology $morphology): self
    {
        $this->morphology = $morphology;

        return $this;
    }

    public function getOrganism(): Organism
    {
        return $this->organism;
    }

    public function setOrganism(Organism $organism): self
    {
        $this->organism = $organism;

        return $this;
    }
}
