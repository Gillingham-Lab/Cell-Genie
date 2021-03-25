<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Box;
use App\Entity\Cell;
use App\Entity\CellAliquote;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Rack;
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

        $rack = (new Rack())
            ->setMaxBoxes(9)
            ->setName("Rack 1")
        ;
        $manager->persist($rack);

        $box = (new Box())
            ->setCols(9)
            ->setRows(9)
            ->setName("HEK293 cells")
            ->setRack($rack)
        ;
        $manager->persist($box);

        $secondBox = (new Box())
            ->setCols(8)
            ->setRows(8)
            ->setName("Old cells")
            ->setRack($rack)
        ;
        $manager->persist($secondBox);

        $hekAliquote = (new CellAliquote())
            ->setCell($cell)
            ->setBox($box)
            ->setAliquotedOn(new \DateTime("now"))
            ->setCellCount(2000000)
            ->setVialColor("red")
            ->setVials(18)
            ->setPassage(5);
        ;
        $manager->persist($hekAliquote);

        $oldHekAliquote = (new CellAliquote())
            ->setCell($cell)
            ->setBox($secondBox)
            ->setAliquotedOn(new \DateTime("2020-03-25 13:00:00"))
            ->setCellCount(5000000)
            ->setVialColor("green")
            ->setVials(3)
            ->setPassage(15);
        ;
        $manager->persist($oldHekAliquote);

        $helaAliquote = (new CellAliquote())
            ->setCell($cell3)
            ->setBox($box)
            ->setAliquotedOn(new \DateTime("now"))
            ->setCellCount(1000000)
            ->setVialColor("yellow")
            ->setVials(5)
            ->setPassage(9)
        ;
        $manager->persist($helaAliquote);

        $manager->flush();
    }
}
