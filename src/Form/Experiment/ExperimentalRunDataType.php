<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Form\Collection\TableLiveCollectionType;
use App\Genie\Enums\ExperimentalFieldRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        $this->addConditionFields($builder, $options);
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
            $builder->create("_metadata", FormType::class, [
                "label" => "Metadata",
                "inherit_data" => true,
            ])
        );
    }

    private function addConditionFields(FormBuilderInterface $builder, array $options): void
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
            ->add("conditions", TableLiveCollectionType::class, [
                "label" => " ",
                "allow_add" => true,
                "allow_delete" => true,
                "button_add_options" => [
                    "label" => "+"
                ],
                "button_delete_options" => [
                    "label" => "−",
                ],
                "entry_type" => ExperimentConditionRowType::class,
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

        $innerBuilder = $builder->create("_dataSets", FormType::class, [
            "label" => "Data",
            "inherit_data" => true,
        ]);

        $this->addDataSetCollection($innerBuilder, $fields, $builder->getData()->getConditions()->toArray());
        $builder->add($innerBuilder);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($fields) {
                /** @var Form $dataSetFormEntry */
                $dataSetFormEntry = $event->getForm()["_dataSets"];
                $dataSetFormEntry->remove("dataSets");

                $conditions = [];
                foreach ($event->getForm()["_conditions"]["conditions"]->getData() as $condition) {
                    $conditions[] = $condition;
                }

                $conditionChoices = $conditions;

                $this->addDataSetCollection($dataSetFormEntry, $fields, $conditionChoices);
            }
        );
    }

    private function addDataSetCollection(FormBuilderInterface|Form $builder, iterable $fields, array $conditionChoices = []): void
    {
        $builder->add("dataSets", TableLiveCollectionType::class, [
            "label" => " ",
            "allow_add" => true,
            "allow_delete" => true,
            "button_add_options" => [
                "label" => "+"
            ],
            "button_delete_options" => [
                "label" => "−",
            ],
            "entry_type" => ExperimentalDataSetRowType::class,
            "entry_options" => [
                "fields" => $fields,
                "condition_choices" => $conditionChoices,
            ],
        ]);
    }
}