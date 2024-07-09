<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\ExperimentalCondition;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Twig\Components\Live\Experiment\ExperimentalConditionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalRunDataType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "design" => null,
        ]);

        $resolver->addAllowedTypes("design", ["null", ExperimentalDesign::class]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options["design"] === null) {
            return;
        }

        $this->addMetadataFields($builder, $options);
        $this->getConditionFields($builder, $options);
        $this->addDataFields($builder, $options);
    }

    private function addMetadataFields(FormBuilderInterface $builder, array $options): void
    {
        /** @var ExperimentalDesign $design */
        $design = $options["design"];

        $fields = $design->getFields()->filter(function (ExperimentalDesignField $field) {
            return $field->getRole() === ExperimentalFieldRole::Top;
        });

        if (count($fields) === 0) {
            return;
        }

        $builder->add(
            $builder->create("metadata", FormType::class, [
                "label" => "Metadata",
            ])
        );
    }

    private function getConditionFields(FormBuilderInterface $builder, array $options): void
    {
        /** @var ExperimentalDesign $design */
        $design = $options["design"];

        $fields = $design->getFields()->filter(function (ExperimentalDesignField $field) {
            return $field->getRole() === ExperimentalFieldRole::Condition;
        });

        if (count($fields) === 0) {
            return;
        }

        $builder->add(
            $builder->create("_conditions", FormType::class, [
                "label" => "Conditions",
                "inherit_data" => true,
            ])
            ->add("conditions", ExperimentalConditionsType::class, [
                "label" => " ",
                "allow_add" => true,
                "allow_delete" => true,
                "button_add_options" => [
                    "label" => "+"
                ],
                "button_delete_options" => [
                    "label" => "−",
                ],
                "entry_options" => [
                    "fields" => $fields,
                ],
            ])
        );
    }

    private function addDataFields(FormBuilderInterface $builder, array $options): void
    {
        /** @var ExperimentalDesign $design */
        $design = $options["design"];

        $fields = $design->getFields()->filter(function (ExperimentalDesignField $field) {
            return $field->getRole() === ExperimentalFieldRole::Datum;
        });

        if (count($fields) === 0) {
            return;
        }

        $builder->add(
            $builder->create("conditions", FormType::class, [
                "label" => "Data",
            ])
        );
    }
}