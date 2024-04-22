<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Box;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Cell\CellGroup;
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

        $cellGroup = (new CellGroup())
            ->setName("HEK293")
            ->setNumber("C001")
            ->setAge("Fetus")
            ->setIsCancer(false)
            ->setCultureType("adherent")
            ->setMorphology($morphology)
            ->setOrganism($organism)
            ->setTissue($tissue_kidney)
        ;

        $cell = (new Cell())
            ->setIsEngineered(false)
            ->setCellGroup($cellGroup)
            ->setCellNumber("C001")
        ;

        $manager->persist($morphology);
        $manager->persist($organism);
        $manager->persist($tissue_kidney);
        $manager->persist($tissue_cervix);
        $manager->persist($cellGroup);
        $manager->persist($cell);

        $cellGroup2 = (new CellGroup())
            ->setName("HEK293T")
            ->setNumber("C002")
            ->setAge("Fetus")
            ->setIsCancer(false)
            ->setCultureType("adherent")
            ->setMorphology($morphology)
            ->setOrganism($organism)
            ->setTissue($tissue_kidney)
            ->setParent($cellGroup)
        ;

        $cell2 = (new Cell())
            ->setName("HEK293T")
            ->setCellGroup($cellGroup2)
            ->setIsEngineered(false)
            ->setCellNumber("C002")
        ;
        $manager->persist($cell2);
        $manager->persist($cellGroup2);

        $cellGroup3 = (new CellGroup())
            ->setName("HeLa")
            ->setNumber("C003")
            ->setIsCancer(true)
            ->setCultureType("adherent")
            ->setMorphology($morphology)
            ->setOrganism($organism)
            ->setTissue($tissue_cervix)
        ;

        $cell3 = (new Cell())
            ->setName("HeLa")
            ->setIsEngineered(false)
            ->setCellGroup($cellGroup3)
            ->setCellNumber("C003")
        ;
        $manager->persist($cell3);
        $manager->persist($cellGroup3);

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

        $hekAliquote = (new CellAliquot())
            ->setCell($cell)
            ->setBox($box)
            ->setAliquotedOn(new \DateTime("now"))
            ->setBoxCoordinate("A1")
            ->setCellCount(2000000)
            ->setVialColor("red")
            ->setVials(18)
            ->setPassage(5);
        ;
        $manager->persist($hekAliquote);

        $oldHekAliquote = (new CellAliquot())
            ->setCell($cell)
            ->setBox($secondBox)
            ->setAliquotedOn(new \DateTime("2020-03-25 13:00:00"))
            ->setBoxCoordinate("A1")
            ->setCellCount(5000000)
            ->setVialColor("green")
            ->setVials(3)
            ->setPassage(15);
        ;
        $manager->persist($oldHekAliquote);

        $helaAliquote = (new CellAliquot())
            ->setCell($cell3)
            ->setBox($box)
            ->setAliquotedOn(new \DateTime("now"))
            ->setBoxCoordinate("C1")
            ->setCellCount(1000000)
            ->setVialColor("yellow")
            ->setVials(5)
            ->setPassage(9)
        ;
        $manager->persist($helaAliquote);

        $manager->flush();
    }
}
