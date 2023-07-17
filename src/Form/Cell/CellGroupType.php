<?php
declare(strict_types=1);

namespace App\Form\Cell;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Tissue;
use App\Form\SaveableType;
use App\Form\Traits\VocabularyTrait;
use App\Repository\Cell\CellRepository;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CellGroupType extends SaveableType
{
    use VocabularyTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository,
        private CellRepository $cellRepository,
    ) {
    }

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
                    "help" => "The number that should be used to reference this cell group. Use the cellosauros ID if applicable.",
                ])
                ->add("name", TextType::class, [
                    "label" => "Cell group name",
                    "required" => true,
                    "help" => "The name of the cell group can either be abstract (such as 'Human' or 'Engineered') for supersets, but should turn into official cell names further down if cells are added to it."
                ])
                ->add("cellosaurusId", TextType::class, [
                    "label" => "Cellosaurus ID",
                    "required" => false,
                    "help" => "Expasy has a cell database with many previously described cell lines, including many more information on them. If its not a custom cell line, try to find the corresponding entry there.",
                ])
                ->add("rrid", TextType::class, [
                    "label" => "RRID",
                    "required" => false,
                ])
                ->add("cultureType", ... $this->getTextOrChoiceOptions("cultureType", [
                    "label" => "Culture type",
                    "required" => false,
                    "empty_data" => "",
                    "help" => "Set the culture type (if known). Are the cells adherent or suspension? In clusters? Both?",
                ]))
                ->add("parent", EntityType::class, [
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
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("children", EntityType::class, [
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
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
            )
            ->add(
                $builder->create("__groupOrigin", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Cell information",
                ])
                ->add("organism", EntityType::class, [
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
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("morphology", EntityType::class, [
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
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("tissue", EntityType::class, [
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
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
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
                ])
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