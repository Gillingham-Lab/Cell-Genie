<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Entity\DoctrineEntity\Epitope;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FancyEntityType;
use App\Genie\Enums\AntibodyType;
use App\Twig\Components\Live\SubstanceTable;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 * @see SubstanceTable
 */
class AntibodySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("antibodyNumber", TextType::class, [
                "label" => "Antibody Nr.",
                "required" => false,
            ])
            ->add("antibodyType", EnumType::class, [
                "class" => AntibodyType::class,
                "required" => false,
            ])
            ->add("antibodyName", TextType::class, [
                "label" => "Name",
                "required" => false,
            ])
            ->add("rrid", TextType::class, [
                "label" => "RRID",
                "required" => false,
            ])
            ->add("hasEpitope", FancyEntityType::class, [
                "label" => "Has epitopes",
                "class" => Epitope::class,
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
            ->add("targetsEpitope", FancyEntityType::class, [
                "label" => "Targets epitope",
                "class" => Epitope::class,
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
            ->add("hasAvailableLots", FancyChoiceType::class, [
                "label" => "Has available lots",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("internallyValidated", FancyChoiceType::class, [
                "label" => "Internally validated",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("externallyValidated", FancyChoiceType::class, [
                "label" => "Externally Validated",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("productNumber", TextType::class, [
                "label" => "Product number",
                "required" => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, "onPreSetData"]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $formData = $event->getData();

        if (isset($formData["antibodyType"]) and is_string($formData["antibodyType"])) {
            $formData["antibodyType"] = AntibodyType::from($formData["antibodyType"]);
        }

        $event->setData($formData);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}
