<?php
declare(strict_types=1);

namespace App\Form\User;

use App\Form\BasicType\FormGroupType;
use App\Form\FancyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @extends AbstractType<mixed>
 */
class UserGroupSettingsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("displayOptions", FormType::class, [
                    "label" => "Display options",
                    "inherit_data" => true,
                ])
                ->add("entityPageLimit", FancyChoiceType::class, [
                    "label" => "Default page size for entities",
                    "empty_data" => 30,
                    "choices" => [
                        10 => 10,
                        20 => 20,
                        30 => 30,
                        50 => 50,
                        75 => 75,
                        100 => 100,
                    ],
                    "required" => false,
                ])
                ->add("dateFormat", FancyChoiceType::class, [
                    "label" => "Preferred format for datetime",
                    "empty_data" => "d. m. Y",
                    "choices" => UserSettingsType::getDateFormats(),
                    "help" => "Y = year, m = month, d = day",
                    "allow_add" => true,
                    "required" => false,
                ])
            )
            ->add(
                $builder->create("numbering", FormType::class, [
                    "label" => "Numbering options",
                    "inherit_data" => true,
                ])
                ->add("numberingGroupPrefix", TextType::class, [
                    "label" => "Group prefix for numbering entities",
                    "required" => false,
                    "empty_data" => "",
                    "help" => "Changing this will only effect newly created entities, and only if the user does not change the number manually.",
                    "constraints" => [
                        new Length(max: 4),
                    ],
                ])
                ->add("numberingLength", NumberType::class, [
                    "label" => "Length of the number (eg, 4 = 0004) after the prefix",
                    "required" => false,
                    "empty_data" => 4,
                    "constraints" => [
                        new Range(min: 2, max: 30),
                    ]
                ])
                ->add(
                    $builder->create("numberingCell", FormGroupType::class, [
                        "label" => "Cell numbering options",
                    ])
                    ->add("prefix", TextType::class, [
                        "label" => "Prefix for numbering cells",
                        "required" => false,
                        "empty_data" => "",
                        "help" => "Changing this will only effect newly created cells, and only if the user does not change the number manually.",
                        "constraints" => [
                            new Length(max: 4),
                        ]
                    ])
                    ->add("userSigill", CheckboxType::class, [
                        "label" => "Use user sigill for cell numbering by default",
                        "required" => false,
                    ])
                )
                ->add(
                    $builder->create("numberingCellCulture", FormGroupType::class, [
                        "label" => "Cell culture numbering options",
                    ])
                    ->add("prefix", TextType::class, [
                        "label" => "Prefix for numbering cell cultures",
                        "required" => false,
                        "empty_data" => "",
                        "help" => "Changing this will only effect newly created cell cultures, and only if the user does not change the number manually.",
                        "constraints" => [
                            new Length(max: 4),
                        ]
                    ])
                    ->add("userSigill", CheckboxType::class, [
                        "label" => "Use user sigill for cell culture numbering by default",
                        "required" => false,
                    ])
                )
                ->add(
                    $builder->create("numberingAntibody", FormGroupType::class, [
                        "label" => "Antibody numbering options",
                        "help" => "Changing this will only effect newly created antibodies, and only if the user does not change the number manually.",
                    ])
                    ->add("prefix", TextType::class, [
                        "label" => "Prefix for numbering antibodies",
                        "required" => false,
                        "empty_data" => "",
                        "constraints" => [
                            new Length(max: 4),
                        ]
                    ])
                    ->add("userSigill", CheckboxType::class, [
                        "label" => "Use user sigill for antibody numbering by default",
                        "required" => false,
                    ])
                )
                ->add(
                    $builder->create("numberingChemical", FormGroupType::class, [
                        "label" => "Chemical numbering options",
                        "help" => "Changing this will only effect newly created chemicals, and only if the user does not change the number manually.",
                    ])
                    ->add("prefix", TextType::class, [
                        "label" => "Prefix for numbering chemicals",
                        "required" => false,
                        "empty_data" => "",
                        "constraints" => [
                            new Length(max: 4),
                        ]
                    ])
                    ->add("userSigill", CheckboxType::class, [
                        "label" => "Use user sigill for chemical numbering by default",
                        "required" => false,
                    ])
                )
                ->add(
                    $builder->create("numberingOligo", FormGroupType::class, [
                        "label" => "Oligo numbering options",
                        "help" => "Changing this will only effect newly created oligos, and only if the user does not change the number manually.",
                    ])
                    ->add("prefix", TextType::class, [
                        "label" => "Prefix for numbering oligos",
                        "required" => false,
                        "empty_data" => "",
                        "constraints" => [
                            new Length(max: 4),
                        ]
                    ])
                    ->add("userSigill", CheckboxType::class, [
                        "label" => "Use user sigill for oligo numbering by default",
                        "required" => false,
                    ])
                )
                ->add(
                    $builder->create("numberingPlasmid", FormGroupType::class, [
                        "label" => "Plasmid numbering options",
                        "help" => "Changing this will only effect newly created plasmids, and only if the user does not change the number manually.",
                    ])
                    ->add("prefix", TextType::class, [
                        "label" => "Prefix for numbering plasmids",
                        "required" => false,
                        "empty_data" => "",
                        "constraints" => [
                            new Length(max: 4),
                        ]
                    ])
                    ->add("userSigill", CheckboxType::class, [
                        "label" => "Use user sigill for plasmid numbering by default",
                        "required" => false,
                    ])
                )
                ->add(
                    $builder->create("numberingProtein", FormGroupType::class, [
                        "label" => "Protein numbering options",
                        "help" => "Changing this will only effect newly created proteins, and only if the user does not change the number manually.",
                    ])
                    ->add("prefix", TextType::class, [
                        "label" => "Prefix for numbering proteins",
                        "required" => false,
                        "empty_data" => "",
                        "constraints" => [
                            new Length(max: 4),
                        ]
                    ])
                    ->add("userSigill", CheckboxType::class, [
                        "label" => "Use user sigill for protein numbering by default",
                        "required" => false,
                    ])
                )
            )
        ;
    }
}