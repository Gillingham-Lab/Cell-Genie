<?php
declare(strict_types=1);

namespace App\DataFixtures\Cell;

use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\User\User;
use App\Service\Cells\CellCultureService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CellCultureFixture extends Fixture implements DependentFixtureInterface
{
    const string OldHEK293CulturePrefix = "cell.culture.oldHEK293.";

    public function __construct(
        private CellCultureService $cellCultureService,
    ) {

    }

    public function getDependencies(): array
    {
        return [
            CellAliquotFixture::class,
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class);
        $oldHekAliquot = $this->getReference(CellAliquotFixture::OldHEK293, CellAliquot::class);

        for ($i = 0; $i < 2; $i++) {
            $cellCulture = $this->cellCultureService->createCellCultureFromAliquot($user, $oldHekAliquot);
            $cellCulture->setUnfrozenOn(new \DateTime("2020-03-11"));
            $manager->persist($cellCulture);
            $this->setReference(self::OldHEK293CulturePrefix . $i, $cellCulture);
        }

        $manager->flush();
    }
}