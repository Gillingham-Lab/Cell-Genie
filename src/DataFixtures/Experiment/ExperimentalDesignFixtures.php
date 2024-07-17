<?php
declare(strict_types=1);

namespace App\DataFixtures\Experiment;

use App\DataFixtures\Substance\CompoundFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Genie\Enums\PrivacyLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExperimentalDesignFixtures extends Fixture implements DependentFixtureInterface
{
    const EXPERIMENTAL_DESIGN = "experimentalDesign.one";

    public function load(ObjectManager $manager): void
    {
        $design = (new ExperimentalDesign())
            ->setOwner($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE))
            ->setGroup($this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE)->getGroup())
            ->setPrivacyLevel(PrivacyLevel::Group)
            ->setNumber("EXP001")
            ->setShortName("Experiment Design 1")
            ->setLongName("Experiment Design 1");

        $this->setReference(self::EXPERIMENTAL_DESIGN, $design);

        $manager->persist($design);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}