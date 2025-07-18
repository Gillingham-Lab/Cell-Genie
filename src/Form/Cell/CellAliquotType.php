<?php
declare(strict_types=1);

namespace App\Form\Cell;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Form\CompositeType\PrivacyAwareType;
use App\Form\SaveableType;
use App\Form\Storage\BoxPositionType;
use App\Form\Traits\VocabularyTrait;
use App\Form\UserEntityType;
use App\Repository\Vocabulary\VocabularyRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<CellAliquot>
 */
class CellAliquotType extends SaveableType
{
    /**
     * @phpstan-use VocabularyTrait<CellAliquot>
     */
    use VocabularyTrait;

    public function __construct(
        private readonly VocabularyRepository $vocabularyRepository,
    ) {}

    /**
     * @param FormBuilderInterface<CellAliquot|null> $builder
     * @param bool $includeParent
     * @return FormBuilderInterface<CellAliquot|null>
     */
    protected function getGeneralForm(FormBuilderInterface $builder, bool $includeParent = true): FormBuilderInterface
    {
        $builder = $builder
            ->create("_general", FormType::class, [
                "label" => "General",
                "inherit_data" => true,
            ])
            ->add("aliquotName", TextType::class, [
                "label" => "Aliquot name",
                "help" => "Give each aliquot a name that is also mentioned on the physical tube(s)",
                "required" => true,
            ])
            ->add("aliquoted_on", DateType::class, [
                "label" => "Aliquoted on",
                "help" => "On which date has this field been aliquoted? Leave empty if unknown.",
                "required" => false,
                "html5" => true,
                "empty_data" => "",
                "placeholder" => "Set a date",
                "widget" => "single_text",
            ])
            ->add("aliquoted_by", UserEntityType::class, [
                "label" => "Aliquoted by",
                "help" => "Who has aliquoted it?",
                "required" => false,
            ])
            ->add("cryoMedium", ... $this->getTextOrChoiceOptions("cryoMedium", [
                "label" => "Cryo medium",
                "help" => "In which cryo medium were the cells aliquoted?",
                "required" => true,
            ]))
            ->add("passage", IntegerType::class, [
                "label" => "Passage number (p)",
                "required" => true,
            ])
            ->add("passageDetail", TextareaType::class, [
                "label" => "Passage detail",
                "help" => "Describe what the passage number means. Passage after getting it from ATCC, after thawing, after new cell line?",
                "required" => false,
            ])
            ->add("cellCount", IntegerType::class, [
                "label" => "Number of cells in the aliquot, per 1000",
                "help" => "Leave empty if not known.",
                "empty_data" => null,
                "required" => false,
            ])
            ->add("_privacy", PrivacyAwareType::class, [
                "inherit_data" => true,
                "label" => "Ownership",
            ])
        ;

        return $builder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $this->getGeneralForm($builder, false),
            )
            ->add(
                $builder->create("_storage", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Storage",
                ])
                ->add("storageCoordinate", BoxPositionType::class, [

                ])
                ->add("boxCoordinate", TextType::class, options: [
                    "label" => "Position in box",
                    "help" => "Give the position in the box. Use letters for row, and numbers for column (A12 is the first row, 12th column; AA1 is the 27th row, 1st column)",
                    "required" => false,
                ])
                ->add("vialColor", TextType::class, [
                    "label" => "Vial colour",
                    "help" => "Write down the colour of the cryo vial lid (eg, red, blue, green, purple ...). Any named colour is accepted.",
                ])
                ->add("vials", IntegerType::class, [
                    "label" => "Number of vials left",
                    "required" => true,
                ])
                ->add("maxVials", IntegerType::class, [
                    "label" => "Number of vials created",
                    "required" => true,
                ]),
            )
            ->add(
                $builder->create("_mycoplasma", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Mycoplasma testing",
                ])
                ->add("mycoplasmaTestedOn", DateType::class, [
                    "label" => "Tested on",
                    "help" => "On which date has this aliquot been tested?",
                    "required" => false,
                    "html5" => true,
                    "empty_data" => "",
                    "placeholder" => "Set a date",
                    "widget" => "single_text",
                ])
                ->add("mycoplasmaTestedBy", UserEntityType::class, [
                    "label" => "Tested by",
                    "help" => "Who has run the mycoplasma test?",
                    "required" => false,
                ])
                ->add("mycoplasmaResult", ChoiceType::class, [
                    "label" => "Test result",
                    "choices" => [
                        "unknown" => "unknown",
                        "positive" => "positive",
                        "negative" => "negative",
                        "unclear" => "unclear",
                    ],
                    "empty_data" => null,
                    "placeholder" => "Select a test result",
                    "required" => false,
                ])
                ->add("mycoplasma", CKEditorType::class, [
                    "label" => "Comment",
                    "help" => "Additional information (what test; what there anything weird; references).",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ]),
            )
            ->add(
                $builder->create("_other", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Other",
                ])
                ->add("typing", CKEditorType::class, [
                    "label" => "Typing",
                    "help" => "Typing information if it has been done.",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("history", CKEditorType::class, [
                    "label" => "History",
                    "help" => "Any important history for that cell aliquot.",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ]),
            )
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => CellAliquot::class,
            "current_cell" => null,
            "current_aliquot" => null,
        ]);

        $resolver->setAllowedTypes("current_cell", ['null', Cell::class]);
        $resolver->setAllowedTypes("current_aliquot", ['null', CellAliquot::class]);

        parent::configureOptions($resolver);
    }
}
