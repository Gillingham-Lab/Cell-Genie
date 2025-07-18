<?php
declare(strict_types=1);

namespace App\DataFixtures\Substance;

use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CompoundFixtures extends Fixture implements DependentFixtureInterface
{
    public const PENICILLIN_I_COMPOUND_REFERENCE = "compound.penicillin1";
    public const PENICILLIN_II_COMPOUND_REFERENCE = "compound.penicillin2";

    public function load(ObjectManager $manager): void
    {
        $penicillins = $this->getPenicillins();
        foreach ($penicillins as $penicillin) {
            $manager->persist($penicillin);
        }

        $manager->flush();

        $this->addReference(self::PENICILLIN_I_COMPOUND_REFERENCE, $penicillins[0]);
        $this->addReference(self::PENICILLIN_II_COMPOUND_REFERENCE, $penicillins[1]);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    /**
     * @return Chemical[]
     */
    public function getPenicillins(): array
    {
        $scientist = $this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class);

        return [
            (new Chemical())
                ->setShortName("Pen1")
                ->setLongName("Penicillin I")
                ->setOwner($scientist)
                ->setCasNumber("118-53-6")
                ->setIupacName("(2S,5R,6R)-6-[[(E)-hex-3-enoyl]amino]-3,3-dimethyl-7-oxo-4-thia-1-azabicyclo[3.2.0]heptane-2-carboxylic acid")
                ->setMolecularMass(312.39)
                ->setSmiles("CC/C=C/CC(=O)N[C@H]1[C@@H]2N(C1=O)[C@H](C(S2)(C)C)C(=O)O"),
            (new Chemical())
                ->setShortName("Pen2")
                ->setLongName("Penicillin II")
                ->setOwner($scientist)
                ->setCasNumber("61-33-6")
                ->setIupacName("(2S,5R,6R)-3,3-dimethyl-7-oxo-6-[(2-phenylacetyl)amino]-4-thia-1-azabicyclo[3.2.0]heptane-2-carboxylic acid")
                ->setMolecularMass(334.4)
                ->setSmiles("CC1([C@@H](N2[C@H](S1)[C@@H](C2=O)NC(=O)CC3=CC=CC=C3)C(=O)O)C"),
        ];
    }
}
