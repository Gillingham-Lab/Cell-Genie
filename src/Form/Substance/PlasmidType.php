<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Organism;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\Collection\SequenceAnnotationCollectionType;
use App\Form\SaveableType;
use App\Form\Traits\VocabularyTrait;
use App\Form\User\PrivacyAwareType;
use App\Form\UserEntityType;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlasmidType extends SaveableType
{
    use VocabularyTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("_general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General information"
                ])
                ->add("number", TextType::class, [
                    "label" => "Number",
                    "help" => "Plasmid number",
                ])
                ->add("shortName", TextType::class, [
                    "label" => "Short name",
                    "help" => "Short name of the plasmid, must be unique among all substances.",
                ])
                ->add("longName", TextType::class, [
                    "label" => "Name",
                    "help" => "A longer, more descriptive name.",
                ])
                ->add("createdBy", UserEntityType::class, [
                    "label" => "Created by",
                    "help" => "Who made this plasmid?"
                ])
                ->add("labjournal", TextType::class, [
                    "label" => "Lab journal entry",
                    "help" => "Give a reference to the lab journal entry",
                    "required" => false,
                ])
                ->add("comment", TextareaType::class, [
                    "label" => "Comment",
                    "help" => "A short comment of the purpose of this plasmid, or any other information.",
                    "required" => false,
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership",
                ])
            )
            ->add(
                $builder->create("_features", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General features"
                ])
                ->add("growthResistance", ... $this->getTextOrChoiceOptions("plasmidResistance", [
                    "label" => "Growth resistance",
                    "multiple" => true,
                    "by_reference" => false,
                    "required" => true,
                ]))
                ->add("expressionIn", EntityType::class, [
                    "label" => "Expression system",
                    "help" => "Which organism or bacterial strain should be used to express this protein?",
                    "required" => true,
                    "class" => Organism::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("e")
                            ->addOrderBy("e.name", "ASC")
                            ;
                    },
                    "empty_data" => null,
                    "placeholder" => "Select an organism",
                    "multiple" => false,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("expressionResistance", ... $this->getTextOrChoiceOptions("plasmidResistance", [
                    "label" => "Expression resistance",
                    "help" => "Please provide even if it is the same as for growth (which is usually the case in bacterial production)",
                    "multiple" => true,
                    "by_reference" => false,
                    "required" => true,
                ]))
                ->add("forProduction", CheckboxType::class, [
                    "label" => "Can the plasmid be used for production of the protein?",
                    "required" => false,
                ])
                ->add("expressedProteins", EntityType::class, [
                    "label" => "Expressed Proteins",
                    "help" => "Note which proteins are expressed specifically on this vector. Helper-proteins (like lacI for the lac repressor or the resistance gene) should not be mentioned. "
                        . "If the protein gets cleaved directly after the expression (like with a special linker), both parts are for the purpose of this vector separate proteins. However, "
                        . "please mention this in the comments of this protein.",
                    "class" => Protein::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("e")
                            ->addOrderBy("e.shortName", "ASC")
                            ;
                    },
                    'empty_data' => [],
                    'by_reference' => false,
                    "placeholder" => "Empty",
                    "required" => false,
                    "multiple" => true,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
            )
            ->add(
                $builder->create("_structure", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Structure",
                ])
                ->add("parent", EntityType::class, [
                    "label" => "Parent plasmid",
                    "class" => Plasmid::class,
                    "query_builder" => function (EntityRepository $er) use ($builder) {
                        return $er
                            ->createQueryBuilder("e")
                            ->orderBy("e.number", "ASC")
                            ->where("e.ulid != :current")
                            ->setParameter("current", $builder->getData()->getUlid(), "ulid")
                            ;
                    },
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                    'empty_data' => null,
                    'by_reference' => true,
                    "multiple" => false,
                    "required" => false,
                    "placeholder" => "Empty",
                ])
                ->add("children", EntityType::class, [
                    "label" => "Children plasmids",
                    "class" => Plasmid::class,
                    "query_builder" => function (EntityRepository $er) use ($builder) {
                        return $er
                            ->createQueryBuilder("e")
                            ->orderBy("e.number", "ASC")
                            ->where("e.ulid != :current")
                            ->setParameter("current", $builder->getData()->getUlid(), "ulid")
                        ;
                    },
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                    'empty_data' => [],
                    "multiple" => true,
                    'by_reference' => false,
                    "required" => false,
                    "placeholder" => "Empty",
                ])
                ->add("sequence", TextareaType::class, [
                    "label" => "Sequence",
                    "help" => "The plasmid sequence (5' to 3').",
                    "required" => false,
                ])
                ->add("sequenceAnnotations", SequenceAnnotationCollectionType::class, [
                    "label" => "Sequence annotations",
                ])
            )
            ->add(
                $builder->create("_attachments", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Attachments",
                ])
                ->add("importSequence", CheckboxType::class, [
                    "label" => "Import sequence from uploaded genbank file",
                    "help" => "If turned on, the sequence will be imported from the genbank file if it has been freshly uploaded.",
                    "mapped" => false,
                    "empty_data" => null,
                    "required" => false,
                ])
                ->add("importFeatures", CheckboxType::class, [
                    "label" => "Import features from uploaded genbank file",
                    "help" => "If turned on, sequence features will be imported from the genbank file if it has been freshly uploaded.",
                    "mapped" => false,
                    "empty_data" => null,
                    "required" => false,
                ])
                ->add("attachments", AttachmentCollectionType::class, [
                    "label" => "Attachments",
                ])
            )
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Plasmid::class,
        ]);

        parent::configureOptions($resolver);
    }
}