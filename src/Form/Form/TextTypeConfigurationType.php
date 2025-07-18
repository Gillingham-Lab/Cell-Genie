<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class TextTypeConfigurationType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                "length_min",
                IntegerType::class,
                [
                    "label" => "Minimum length",
                    "required" => false,
                    "constraints" => [
                        new Assert\Length(min: 0, max: 255),
                    ],
                ],
            )
            ->add(
                "length_max",
                IntegerType::class,
                [
                    "label" => "Maximum length",
                    "required" => false,
                    "constraints" => [
                        new Assert\Length(min: 0, max: 255),
                    ],
                ],
            )
        ;
    }
}
