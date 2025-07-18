<?php
declare(strict_types=1);

namespace App\Form\Cell;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\Vocabulary\Morphology;
use App\Entity\DoctrineEntity\Vocabulary\Organism;
use App\Entity\DoctrineEntity\Vocabulary\Tissue;
use App\Form\BasicType\FancyEntityType;
use App\Form\SaveableType;
use App\Form\Traits\VocabularyTrait;
use App\Repository\Vocabulary\VocabularyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<CellGroup>
 */
class CellGroupType extends SaveableType
{
    /**
     * @phpstan-use VocabularyTrait<CellGroup>
     */
    use VocabularyTrait;

    public function __construct(
        private readonly VocabularyRepository $vocabularyRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entity = $builder->getData();

        $builder
            ->add(
                $builder->create("__generalGroup", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General group information",
                ])
                ->add("number", TextType::class, [
                    "label" => "Cell number",
                    "required" => true,
                    "help" => "The number that should be used to reference this cell group. Use the cellosaurus ID if applicable.",
                ])
                ->add("name", TextType::class, [
                    "label" => "Cell group name",
                    "required" => true,
                    "help" => "For conceptual groups, it can be abstract (such as 'Human' or 'Engineered') for supersets, but should turn into official cell names further down if cells are added to it (not a cellosaurus number). Use the preferred name on cellosaurus!",
                ])
                ->add("cellosaurusId", TextType::class, [
                    "label" => "Cellosaurus ID",
                    "required" => false,
                    "help" => "Expasy has a cell database with many previously described cell lines, including many more information on them. If its not a custom cell line, try to find the corresponding entry there.",
                ])
                ->add("rrid", TextType::class, [
                    "label" => "RRID",
                    "required" => false,
                    "help" => "Can usually be found on Cellosaurus as well and is often equivalent to Cellosaurus Acession number. Alternatively, check here: https://scicrunch.org/resources/data/source/SCR_013869-1/search",
                ])
                ->add("cultureType", ... $this->getTextOrChoiceOptions("cultureType", [
                    "label" => "Culture type",
                    "required" => false,
                    "empty_data" => "",
                    "help" => "Set the culture type (if known). Are the cells adherent or suspension? In clusters? Both?",
                ]))
                ->add("parent", FancyEntityType::class, [
                    "label" => "Parent",
                    "help" => "A parent cell group. For organisation of cell group only, and it should not imply direct origin. For that, cell line should be used instead.",
                    "required" => false,
                    "class" => CellGroup::class,
                    "query_builder" => function (EntityRepository $er) use ($entity) {
                        $qb = $er->createQueryBuilder("cg")
                            ->addOrderBy("cg.number", "ASC")
                            ->addOrderBy("cg.name", "ASC")
                        ;

                        if ($entity->getId()) {
                            $qb = $qb->andWhere("cg.id != :id")
                                ->setParameter("id", $entity->getId(), "ulid");
                        }

                        return $qb;
                    },
                    "empty_data" => null,
                    "placeholder" => "Select a cell group",
                    "multiple" => false,
                    "allow_empty" => true,
                ])
                ->add("children", FancyEntityType::class, [
                    "label" => "Children",
                    "help" => "A list of child groups. For organisation of cell groups only, and it should not imply direct origin. For that, cell line should be used instead.",
                    "required" => false,
                    "class" => CellGroup::class,
                    "query_builder" => function (EntityRepository $er) use ($entity) {
                        $qb = $er->createQueryBuilder("cg")
                            ->addOrderBy("cg.number", "ASC")
                            ->addOrderBy("cg.name", "ASC")
                        ;

                        if ($entity->getId()) {
                            $qb = $qb->andWhere("cg.id != :id")
                                ->setParameter("id", $entity->getId(), "ulid");
                        }

                        return $qb;
                    },
                    "by_reference" => false,
                    "empty_data" => [],
                    "placeholder" => "Select cell groups",
                    "multiple" => true,
                    "allow_empty" => true,
                ]),
            )
            ->add(
                $builder->create("__groupOrigin", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Cell information",
                ])
                ->add("organism", FancyEntityType::class, [
                    "label" => "Organism",
                    "required" => false,
                    "class" => Organism::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("e")
                            ->addOrderBy("e.name", "ASC")
                        ;
                    },
                    "empty_data" => null,
                    "placeholder" => "Select an organism",
                    "multiple" => false,
                    "allow_empty" => true,
                ])
                ->add("morphology", FancyEntityType::class, [
                    "label" => "Morphology",
                    "required" => false,
                    "class" => Morphology::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("e")
                            ->addOrderBy("e.name", "ASC")
                        ;
                    },
                    "empty_data" => null,
                    "placeholder" => "Select a morphology",
                    "multiple" => false,
                    "allow_empty" => true,
                ])
                ->add("tissue", FancyEntityType::class, [
                    "label" => "Tissue type",
                    "required" => false,
                    "class" => Tissue::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("e")
                            ->addOrderBy("e.name", "ASC")
                        ;
                    },
                    "empty_data" => null,
                    "placeholder" => "Select a tissue type",
                    "multiple" => false,
                    "allow_empty" => true,
                ])
                ->add("isCancer", CheckboxType::class, [
                    "label" => "Cancer?",
                    "help" => "Check if the cell is a cancer cell line (or based on one).",
                    "required" => false,
                    "empty_data" => null,
                ])
                ->add("age", TextType::class, [
                    "label" => "Age",
                    "required" => false,
                    "help" => "Age of the cell donor. Can also be something like 'adult', 'embryo', or more specifically '6 months', '44 years'",
                ])
                ->add("sex", ... $this->getTextOrChoiceOptions("sex", [
                    "label" => "Sex",
                    "required" => false,
                    "help" => "Genotype (not the phenotypical gender) of the cell. Mainly used to describe the chromosomal configuration. XXY is a different genotype than XX.",
                ]))
                ->add("ethnicity", ... $this->getTextOrChoiceOptions("ethnicity", [
                    "label" => "Ethnicity",
                    "required" => false,
                    "help" => "Ethnicity, can be interesting as some genotypical features are more common among some ethnicities.",
                ]))
                ->add("disease", TextType::class, [
                    "label" => "Disease",
                    "required" => false,
                    "help" => "Disease the cell originates from, if any.",
                ]),
            )
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => CellGroup::class,
        ]);

        parent::configureOptions($resolver);
    }
}
