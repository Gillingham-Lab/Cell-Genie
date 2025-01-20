<?php
declare(strict_types=1);

namespace App\Service\Cells;

use App\Entity\DoctrineEntity\Cell\CellAliquot;

class CellService
{
    /**
     * Updates legacy cell aliquots without a number of max vials.
     */
    public function updateLegacyAliquot(
        CellAliquot $aliquot
    ): void {
        if ($aliquot->getMaxVials() === null) {
            $aliquot->setMaxVials($aliquot->getVials());
        }
    }
}