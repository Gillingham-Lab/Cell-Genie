<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameTrait;
use App\Entity\Traits\VendorTrait;
use App\Repository\PlateTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlateTypeRepository::class)]
#[Gedmo\Loggable]
class PlateType
{
    use IdTrait;
    use NameTrait;
    use VendorTrait;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Range(min: 1, max: 128)]
    private ?int $rows = 12;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Range(min: 1, max: 128)]
    private ?int $cols = 8;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $material;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Gedmo\Versioned]
    private ?string $color;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    #[Gedmo\Versioned]
    private ?string $colorBottom;

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Length(max: 30)]
    #[Gedmo\Versioned]
    private ?string $wellType;

    #[ORM\OneToMany(mappedBy: 'plate', targetEntity: PlateWell::class, orphanRemoval: true)]
    private $wells;

    public function __construct()
    {
        $this->wells = new ArrayCollection();
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

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(?string $material): self
    {
        $this->material = $material;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getColorBottom(): ?string
    {
        return $this->colorBottom;
    }

    public function setColorBottom(?string $colorBottom): self
    {
        $this->colorBottom = $colorBottom;

        return $this;
    }

    public function getWellType(): ?string
    {
        return $this->wellType;
    }

    public function setWellType(string $wellType): self
    {
        $this->wellType = $wellType;

        return $this;
    }

    /**
     * @return Collection<int, PlateWell>
     */
    public function getWells(): Collection
    {
        return $this->wells;
    }

    public function addWell(PlateWell $well): self
    {
        if (!$this->wells->contains($well)) {
            $this->wells[] = $well;
            $well->setPlate($this);
        }

        return $this;
    }

    public function removeWell(PlateWell $well): self
    {
        if ($this->wells->removeElement($well)) {
            // set the owning side to null (unless already changed)
            if ($well->getPlate() === $this) {
                $well->setPlate(null);
            }
        }

        return $this;
    }
}
