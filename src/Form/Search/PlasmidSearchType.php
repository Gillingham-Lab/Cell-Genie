<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Epitope;
use App\Entity\Organism;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlasmidSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("number", TextType::class, [
                "label" => "Short name",
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
            ->add("hasAvailableLots", ChoiceType::class, [
                "label" => "Has available lots",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("growthResistance", TextType::class, [
                "label" => "Growth resistance",
                "required" => false,
            ])
            ->add("expressionResistance", TextType::class, [
                "label" => "Expression resistance",
                "required" => false,
            ])
            ->add("expressionOrganism", EntityType::class, [
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
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
            ->add("expressesProtein", ChoiceType::class, [
                "label" => "Expresses a protein",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("expressedProtein", EntityType::class, [
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
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}