<?php
declare(strict_types=1);

namespace App\DataFixtures\Substance;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Genie\Enums\AntibodyType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AntibodyFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $antibody = (new Antibody())
            ->setShortName("anti-mouse")
            ->setLongName("anti-mouse (long)")
            ->setNumber("AB001")
            ->setType(AntibodyType::Primary)
        ;

        $manager->persist($antibody);
        $manager->flush();
    }
}