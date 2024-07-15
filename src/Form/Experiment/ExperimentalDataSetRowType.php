<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use App\Service\Experiment\ExperimentalDataFormRowService;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalDataSetRowType extends AbstractType
{
    public function __construct(
        private readonly ExperimentalDataFormRowService $formRowService,
    ) {

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "fields" => null,
            "condition_choices" => [],
            "data_class" => ExperimentalRunDataSet::class,
        ]);

        $resolver->setAllowedTypes("fields", Collection::class);
        $resolver->setAllowedTypes("condition_choices", ["array"]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Add constant fields
        $builder
            ->add("condition", EntityType::class, [
                "class" => ExperimentalRunCondition::class,
                "label" => "Condition Name",
                "choices" => $options["condition_choices"],
                "empty_data" => null,
                "required" => false,
                "choice_label" => fn (ExperimentalRunCondition $condition) => $condition->getName(),
            ])
        ;

        $this->formRowService->createBuilder($builder, "data", ... $options["fields"]);
    }
}