<?php
declare(strict_types=1);

namespace App\DataFixtures\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BoxFixtures extends Fixture implements DependentFixtureInterface
{
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

        array_map(fn (Box $box) => $manager->persist($box), $boxes);
        array_map(fn (Box $box) => $box->setRack($this->getReference(RackFixtures::RACK_1)),$boxes);

        $manager->flush();
    }
}