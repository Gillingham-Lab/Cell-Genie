<?php
declare(strict_types=1);

namespace App\DataFixtures\Other;

use App\Entity\DoctrineEntity\Vocabulary\Tissue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TissueFixture extends Fixture
{
    const Kidney = "tissue.kidney";
    const Cervix = "tissue.cervix";

    public function load(ObjectManager $manager): void
    {
        $tissue_kidney = (new Tissue())->setName("Kidney");
        $tissue_cervix = (new Tissue())->setName("Cervix");

        $this->setReference(self::Kidney, $tissue_kidney);
        $this->setReference(self::Cervix, $tissue_cervix);

        $manager->persist($tissue_kidney);
        $manager->persist($tissue_cervix);
        $manager->flush();
    }
}