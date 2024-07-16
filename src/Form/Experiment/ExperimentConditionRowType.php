<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Service\Experiment\ExperimentalDataFormRowService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentConditionRowType extends AbstractType
{
    public function __construct(
        private readonly ExperimentalDataFormRowService $formRowService,
    ) {

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "fields" => null,
            "data_class" => ExperimentalRunCondition::class,
        ]);

        $resolver->setAllowedTypes("fields", Collection::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Add constant fields
        $builder
            ->add("name", TextType::class, [
                "label" => "Condition Name"
            ])
            ->add("control", CheckboxType::class, [
                "label" => "Control condition",
                "empty_data" => null,
                "required" => false,
            ])
        ;

        $this->formRowService->createBuilder($builder, "data", ... $options["fields"]);
    }
}