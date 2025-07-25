<?php
declare(strict_types=1);

namespace App\DataFixtures\Cell;

use App\DataFixtures\Storage\BoxFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CellAliquotFixture extends Fixture implements DependentFixtureInterface
{
    public const HEK293 = "cell.aliquot.HEK293";
    public const OldHEK293 = "cell.aliquot.HEK293.old";
    public const HEK293T = "cell.aliquot.HEK293T";
    public const HCT116 = "cell.aliquot.HCT116";
    public const HeLa = "cell.aliquot.HeLa";
    public const EColi = "cell.aliquot.EColi";

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CellFixture::class,
            BoxFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $aliquots = [
            $this->getHEK293Aliquot(),
            $this->getOldHEK293Aliquot(),
            $this->getEColiAliquot(),
        ];

        array_map(fn(CellAliquot $aliquot) => $manager->persist($aliquot), $aliquots);
        $manager->flush();
    }

    public function getHek293Aliquot(): CellAliquot
    {
        $aliquot = (new CellAliquot())
            ->setAliquotName("HEK1")
            ->setCell($this->getReference(CellFixture::HEK293, Cell::class))
            ->setBox($this->getReference(BoxFixtures::HEK293, Box::class))
            ->setAliquotedOn(new DateTime("now"))
            ->setAliquotedBy($this->getReference(UserFixtures::SCIENTIST_USER_REFERENCE, User::class))
            ->setBoxCoordinate("A1")
            ->setCellCount(2000000)
            ->setVialColor("red")
            ->setVials(18)
            ->setMaxVials(18)
            ->setOwner($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class))
            ->setGroup($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class)->getGroup())
            ->setPrivacyLevel(PrivacyLevel::Group)
            ->setPassage(5);

        $this->setReference(self::HEK293, $aliquot);

        return $aliquot;
    }

    public function getOldHEK293Aliquot(): CellAliquot
    {
        $aliquot = (new CellAliquot())
            ->setAliquotName("HEKAntique")
            ->setCell($this->getReference(CellFixture::HEK293, Cell::class))
            ->setBox($this->getReference(BoxFixtures::HEK293, Box::class))
            ->setAliquotedOn(new DateTime("2020-03-25 13:00:00"))
            ->setAliquotedBy($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class))
            ->setBoxCoordinate("A1")
            ->setCellCount(5000000)
            ->setVialColor("green")
            ->setVials(3)
            ->setMaxVials(20)
            ->setOwner($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class))
            ->setGroup($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class)->getGroup())
            ->setPrivacyLevel(PrivacyLevel::Group)
            ->setPassage(15);
        $this->setReference(self::OldHEK293, $aliquot);

        return $aliquot;
    }

    public function getEColiAliquot(): CellAliquot
    {
        $aliquot = (new CellAliquot())
            ->setAliquotName("ECOLI.1")
            ->setCell($this->getReference(CellFixture::EColi, Cell::class))
            ->setBox($this->getReference(BoxFixtures::ECOLI, Box::class))
            ->setAliquotedOn(new DateTime("now"))
            ->setAliquotedBy($this->getReference(UserFixtures::SCIENTIST_USER_REFERENCE, User::class))
            ->setBoxCoordinate("A1")
            ->setCellCount(1)
            ->setVialColor("brown")
            ->setVials(25)
            ->setMaxVials(25)
            ->setOwner($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class))
            ->setGroup($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class)->getGroup())
            ->setPrivacyLevel(PrivacyLevel::Group)
            ->setPassage(5);

        $this->setReference(self::EColi, $aliquot);

        return $aliquot;
    }
}
