<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\BarcodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BarcodeRepository::class)]
#[ORM\UniqueConstraint(fields: ["barcode"])]
class Barcode
{
    use IdTrait;

    #[ORM\Column(length: 255)]
    private ?string $barcode = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $referencedTable = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $referencedId = null;

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(string $barcode): self
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getReferencedTable(): ?string
    {
        return $this->referencedTable;
    }

    public function setReferencedTable(?string $referencedTable): self
    {
        $this->referencedTable = $referencedTable;
        return $this;
    }

    public function getReferencedId(): ?string
    {
        return $this->referencedId;
    }

    public function setReferencedId(?string $referencedId): self
    {
        $this->referencedId = $referencedId;
        return $this;
    }
}
