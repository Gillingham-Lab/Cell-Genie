<?php
declare(strict_types=1);

namespace App\Service\Cells;

use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\User\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CellCultureService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {

    }

    public function createCellCulture(
        User $user,
        CellAliquot $cellAliquot,
    ): CellCulture {
        // Create a new cell culture based on that aliquot
        $cellCulture = new CellCulture();

        // Set user from security (= current user)
        $cellCulture->setOwner($user);

        // Set data for cell aliquot
        $cellCulture->setAliquot($cellAliquot);
        $cellCulture->setUnfrozenOn(new DateTime("today"));
        $cellCulture->setIncubator("unknown");
        $cellCulture->setFlask("T-25");

        // Persist object
        $this->entityManager->persist($cellCulture);

        return $cellCulture;
    }
}