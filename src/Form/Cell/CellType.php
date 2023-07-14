<?php
declare(strict_types=1);

namespace App\Form\Cell;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Tissue;
use App\Form\CellularProteinCollectionType;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\SaveableType;
use App\Form\Traits\GroupOwnerTrait;
use App\Form\Traits\OwnerTrait;
use App\Form\Traits\PrivacyLevelTrait;
use App\Form\Traits\VocabularyTrait;
use App\Form\UserEntityType;
use App\Form\VendorType;
use App\Repository\Cell\CellRepository;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tienvx\UX\CollectionJs\Form\CollectionJsType;

class CellType extends SaveableType
{
    use VocabularyTrait;
    use OwnerTrait;
    use GroupOwnerTrait;
    use PrivacyLevelTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository,
        private CellRepository $cellRepository,
        private Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->createGeneralForm($builder, $options))
            ->add($this->createOriginForm($builder, $options))
            ->add($this->createEngineeringForm($builder, $options))
            ->add($this->createConditionForm($builder, $options))

            ->add(
                $builder->create("__experimentalGroup", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Experimental hints",
                ])
                    ->add("seeding", CKEditorType::class, [
                        "label" => "Seeding conditions",
                        "help" => "Detailed hints on cell seeding (cell amount, medium volume, time until confluency, wellplate). Also recommend a specific well-plate",
                        "sanitize_html" => true,
                        "required" => false,
                        "empty_data" => null,
                        "config" => ["toolbar" => "basic"],
                    ])
                    ->add("countOnConfluence", IntegerType::class, [
                        "label" => "Cell count at confluence",
                        "help" => "Try to keep the well-plate format consistent between seeding and cell seeding conditions. If you give multiple recommendations, highlight the one used for this field.",
                        "required" => false,
                    ])
                    ->add("lysing", CKEditorType::class, [
                        "label" => "Lysing conditions",
                        "help" => "If the cells need to be lysed with anything but RIPA, mention it here.",
                        "sanitize_html" => true,
                        "required" => false,
                        "empty_data" => null,
                        "config" => ["toolbar" => "basic"],
                    ])
            )
            ->add(
                $builder->create("expression", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Expressed proteins",
                ])
                    ->add("cellProteins", CollectionJsType::class, [
                        "label" => "Expressed proteins in this cell",
                        "required" => false,
                        "entry_type" => CellularProteinCollectionType::class,
                        "by_reference" => false,
                        "allow_add" => true,
                        "allow_delete" => true,
                        "allow_move_up" => true,
                        "allow_move_down" => true,
                        "call_post_add_on_init" => true,
                        "attr" => array(
                            "class" => "collection",
                        ),
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

        return $builder;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Cell::class,
            "current_cell" => null,
        ]);

        $resolver->setAllowedTypes("current_cell", ['null', Cell::class]);

        parent::configureOptions($resolver);
    }

    private function createGeneralForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        $builder = $builder
            ->create("general", FormType::class, [
                "inherit_data" => true,
                "label" => "General information",
            ])
            ->add("cellNumber", TextType::class, [
                "label" => "Cell number",
                "required" => true,
                "help" => "The cell number must be a unique identifier of the cell. It can be changed, but this will make permanent links (like from QR-Codes) to the cell invalid.",
            ])
            ->add("name", TextType::class, [
                "label" => "Cell name",
                "required" => true,
                "help" => "(Official) name of the cell line if commercially available (like 'HCT 116') or a descriptive name if the cell has been engineered ('HEK293T, MGMT-GFP, mCherry')"
            ])
            ->add("cellGroup", EntityType::class, [
                "label" => "Cell group",
                "help" => "A cell group is for collection cell lines from different origins under the same label.",
                "required" => true,
                "class" => CellGroup::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("cg")
                        ->addOrderBy("cg.name", "ASC");
                },
                "empty_data" => null,
                "placeholder" => "Select a cell group",
                "multiple" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
        ;

        $this->addOwnerField($builder, $this->security);
        $this->addGroupOwnerField($builder, $this->security);
        $this->addPrivacyLevelField($builder, $this->security);

        return $builder;
    }

    private function createOriginForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder
            ->create("origins", VendorType::class, [
                "inherit_data" => true,
                "label" => "Origins",
                "required" => false,
            ])
            ->add("price", NumberType::class, [
                "label" => "Cell line price"
            ])
            ->add("acquiredOn", DateType::class, [
                "label" => "Acquired on",
                "help" => "When did we get this specific cell line?",
                "required" => false,
                "html5" => true,
                "empty_data" => "",
                "placeholder" => "Set a date",
                "widget" => "single_text",
            ])
            ->add("boughtBy", UserEntityType::class, [
                "label" => "Bought by",
                "required" => false,
            ])
            ->add("origin", CKEditorType::class, [
                "label" => "Origins",
                "help" => "Write down a few words on the origin of the cell line. Where did we get it from? Not necessary if a vendor is given.",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("originComment", CKEditorType::class, [
                "label" => "Comment",
                "help" => "A comment on the origin.",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
        ;
    }

    private function createEngineeringForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder
            ->create("engineering", FormType::class, [
            "inherit_data" => true,
            "label" => "Engineering",
        ])
            ->add("isEngineered", CheckboxType::class, [
                "label" => "Is Engineered?",
                "help" => "Check if the cell has been engineered.",
                "required" => false,
                "empty_data" => null,
            ])
            ->add("engineer", UserEntityType::class, [
                "label" => "Engineer",
                "help" => "Which scientist has engineered this cell line?",
                "required" => false,
            ])
            ->add("parent", EntityType::class, [
                "label" => "Parent",
                "help" => "From which cell line has this one been derived? Also important if the cell is a known derivative (but not self-made), like HEK293T originates from HEK293.",
                "required" => false,
                "class" => Cell::class,
                "query_builder" => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder("c")
                        ->addOrderBy("c.cellNumber", "ASC")
                        ->addOrderBy("c.name", "ASC")
                    ;

                    if ($options["current_cell"]) {
                        $qb = $qb
                            ->where("c.id != :currentId")
                            ->setParameter("currentId", $options["current_cell"]->getId())
                        ;
                    }

                    return $qb;
                },
                "empty_data" => null,
                "placeholder" => "Select a cell line",
                "multiple" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
            ->add("engineeringPlasmid", EntityType::class, [
                "class" => Plasmid::class,
                "label" => "Plasmid",
                "help" => "Which plasmid has been used to construct this cell line?",
                "required" => false,
                "query_builder" => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder("p")
                        ->addOrderBy("p.number", "ASC")
                        ->addOrderBy("p.shortName", "ASC")
                    ;

                    return $qb;
                },
                "empty_data" => null,
                "placeholder" => "Select a cell line",
                "multiple" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
            ->add("engineeringDescription", CKEditorType::class, [
                "label" => "Engineering description",
                "help" => "A brief description on the construction of this cell line.",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
        ;
    }

    private function createConditionForm(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $builder
            ->create("__conditionGroup", FormType::class, [
                "inherit_data" => true,
                "label" => "Culturing conditions",
            ])
            ->add("medium", CKEditorType::class, [
                "label" => "Cell medium",
                "help" => "Recommendation on which cell medium should be used",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("trypsin", CKEditorType::class, [
                "label" => "Trypsin",
                "help" => "Recommendation on which trypsin should be used.",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("splitting", CKEditorType::class, [
                "label" => "Splitting",
                "help" => "Recommendation splitting procedure (including a timeline when the cells would be ready for treatments)",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("freezing", CKEditorType::class, [
                "label" => "Freezing conditions",
                "help" => "Recommended freezing conditions",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("thawing", CKEditorType::class, [
                "label" => "Thawing conditions",
                "help" => "Do these cells need any special thawing procedure?",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("cultureConditions", CKEditorType::class, [
                "label" => "Incubator conditions",
                "help" => "Growth conditions for incubator (does it need more CO2? Should be grown without oxygen?)",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
        ;
    }
}