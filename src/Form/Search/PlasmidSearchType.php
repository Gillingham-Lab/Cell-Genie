<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Entity\DoctrineEntity\Epitope;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Vocabulary\Organism;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FancyEntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class PlasmidSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("number", TextType::class, [
                "label" => "Number",
                "required" => false,
            ])
            ->add("shortName", TextType::class, [
                "label" => "Short name",
                "required" => false,
            ])
            ->add("anyName", TextType::class, [
                "label" => "Name",
                "required" => false,
            ])
            ->add("sequence", TextType::class, [
                "label" => "Sequence",
                "required" => false,
            ])
            ->add("hasAvailableLots", FancyChoiceType::class, [
                "label" => "Has available lots",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
                "allow_empty" => true,
            ])
            ->add("growthResistance", TextType::class, [
                "label" => "Growth resistance",
                "required" => false,
            ])
            ->add("expressionResistance", TextType::class, [
                "label" => "Expression resistance",
                "required" => false,
            ])
            ->add("expressionOrganism", FancyEntityType::class, [
                "class" => Organism::class,
                "label" => "Expression host",
                "required" => false,
                "choice_value" => function (Organism|null|string $entity) {
                    if (is_string($entity)) {
                        return (int)$entity;
                    } else {
                        return $entity?->getId();
                    }
                },
                "placeholder" => "Empty",
                "multiple" => false,
                "allow_empty" => true,
            ])
            ->add("expressesProtein", FancyChoiceType::class, [
                "label" => "Expresses a protein",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
                "allow_empty" => true,
            ])
            ->add("expressedProtein", FancyEntityType::class, [
                "label" => "Expressed protein",
                "class" => Protein::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("e")
                        ->addOrderBy("e.shortName", "ASC")
                        ;
                },
                "choice_value" => function (Epitope|null|string $entity) {
                    if (is_string($entity)) {
                        return $entity;
                    } else {
                        return $entity?->getId()?->toRfc4122();
                    }
                },
                "placeholder" => "Empty",
                "required" => false,
                "multiple" => false,
                "allow_empty" => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}