<?php
declare(strict_types=1);

namespace App\Entity\FormEntity;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\SubstanceLot;
use App\Validator\BarcodeHasValidTarget;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Callback([BarcodeHasValidTarget::class, "validate"])]
class BarcodeEntry
{
    #[Assert\NotBlank]
    private ?string $barcode = null;

    private ?CellCulture $cellCulture = null;
    private ?Cell $cell = null;
    private ?Substance $substance = null;
    private ?SubstanceLot $substanceLot = null;

    public function __construct(string $barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * @return string|null
     */
    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function getCellCulture(): ?CellCulture
    {
        return $this->cellCulture;
    }

    public function setCellCulture(?CellCulture $cellCulture): self
    {
        $this->cellCulture = $cellCulture;
        return $this;
    }

    public function getCell(): ?Cell
    {
        return $this->cell;
    }

    public function setCell(?Cell $cell): self
    {
        $this->cell = $cell;
        return $this;
    }

    public function getSubstance(): ?Substance
    {
        return $this->substance;
    }

    public function setSubstance(?Substance $substance): self
    {
        $this->substance = $substance;
        return $this;
    }

    public function getSubstanceLot(): ?SubstanceLot
    {
        return $this->substanceLot;
    }

    public function setSubstanceLot(?SubstanceLot $substanceLot): self
    {
        $this->substanceLot = $substanceLot;
        return $this;
    }
}
