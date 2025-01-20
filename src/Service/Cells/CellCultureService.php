<?php
declare(strict_types=1);

namespace App\Service\Cells;

use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\User\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

readonly class CellCultureService
{
    public function __construct(
        private CellService $cellService,
    ) {

    }

    /**
     * @throws LogicException if aliquot is empty
     */
    public function createCellCultureFromAliquot(
        User $user,
        CellAliquot $aliquot
    ): ?CellCulture {
        if ($aliquot->getVials() <= 0) {
            throw new LogicException("Cannot create cell culture from empty aliquot", code: 27_000_000);
        }

        $this->cellService->updateLegacyAliquot($aliquot);

        // Reduce aliquot numbers by 1
        $aliquot->setVials($aliquot->getVials() - 1);

        if ($aliquot->getCell()->isAliquotConsumptionCreatesCulture()) {
            // Only create a cell culture if the cell is not configured to prevent this
            return $this->createCellCulture($user, $aliquot);
        } else {
            return null;
        }
    }

    public function createCellCulture(
        User $user,
        CellAliquot $aliquot,
    ): CellCulture {
        // Create a new cell culture based on that aliquot
        $cellCulture = new CellCulture();

        // Set user from security (= current user)
        $cellCulture->setOwner($user);

        // Set data for cell aliquot
        $cellCulture->setAliquot($aliquot);
        $cellCulture->setUnfrozenOn(new DateTime("today"));
        $cellCulture->setIncubator("unknown");
        $cellCulture->setFlask("T-25");

        return $cellCulture;
    }
}