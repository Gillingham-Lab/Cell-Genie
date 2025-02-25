<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Entity\DoctrineEntity\Vocabulary\Organism;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FancyEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class ProteinSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ])
            ->add("originOrganism", FancyEntityType::class, [
                "class" => Organism::class,
                "label" => "Origin organism",
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
            ])
            ->add("hasAntibodies", FancyChoiceType::class, [
                "label" => "Has antibodies",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}