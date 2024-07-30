<?php
declare(strict_types=1);

namespace App\DataFixtures\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BoxFixtures extends Fixture implements DependentFixtureInterface
{
    const HEK293 = "box.hek293";
    const HCT116 = "box.hct116";

    public function getDependencies()
    {
        return [
            RackFixtures::class
        ];
    }

    public function load(ObjectManager $manager)
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

        array_map(fn (Box $box) => $box->setRack($this->getReference(RackFixtures::RACK_1)),$boxes);
        array_map(fn (Box $box) => $manager->persist($box), $boxes);

        $otherBoxes = [
            (new Box())
                ->setName("HEK293 cells")
                ->setRows(9)
                ->setCols(9)
                ->setRack($this->getReference(RackFixtures::RACK_2)),
            (new Box())
                ->setName("HCT 116 cells")
                ->setRows(9)
                ->setCols(9)
                ->setRack($this->getReference(RackFixtures::RACK_2)),
        ];

        $this->setReference(self::HEK293, $otherBoxes[0]);
        $this->setReference(self::HCT116, $otherBoxes[1]);

        array_map(fn (Box $box) => $manager->persist($box), $otherBoxes);

        $manager->flush();
    }
}