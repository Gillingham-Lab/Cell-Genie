<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Form\NameType;
use App\Form\SaveableType;
use App\Form\Traits\VocabularyTrait;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProteinType extends SaveableType
{
    use VocabularyTrait;

    public function __construct(
        private VocabularyRepository $vocabularyRepository
    ) {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder->create("general", NameType::class, [
                    "inherit_data" => true,
                    "label" => "General information"
                ])
                ->add("proteinAtlasUri", UrlType::class, [
                    "label" => "Protein Atlas",
                    "help" => "A link to the protein atlas entry",
                    "required" => false,
                ])
            )
            ->add(
                $builder->create("structure", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Structure",
                ])
                ->add("fastaSequence", TextareaType::class, [
                    "label" => "Sequence",
                    "help" => "The DNA oligomer sequence (5' to 3'). Add modified bases using the square bracket notation (e.g., [Hexylamine]ATG[FAM])",
                ])
                ->add("proteinType", ... $this->getTextOrChoiceOptions("proteinType", options: [
                    "label" => "Protein type",
                    "help" => "Specify the type of the protein (wildtype, point mutant, isoform ...). Additional context is given by the parent.",
                    "placeholder" => "Choose ..."
                ]))
                ->add("mutation", TextType::class, [
                    "label" => "Mutation",
                    "help" => "In the form of G12C, for example. Make sure the sequence is correct.",
                    "required" => false,
                ])
            )
            ->add(
                $builder->create("relations", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Relations",
                ])
                ->add("children", EntityType::class, [
                    "class" => Protein::class,
                    "label" => "Children proteins",
                    "help" => "Add any protein that can be regarded as a derivative from this protein.",
                    "query_builder" => function (EntityRepository $er) use ($builder) {
                        return $er->createQueryBuilder("p")
                            ->addOrderBy("p.shortName", "ASC")
                            ->where("p.ulid != :ulid")
                            ->setParameter("ulid", $builder->getData()->getUlid(), "ulid");
                    },
                    "multiple" => true,
                    'empty_data' => [],
                    "placeholder" => "Empty",
                    "required" => false,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("parents", EntityType::class, [
                    "class" => Protein::class,
                    "label" => "Parent proteins",
                    "help" => "Add any protein that can be regarded as a 'parent' of this protein.",
                    "query_builder" => function (EntityRepository $er) use ($builder) {
                        return $er->createQueryBuilder("p")
                            ->addOrderBy("p.shortName", "ASC")
                            ->where("p.ulid != :ulid")
                            ->setParameter("ulid", $builder->getData()->getUlid(), "ulid");
                    },
                    "multiple" => true,
                    'empty_data' => [],
                    "placeholder" => "Empty",
                    "required" => false,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
            )
        ;
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Protein::class,
        ]);

        parent::configureOptions($resolver);
    }
}