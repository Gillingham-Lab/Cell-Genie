<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\BoxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BoxRepository::class)
 */
class Box
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
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $rows;

    /**
     * @ORM\Column(type="integer")
     */
    private $cols;

    /**
     * @ORM\ManyToOne(targetEntity=Rack::class, inversedBy="boxes", fetch="EAGER")
     */
    private ?Rack $rack = null;

    /**
     * @ORM\OneToMany(targetEntity=BoxEntry::class, mappedBy="box")
     */
    private Collection $entries;

    /**
     * @ORM\OneToMany(targetEntity=CellAliquote::class, mappedBy="box")
     */
    private Collection $cellAliquotes;

    public function __construct()
    {
        $this->cellAliquotes = new ArrayCollection();
        $this->entries = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getFullLocation();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullLocation(): string
    {
        return "{$this->rack->getName()}/{$this->name}";
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

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function setRows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function getCols(): ?int
    {
        return $this->cols;
    }

    public function setCols(int $cols): self
    {
        $this->cols = $cols;

        return $this;
    }

    public function getRack(): ?Rack
    {
        return $this->rack;
    }

    public function setRack(Rack $rack): self
    {
        $this->rack = $rack;

        return $this;
    }

    /**
     * @return Collection|CellAliquote[]
     */
    public function getCellAliquotes(): Collection
    {
        return $this->cellAliquotes;
    }

    public function addCellAliquote(CellAliquote $cellAliquote): self
    {
        if (!$this->cellAliquotes->contains($cellAliquote)) {
            $this->cellAliquotes[] = $cellAliquote;
            $cellAliquote->setBox($this);
        }

        return $this;
    }

    public function removeCellAliquote(CellAliquote $cellAliquote): self
    {
        if ($this->cellAliquotes->removeElement($cellAliquote)) {
            // set the owning side to null (unless already changed)
            if ($cellAliquote->getBox() === $this) {
                $cellAliquote->setBox(null);
            }
        }

        return $this;
    }

    public function getAliquoteCount(): int
    {
        $count = 0;
        foreach ($this->cellAliquotes as $aliquote) {
            $count += $aliquote->getVials();
        }

        return $count;
    }
}
