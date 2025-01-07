<?php
declare(strict_types=1);

namespace App\DataFixtures\Other;

use App\Entity\Morphology;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MorphologyFixture extends Fixture
{
    const Epithelial = "morphology.epithelial";

    public function load(ObjectManager $manager): void
    {
        $morphology = (new Morphology())->setName("epithelial");
        $this->setReference(self::Epithelial, $morphology);

        $manager->persist($morphology);
        $manager->flush();
    }
}