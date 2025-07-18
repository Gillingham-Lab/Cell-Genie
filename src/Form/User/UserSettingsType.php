<?php
declare(strict_types=1);

namespace App\Form\User;

use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FormGroupType;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @extends AbstractType<mixed>
 */
class UserSettingsType extends AbstractType
{
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
                    "choices" => self::getDateFormats(),
                    "help" => "Y = year, m = month, d = day",
                    "allow_add" => true,
                    "required" => false,
                ])
                ->add("hideSmilesInDataOverview", CheckboxType::class, [
                    "label" => "Hide smiles in experimental data overview",
                    "empty_data" => null,
                    "required" => false,
                ]),
            )
            ->add(
                $builder->create("numbering", FormType::class, [
                    "label" => "Numbering options",
                    "inherit_data" => true,
                ])
                ->add("sigill", TextType::class, [
                    "label" => "User Sigill",
                    "required" => false,
                    "empty_data" => "",
                    "help" => "Depdending on the settings, the sigill will be added to numbers of compounds.",
                    "constraints" => [
                        new Length(max: 6),
                    ],
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
                        ],
                    ]),
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
                        ],
                    ]),
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
                        ],
                    ]),
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
                        ],
                    ]),
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
                        ],
                    ]),
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
                        ],
                    ]),
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
                        ],
                    ]),
                ),
            )
        ;
    }

    /**
     * @return array<string>
     */
    public static function getDateFormats(): array
    {
        $date = new DateTime("now");

        $formats = [
            "Y-m-d",
            "d. m. Y",
            "m/d/Y",
            "d. M. Y",
            "d. F Y",
            "F d, Y",
            "D, d. M. Y",
        ];

        return array_combine(array_map(fn($x) => $date->format($x), $formats), $formats);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
        ]);
    }
}
