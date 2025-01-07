<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\DoctrineEntity\User\UserGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture
{
    public const RESEARCH_GROUP_REFERENCE = "usergroup.research";
    public const OTHER_GROUP_REFERENCE = "usergroup.other";

    public function load(ObjectManager $manager): void
    {
        $group1 = (new UserGroup())
            ->setShortName("Research Group");
        $this->setReference(self::RESEARCH_GROUP_REFERENCE, $group1);
        $manager->persist($group1);

        $group2 = (new UserGroup())
            ->setShortName("Other Group");
        $this->setReference(self::OTHER_GROUP_REFERENCE, $group2);
        $manager->persist($group2);

        $manager->flush();
    }
}