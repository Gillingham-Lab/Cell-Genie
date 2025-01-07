<?php
declare(strict_types=1);

namespace App\DataFixtures\Storage;

use App\DataFixtures\GroupFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RackFixtures extends Fixture implements DependentFixtureInterface
{
    const RACK_1 = "rack.1";
    const RACK_2 = "rack.2";

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            GroupFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Rack[] $boxes */
        $boxes = [
            (new Rack())->setName("Rack 1"),
            (new Rack())->setName("Rack 1.1"),
            (new Rack())->setName("Rack 1.1.1"),
            (new Rack())->setName("Rack 1.1.2"),
            (new Rack())->setName("Rack 1.2"),
            (new Rack())->setName("Rack 1.2.1"),
            (new Rack())->setName("Rack 1.2.2"),
            (new Rack())->setName("Rack 2"),
        ];

        // Set relationships
        $boxes[0]->addChild($boxes[1]);
        $boxes[1]->addChild($boxes[2]);
        $boxes[1]->addChild($boxes[3]);
        $boxes[0]->addChild($boxes[4]);
        $boxes[1]->addChild($boxes[5]);
        $boxes[1]->addChild($boxes[6]);

        // Set ownership
        array_map(fn (Rack $box) => $this->setMainGroupOwner($box), $boxes);

        // Persist
        array_map(fn (Rack $box) => $manager->persist($box), $boxes);

        // Add references
        $this->setReference(self::RACK_1, $boxes[0]);
        $this->setReference(self::RACK_2, $boxes[7]);

        // Flush
        $manager->flush();
    }

    private function setMainGroupOwner(Rack $entity): Rack
    {
        $entity
            ->setOwner($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class))
            ->setGroup($this->getReference(GroupFixtures::RESEARCH_GROUP_REFERENCE, UserGroup::class))
        ;

        return $entity;
    }
}