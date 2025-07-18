<?php
declare(strict_types=1);

namespace App\DataFixtures\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BoxFixtures extends Fixture implements DependentFixtureInterface
{
    public const HEK293 = "box.hek293";
    public const HCT116 = "box.hct116";
    public const ECOLI = "box.ecoli";

    public function getDependencies(): array
    {
        return [
            RackFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $boxes = [
            (new Box())
                ->setName("Box 1"),
            (new Box())
                ->setName("Box 2"),
            (new Box())
                ->setName("Box 3"),
            (new Box())
                ->setName("Box 4"),
            (new Box())
                ->setName("Box 5"),
        ];

        array_map(fn(Box $box) => $box->setRack($this->getReference(RackFixtures::RACK_1, Rack::class)), $boxes);
        array_map(fn(Box $box) => $manager->persist($box), $boxes);

        $otherBoxes = [
            (new Box())
                ->setName("HEK293 cells")
                ->setRows(9)
                ->setCols(9)
                ->setRack($this->getReference(RackFixtures::RACK_2, Rack::class)),
            (new Box())
                ->setName("HCT 116 cells")
                ->setRows(9)
                ->setCols(9)
                ->setRack($this->getReference(RackFixtures::RACK_2, Rack::class)),
            (new Box())
                ->setName("E. coli cells")
                ->setRows(12)
                ->setCols(12)
                ->setRack($this->getReference(RackFixtures::RACK_2, Rack::class)),
        ];

        $this->setReference(self::HEK293, $otherBoxes[0]);
        $this->setReference(self::HCT116, $otherBoxes[1]);
        $this->setReference(self::ECOLI, $otherBoxes[2]);

        array_map(fn(Box $box) => $manager->persist($box), $otherBoxes);

        $manager->flush();
    }
}
