<?php
declare(strict_types=1);

namespace App\DataFixtures\Experiment;

use App\DataFixtures\Substance\CompoundFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\PrivacyLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExperimentalDataFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $scientist */
        $scientist = $this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE);

        $condition1 = (new ExperimentalRunCondition())
            ->setName("Condition 1")
            ->setControl(false)
            ->addData((new ExperimentalDatum())
                ->setName("_compound")
                ->setType(DatumEnum::EntityReference)
                ->setValue($this->getReference(CompoundFixtures::PENICILLIN_I_COMPOUND_REFERENCE))
            )
            ->addData((new ExperimentalDatum())
                ->setName("_time")
                ->setType(DatumEnum::UInt16)
                ->setValue(24)
            )
        ;

        $condition2 = (new ExperimentalRunCondition())
            ->setName("Condition 2")
            ->setControl(false)
            ->addData((new ExperimentalDatum())
                ->setName("_compound")
                ->setType(DatumEnum::EntityReference)
                ->setValue($this->getReference(CompoundFixtures::PENICILLIN_II_COMPOUND_REFERENCE))
            )
            ->addData((new ExperimentalDatum())
                ->setName("_time")
                ->setType(DatumEnum::UInt16)
                ->setValue(24)
            )
        ;

        $run = (new ExperimentalRun())
            ->setName("AF001 - Penicillin inhibition")
            ->setDesign($this->getReference(ExperimentalDesignFixtures::EXPERIMENTAL_DESIGN))
            ->setScientist($scientist)
            ->addCondition($condition1)
            ->addCondition($condition2)
            ->addDataSet((new ExperimentalRunDataSet())
                ->setCondition($condition1)
                ->addData((new ExperimentalDatum())
                    ->setName("_MIC")
                    ->setType(DatumEnum::Float32)
                    ->setValue(150)
                )
            )
            ->addDataSet((new ExperimentalRunDataSet())
                ->setCondition($condition2)
                ->addData((new ExperimentalDatum())
                    ->setName("_MIC")
                    ->setType(DatumEnum::Float32)
                    ->setValue(140)
                )
            )
            ->setOwner($scientist)
            ->setGroup($scientist->getGroup())
            ->setPrivacyLevel(PrivacyLevel::Group)
        ;

        $manager->persist($run);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CompoundFixtures::class,
            ExperimentalDesignFixtures::class,
        ];
    }
}