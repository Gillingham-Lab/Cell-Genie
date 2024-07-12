<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Form\Form\FormRowType;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\ExperimentalFieldVariableRoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalDesignFieldType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalDesignField::class,
        ]);
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
            ->add("formRow", FormRowType::class, [
                "label" => "Field settings",
            ])
        ;
    }
}