<?php
declare(strict_types=1);

namespace App\DataFixtures\Experiment;

use App\DataFixtures\Substance\CompoundFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\ExperimentalFieldVariableRoleEnum;
use App\Genie\Enums\FormRowTypeEnum;
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
            ->setLongName("Experiment Design 1")
            ->addField(
                (new ExperimentalDesignField())
                    ->setRole(ExperimentalFieldRole::Condition)
                    ->setExposed(true)
                    ->setWeight(0)
                    ->setVariableRole(ExperimentalFieldVariableRoleEnum::Group)
                    ->setFormRow(
                        (new FormRow())
                            ->setLabel("compound")
                            ->setType(FormRowTypeEnum::EntityType)
                            ->setConfiguration([
                                "entityType" => "App\\Entity\\DoctrineEntity\\Substance\\Chemical"
                            ])
                    )
            )
            ->addField(
                (new ExperimentalDesignField())
                    ->setRole(ExperimentalFieldRole::Condition)
                    ->setExposed(true)
                    ->setWeight(1)
                    ->setVariableRole(ExperimentalFieldVariableRoleEnum::X)
                    ->setFormRow(
                        (new FormRow())
                            ->setLabel("time")
                            ->setType(FormRowTypeEnum::IntegerType)
                            ->setConfiguration([
                                "datetype_int" => 2,
                                "unsigned" => true,
                            ])
                    )
            )
            ->addField(
                (new ExperimentalDesignField())
                    ->setRole(ExperimentalFieldRole::Datum)
                    ->setExposed(true)
                    ->setWeight(1)
                    ->setVariableRole(ExperimentalFieldVariableRoleEnum::Y)
                    ->setFormRow(
                        (new FormRow())
                            ->setLabel("MIC")
                            ->setType(FormRowTypeEnum::FloatType)
                            ->setConfiguration([
                                "datatype_float" => 1,
                                "floattype_inactive" => "Inf",
                                "floattype_inactive_label" => null,
                            ])
                    )
            )
        ;

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