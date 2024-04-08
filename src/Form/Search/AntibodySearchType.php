<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Entity\Epitope;
use App\Genie\Enums\AntibodyType;
use App\Repository\Substance\UserGroupRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

            ->add("hasEpitope", EntityType::class, [
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
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
            ->add("targetsEpitope", EntityType::class, [
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
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
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
            ->add("internallyValidated", ChoiceType::class, [
                "label" => "Internally validated",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
            ->add("externallyValidated", ChoiceType::class, [
                "label" => "Externally Validated",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}