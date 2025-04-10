<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Epitope;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\BasicType\EnumeratedType;
use App\Form\BasicType\FancyEntityType;
use App\Form\BasicType\FormGroupType;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\CompositeType\PrivacyAwareType;
use App\Genie\Enums\OligoTypeEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SubstanceType<Oligo>
 */
class OligoType extends AbstractType
{
    public function getParent(): string
    {
        return SubstanceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentId = $builder->getData()->getUlid();

        $builder
            ->add(
                $builder->create("general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General information"
                ])
                ->add("shortName", EnumeratedType::class, [
                    "label" => "Short name",
                    "help" => "Short name of the oligo, must be unique among all substances.",
                    "required" => true,
                    "enumeration_type" => "oligo",
                ])
                ->add("longName", TextType::class, [
                    "label" => "Name",
                    "help" => "A longer, more descriptive name.",
                ])
                ->add("comment", TextareaType::class, [
                    "label" => "Comment",
                    "help" => "A short comment of the purpose of this oligo, or any other information.",
                    "required" => false,
                ])
                ->add("oligoTypeEnum", EnumType::class, [
                    "label" => "Oligo type",
                    "class" => OligoTypeEnum::class,
                    "required" => false,
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
                ->add("sequence", TextareaType::class, [
                    "label" => "Sequence",
                    "help" => "The DNA oligomer sequence (5' to 3'). Add modified bases using the square bracket notation (e.g., [Hexylamine]ATG[FAM])",
                    "required" => false,
                ])
                ->add(
                    $builder->create("_conjugate", FormGroupType::class, [
                        "label" => "Conjugate",
                        "inherit_data" => true,
                    ])
                    ->add("startConjugate", FancyEntityType::class, [
                        "class" => Substance::class,
                        "query_builder" => function (EntityRepository $er) use ($currentId) {
                            return $er->createQueryBuilder("e")
                                ->addOrderBy("e.shortName", "ASC")
                                ->andWhere("e.ulid != :currentId")
                                ->setParameter("currentId", $currentId)
                                ;
                        },
                        "group_by" => function (Substance $e) {
                            return match($e::class) {
                                Antibody::class => "Antibody",
                                Chemical::class => "Compound",
                                Oligo::class => "Oligo",
                                Protein::class => "Protein",
                                default => "Other",
                            };
                        },
                        'empty_data' => null,
                        "placeholder" => "Empty",
                        "required" => false,
                        "multiple" => false,
                        "allow_empty" => true,
                    ])
                    ->add("endConjugate", FancyEntityType::class, [
                        "class" => Substance::class,
                        "query_builder" => function (EntityRepository $er) use ($currentId) {
                            return $er->createQueryBuilder("e")
                                ->addOrderBy("e.shortName", "ASC")
                                ->andWhere("e.ulid != :currentId")
                                ->setParameter("currentId", $currentId)
                                ;
                        },
                        "group_by" => function (Substance $e) {
                            return match($e::class) {
                                Antibody::class => "Antibody",
                                Chemical::class => "Compound",
                                Oligo::class => "Oligo",
                                Protein::class => "Protein",
                                default => "Other",
                            };
                        },
                        'empty_data' => null,
                        "placeholder" => "Empty",
                        "required" => false,
                        "multiple" => false,
                        "allow_empty" => true,
                    ])
                )
                ->add("molecularMass", NumberType::class, [
                    "label" => "Molecular mass [Da]",
                    "required" => false,
                ])
                ->add("extinctionCoefficient", NumberType::class, [
                    "label" => "Molar extinction coefficient ε [mM⁻¹ cm⁻¹]",
                    "help" => "Extinction coefficient, as given by the manufacturer or as calculated. Must be in [mM⁻¹ cm⁻¹]",
                    "required" => false,
                ])
                ->add("epitopes", FancyEntityType::class, [
                    "label" => "Epitopes",
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
            "data_class" => Oligo::class,
        ]);

        parent::configureOptions($resolver);
    }
}