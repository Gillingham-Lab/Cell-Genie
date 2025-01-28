<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\Epitope;
use App\Form\BasicType\EnumeratedType;
use App\Form\BasicType\FancyEntityType;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\CompositeType\PrivacyAwareType;
use App\Form\CompositeType\VendorFieldType;
use App\Form\NameType;
use App\Form\Traits\VocabularyTrait;
use App\Genie\Enums\AntibodyType as AntibodyTypeEnum;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SubstanceType<Antibody>
 */
class AntibodyType extends SubstanceType
{
    /**
     * @phpstan-use VocabularyTrait<Antibody>
     */
    use VocabularyTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("general", NameType::class, [
                    "inherit_data" => true,
                    "label" => "General information"
                ])
                ->add("number", EnumeratedType::class, [
                    "label" => "Number",
                    "help" => "An internal identifier (such as AK001). Check with existing antibodies which numbers are free.",
                    "required" => true,
                    "enumeration_type" => "antibody",
                ])
                ->add("rrid", TextType::class, [
                    "label" => "#RRID",
                    "help" => "Most commercial available antibodies have a research resource identifier, and some journals require or encourage the usage of them in the supporting identifer.",
                    "required" => false,
                ])
                ->add("type", ChoiceType::class, [
                    "label" => "Type",
                    "help" => "Mark if the antibody is primary or secondary",
                    "required" => true,
                    "choices" => [
                        "Primary" => AntibodyTypeEnum::Primary,
                        "Secondary" => AntibodyTypeEnum::Secondary,
                    ],
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership",
                ])
            )
            ->add(
                $builder->create("structure", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Structure",
                ])
                ->add("clonality", ... $this->getTextOrChoiceOptions("clonality", [
                    "label" => "Clonality",
                    "help" => "Is the antibody monoclonal, or polyclonal?",
                    "placeholder" => "Unknown",
                    "empty_data" => null,
                ]))
                ->add("detection", TextType::class, [
                    "label" => "Label",
                    "help" => "Give information if this antibody has some type of tag (ex/em wavelengths, HRP, ...). Leave empty if there is no reporter.",
                    "empty_data" => null,
                    "required" => false,
                ])
                ->add("epitopes", FancyEntityType::class, [
                    "label" => "Has epitopes",
                    "class" => Epitope::class,
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
                    "allow_empty" => true,
                ])
                ->add("epitopeTargets", FancyEntityType::class, [
                    "label" => "Targets epitopes",
                    "class" => Epitope::class,
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
                    "allow_empty" => true,
                ])
            )
            ->add(
                $builder->create("usage", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Usage",
                ])
                ->add("usage", TextType::class, [
                    "label" => "Usage",
                    "help" => "Highlight the purpose for this antibody (WB, IP, IH, ...). Highlight with 'Only' if the antibody is for a specific purpose.",
                    "required" => false,
                ])
                ->add("dilution", CKEditorType::class, [
                    "label" => "Dilution suggestions",
                    "help" => "Oftentimes, vendor propose antibody dilutions for specific applications.",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("validatedInternally", CheckboxType::class, [
                    "label" => "Validated internally",
                    "help" => "Mark this if you successfully used the antibody for the intended usage.",
                    "required" => false,
                ])
                ->add("validatedExternally", CheckboxType::class, [
                    "label" => "Validated externally",
                    "help" => "Mark this if you know a paper using this antibody.",
                    "required" => false,
                ])
                ->add("externalReference", TextType::class, [
                    "label" => "External reference",
                    "help" => "If this antibody has been validated externally, please give here a citation (DOI in the style of 'doi:10.1337/0815' preferred)",
                    "required" => false,
                ])
                ->add("storageTemperature", IntegerType::class, [
                    "label" => "Storage at [°C]",
                    "help" => "Note down a storage temperature between -200 and 25 °C. Commonly, -20 °C is used.",
                ])
            )
            ->add(
                $builder->create("vendor", VendorFieldType::class, [
                    "inherit_data" => true,
                    "label" => "Vendor",
                ])
            )
            ->add(
                $builder->create("_attachments", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Attachments",
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
            "data_class" => Antibody::class,
            "show_lots" => false,
        ]);

        $resolver->addAllowedTypes("show_lots", "bool");

        parent::configureOptions($resolver);
    }
}