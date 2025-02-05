<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Form\Form\FormRowType;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\ExperimentalFieldVariableRoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ExperimentalDesignField>
 */
class ExperimentalDesignFieldType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalDesignField::class,
        ]);

        $resolver->define("design")
            ->allowedTypes( ExperimentalDesign::class)
            ->required()
            ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("role", EnumType::class, [
                "class" => ExperimentalFieldRole::class,
                "empty_data" => ExperimentalFieldRole::Top->value,
            ])
            ->add("variableRole", EnumType::class, [
                "class" => ExperimentalFieldVariableRoleEnum::class,
                "empty_data" => ExperimentalFieldVariableRoleEnum::Group->value,
            ])
            ->add("weight", IntegerType::class, [
                "required" => true,
                "empty_data" => 0,
            ])
            ->add("exposed", CheckboxType::class, [
                "required" => false,
                "help" => "If turned on, this field will appear in the data list. For fields with the Roles 'Comparison' or 'Datum', "
                    ." multiple entries will be summarized in the same table cell, for fields the Role 'Top', the value will be "
                    ."repeated for each condition.",
            ])
            ->add("formRow", FormRowType::class, [
                "label" => " ",
                "design" => $options["design"],
                "row_attr" => [
                    "class" => "no-fieldset",
                ]
            ])
        ;
    }
}