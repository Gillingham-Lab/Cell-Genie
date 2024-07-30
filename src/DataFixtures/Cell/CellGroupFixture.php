<?php
declare(strict_types=1);

namespace App\DataFixtures\Cell;

use App\DataFixtures\Other\MorphologyFixture;
use App\DataFixtures\Other\OrganismFixture;
use App\DataFixtures\Other\TissueFixture;
use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CellGroupFixture extends Fixture implements DependentFixtureInterface
{
    const HCT116 = "CellGroup.HCT116";
    const HEK293 = "CellGroup.HEK293";
    const HEK293T = "CellGroup.HEK293T";
    const HeLa = "CellGroup.HELA";
    const Empty = "CellGroup.empty";

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            MorphologyFixture::class,
            OrganismFixture::class,
            TissueFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $cells = [
            $this->getHCT116(),
            $this->getHEK293(),
            $this->getHEK293T(),
            $this->getHeLa(),
            $this->getEmpty(),
        ];

        array_map(fn ($e) => $manager->persist($e), $cells);
        $manager->flush();
    }

    private function getHCT116(): CellGroup
    {
        $cellGroup = (new CellGroup())
            ->setNumber("CVCL_0291")
            ->setName("HCT 116")
            ->setCellosaurusId("CVCL_0291")
            ->setRrid("CVCL_0291")
            ->setIsCancer(true)
            ->setAge("48")
            ->setSex("male (XY)")
            ->setEthnicity("unknown")
            ->setDisease("Colon carcinoma")
        ;

        $this->setReference(self::HCT116, $cellGroup);
        return $cellGroup;
    }

    private function getHEK293(): CellGroup
    {
        $cellGroup = (new CellGroup())
            ->setNumber("CVCL_0045")
            ->setName("HEK293")
            ->setRrid("CVCL_0045")
            ->setCellosaurusId("CVCL_0045")
            ->setIsCancer(false)
            ->setAge("Fetus")
            ->setSex("female (XY)")
            ->setEthnicity("unknown")
        ;

        $this->setReference(self::HEK293, $cellGroup);
        return $cellGroup;
    }

    private function getHEK293T(): CellGroup
    {
        $cellGroup = (new CellGroup())
            ->setName("HEK293T")
            ->setNumber("CVCL_0063")
            ->setAge("Fetus")
            ->setIsCancer(false)
            ->setCultureType("adherent")
            ->setMorphology($this->getReference(MorphologyFixture::Epithelial))
            ->setOrganism($this->getReference(OrganismFixture::Human))
            ->setTissue($this->getReference(TissueFixture::Kidney))
            ->setParent($this->getReference(self::HEK293))
        ;

        $this->setReference(self::HEK293T, $cellGroup);
        return $cellGroup;
    }

    private function getHeLa(): CellGroup
    {
        $cellGroup = (new CellGroup())
            ->setName("HeLa")
            ->setNumber("C003")
            ->setIsCancer(true)
            ->setCultureType("adherent")
            ->setMorphology($this->getReference(MorphologyFixture::Epithelial))
            ->setOrganism($this->getReference(OrganismFixture::Human))
            ->setTissue($this->getReference(TissueFixture::Cervix))
        ;

        $this->setReference(self::HeLa, $cellGroup);
        return $cellGroup;
    }

    private function getEmpty(): CellGroup
    {
        $cellGroup = (new CellGroup())
            ->setName("Empty")
            ->setNumber("Empty")
            ;

        $this->setReference(self::Empty, $cellGroup);
        return $cellGroup;
    }
}