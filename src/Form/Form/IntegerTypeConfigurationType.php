<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class IntegerTypeConfigurationType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("datatype_int", ChoiceType::class, [
                "label" => "Integer Size",
                "help" => "Choose the integer size. Permitted range for values is between −2^(n−1) and +2^(n-1)-1",
                "choices" => [
                    "8 Bit (char, -128 to 127)" => 1,
                    "16 Bit (short, -32 768 to 32 767)" => 2,
                    "32 Bit (long, -2 147 483 648 to 2 147 483 647)" => 4,
                    "64 Bit (long long, -9 223 372 036 854 775 808 to 9 223 372 036 854 775 807)" => 8,
                ],
                "required" => true,
                "empty_data" => 1,
            ])
            ->add("unsigned", CheckboxType::class, [
                "label" => "Unsigned integer",
                "help" => "Making a integer unsigned changes the range from 0 to +2^n-1. **This field is ignored on 64 bit integers due to platform limitations**.",
                "required" => false,
            ])
        ;
    }
}