<?php
declare(strict_types=1);

namespace App\DataFixtures\Cell;

use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Cell\Cell;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CellFixture extends Fixture implements DependentFixtureInterface
{
    const HCT116 = "Cell.HCT116";
    const HEK293 = "Cell.HEK293";
    const HEK293T = "Cell.HEK293T";
    const HeLa = "Cell.HeLa";

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CellGroupFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $cells = [
            $this->getHCT116(),
            $this->getHEK293(),
            $this->getHEK293T(),
            $this->getHeLa(),
        ];

        array_map(fn ($e) => $manager->persist($e), $cells);
        $manager->flush();
    }

    private function getHCT116(): Cell
    {
        $cell = (new Cell())
            ->setCellGroup($this->getReference(CellGroupFixture::HCT116))
            ->setCellNumber("CL001")
            ->setName("HCT 116")
            ->setIsEngineered(false)
        ;

        $this->setReference(self::HCT116, $cell);
        return $cell;
    }

    private function getHEK293(): Cell
    {
        $cell = (new Cell())
            ->setCellGroup($this->getReference(CellGroupFixture::HEK293))
            ->setCellNumber("CL002")
            ->setName("HEK293")
            ->setIsEngineered(false)
        ;

        $this->setReference(self::HEK293, $cell);
        return $cell;
    }

    private function getHEK293T(): Cell
    {
        $cell = (new Cell())
            ->setName("HEK293T")
            ->setCellGroup($this->getReference(CellGroupFixture::HEK293))
            ->setIsEngineered(false)
            ->setCellNumber("C002")
        ;

        $this->setReference(self::HEK293T, $cell);
        return $cell;
    }

    private function getHeLa(): Cell
    {
        $cell = (new Cell())
            ->setName("HeLa")
            ->setIsEngineered(false)
            ->setCellGroup($this->getReference(CellGroupFixture::HeLa))
            ->setCellNumber("C003")
        ;

        $this->setReference(self::HeLa, $cell);
        return $cell;
    }
}