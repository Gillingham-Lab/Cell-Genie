<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Cell;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Tissue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $morphology = (new Morphology())->setName("epithelial");
        $organism = (new Organism())->setName("Human")->setType("homo sapiens");
        $tissue_kidney = (new Tissue())->setName("Kidney");
        $tissue_cervix = (new Tissue())->setName("Cervix");

        $cell = (new Cell())
            ->setName("HEK293")
            ->setAge("Fetus")
            ->setIsCancer(false)
            ->setIsEngineered(false)
            ->setCultureType("adherent")
            ->setMorphology($morphology)
            ->setOrganism($organism)
            ->setTissue($tissue_kidney)
        ;

        $manager->persist($morphology);
        $manager->persist($organism);
        $manager->persist($tissue_kidney);
        $manager->persist($tissue_cervix);
        $manager->persist($cell);

        $cell2 = (new Cell())
            ->setName("HEK293T")
            ->setAge("Fetus")
            ->setIsCancer(false)
            ->setIsEngineered(false)
            ->setCultureType("adherent")
            ->setMorphology($morphology)
            ->setOrganism($organism)
            ->setTissue($tissue_kidney)
        ;
        $manager->persist($cell2);

        $cell3 = (new Cell())
            ->setName("HeLa")
            ->setAge("Adult")
            ->setIsCancer(true)
            ->setIsEngineered(false)
            ->setCultureType("adherent")
            ->setMorphology($morphology)
            ->setOrganism($organism)
            ->setTissue($tissue_cervix)
        ;
        $manager->persist($cell3);

        $manager->flush();
    }
}
