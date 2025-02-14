<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Form\Collection\TableLiveCollectionType;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Service\Experiment\ExperimentalDataFormRowService;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ExperimentalRun>
 */
class ExperimentalRunDataType extends AbstractType
{
    public function __construct(
        private readonly ExperimentalDataFormRowService $formRowService,
    ) {

    }

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

    /**
     * @param FormBuilderInterface<ExperimentalRun> $builder
     * @param array<string, mixed> $options
     */
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

        $innerBuilder =$builder->create("_metadata", FormType::class, [
            "label" => "Metadata",
            "inherit_data" => true,
        ]);

        $this->formRowService->createBuilder($innerBuilder, "data", ... $fields);

        $builder->add($innerBuilder);
    }

    /**
     * @param FormBuilderInterface<ExperimentalRun> $builder
     * @param array<string, mixed> $options
     */
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

        $models = $design->getModels();
        $modelChoices = [];
        foreach ($models as $model) {
            $modelChoices[$model->getName()] = $model->getModel();
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
                    "models" => $modelChoices,
                ],
            ])
        );
    }

    /**
     * @param FormBuilderInterface<ExperimentalRun> $builder
     * @param array<string, mixed> $options
     */
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

        $conditions = [];
        foreach ($builder->getData()->getConditions() as $condition) {
            $conditions[$condition->getName()] = $condition->getName();
        }

        $this->addDataSetCollection($innerBuilder, $fields, $conditions);
        $builder->add($innerBuilder);

        // Initially build up the choice list from persisted conditions
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event): void {
                $form = $event->getForm();

                foreach ($form->get("_dataSets")->get("dataSets") as $dataset) {
                    $dataset->get("condition_name")->setData($dataset->getData()->getCondition()?->getName() ?? "");
                }
            }
        );

        // Update the choice list for all existing conditions
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($fields) {
                /** @var Form $dataSetFormEntry */
                $dataSetFormEntry = $event->getForm()["_dataSets"];
                $dataSetFormEntry->remove("dataSets");

                $eventData = $event->getData();

                $conditions = [];
                foreach ($eventData["_conditions"]["conditions"] as $position => $condition) {
                    if (isset($condition["name"])) {
                        $conditions[$condition["name"]] = $condition["name"];
                    }
                }

                $conditionChoices = $conditions;

                $this->addDataSetCollection($dataSetFormEntry, $fields, $conditionChoices);
            }
        );

        // Upon submit, we need to update the model with the real conditions.
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $formData = $event->getForm()->getData();
                $eventData = $event->getData();

                $conditions = $formData->getConditions();

                $dataSets = $event->getForm()->get("_dataSets")->get("dataSets");
                foreach ($dataSets as $dataSet) {
                    $selectedConditionName = $dataSet->get("condition_name")->getViewData();
                    $condition = $conditions->filter(fn (ExperimentalRunCondition $condition) => $condition->getName() === $selectedConditionName)->first();

                    if ($condition !== false) {
                        $dataSet = $dataSet->getNormData();
                        $dataSet->setCondition($condition);
                    }
                }
            }
        );
    }
    /**
     * @param FormBuilderInterface<ExperimentalRun> $builder
     * @param iterable<int, ExperimentalDesignField> $fields
     * @param array<string, string> $conditionChoices
     */
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