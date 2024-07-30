<?php
declare(strict_types=1);

namespace App\DataFixtures\Other;

use App\Entity\Organism;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganismFixture extends Fixture
{
    const Human = "organism.human";

    public function load(ObjectManager $manager)
    {
        $organism = (new Organism())->setName("Human")->setType("homo sapiens");
        $this->setReference(self::Human, $organism);

        $manager->persist($organism);
        $manager->flush();
    }
}