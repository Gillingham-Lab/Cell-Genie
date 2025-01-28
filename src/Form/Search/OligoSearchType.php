<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FancyEntityType;
use App\Genie\Enums\OligoTypeEnum;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class OligoSearchType extends AbstractType
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
            ->add("oligoType", EnumType::class, [
                "label" => "Oligo type",
                "required" => false,
                "class" => OligoTypeEnum::class,
            ])
            ->add("startConjugate", FancyEntityType::class, [
                "label" => "Start conjugate",
                "required" => false,
                "class" => Substance::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("e")
                        ->join(Oligo::class, "o", Join::WITH, "e.ulid = o.startConjugate")
                        ->addOrderBy("e.shortName", "ASC")
                        ;
                },
                "choice_value" => function (Substance|null|string $entity) {
                    if (is_string($entity)) {
                        return $entity;
                    } else {
                        return $entity?->getUlid()?->toRfc4122();
                    }
                },
                "placeholder" => "Empty",
                "multiple" => false,
                "allow_empty" => true,
            ])
            ->add("endConjugate", FancyEntityType::class, [
                "label" => "End conjugate",
                "required" => false,
                "class" => Substance::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("e")
                        ->join(Oligo::class, "o", Join::WITH, "e.ulid = o.endConjugate")
                        ->addOrderBy("e.shortName", "ASC")
                        ;
                },
                "choice_value" => function (Substance|null|string $entity) {
                    if (is_string($entity)) {
                        return $entity;
                    } else {
                        return $entity?->getUlid()?->toRfc4122();
                    }
                },
                "placeholder" => "Empty",
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
                "allow_empty" => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, "onPreSetData"]);
    }

    public function onPreSetData(FormEvent $event): void
    {
        $formData = $event->getData();

        if (isset($formData["oligoType"]) and is_string($formData["oligoType"])) {
            $formData["oligoType"] = OligoTypeEnum::from($formData["oligoType"]);
        }

        $event->setData($formData);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}