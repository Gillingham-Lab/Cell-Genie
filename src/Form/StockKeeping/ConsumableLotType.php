<?php
declare(strict_types=1);

namespace App\Form\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Form\PriceType;
use App\Form\SaveableType;
use App\Form\UserEntityType;
use App\Genie\Enums\Availability;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConsumableLotType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $form = $builder->create("_general", FormType::class, [
            "inherit_data" => true,
            "label" => "General information",
        ])
            ->add("lotIdentifier", TextType::class, [
                "required" => false,
                "help" => "An internal identifier for the lot. Use this to differentiate multiple packages.",
            ])
            ->add("lotNumber", TextType::class, [
                "required" => false,
                "help" => "A vendor given identifier for the lot. Is not necessarily unique per package.",
            ])
            ->add("consumable", EntityType::class, [
                "disabled" => true,
                "required" => false,
                "class" => Consumable::class,
            ])
            ->add("unitSize", IntegerType::class, [
                "label" => "Pieces per package unit",
                "help" => "For example, if a typical order comes with 5 packs, each with 200 tubes, then this should be 200."
            ])
            ->add("numberOfUnits", IntegerType::class, [
                "label" => "Number of units per package",
                "help" => "If a typical order comes with 5 packs, then this should be 5. However, if a package only individual units, this should be 1.",
            ])
            ->add("pricePerPackage", PriceType::class, [
                "label" => "Price per package",
            ])
            ->add("location", EntityType::class, [
                "class" => Rack::class,
                "label" => "Location",
                "help" => "Location of this lot.",
                "choice_label" => function(Rack $rack) { return $rack->getPathName(); },
                "choice_value" => function(?Rack $rack) { return $rack?->getUlid()?->toBase58(); },
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder("r")
                        ->select("r")
                        ->addSelect("b")
                        ->leftJoin("r.boxes", "b")
                        ->groupBy("r.ulid")
                        ->addGroupBy("b.ulid")
                        ;
                },
                'empty_data' => [],
                'by_reference' => false,
                "placeholder" => "Empty",
                "required" => true,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "false",
                ],
            ])
            ->add("boughtBy", UserEntityType::class, [
                "label" => "Bought by",
                "required" => false,
            ])
            ->add("boughtOn", DateType::class, [
                "label" => "Acquired on",
                "help" => "When we placed the order",
                "required" => true,
                "html5" => true,
                "empty_data" => "",
                "placeholder" => "Set a date",
                "widget" => "single_text",
            ])
            ->add("availability", EnumType::class, [
                "label" => "Status",
                "required" => true,
                "class" => Availability::class,
                "constraints" => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add("arrivedOn", DateType::class, [
                "label" => "Arrived on",
                "help" => "When the order arrived",
                "required" => false,
                "html5" => true,
                "empty_data" => "",
                "placeholder" => "Set a date",
                "widget" => "single_text",
            ])
            ->add("openedOn", DateType::class, [
                "label" => "Opened on",
                "help" => "When the lot was opened",
                "required" => false,
                "html5" => true,
                "empty_data" => "",
                "placeholder" => "Set a date",
                "widget" => "single_text",
            ])
        ;

        /** @var ConsumableLot $entity */
        $entity = $builder->getData();

        if ($entity->getConsumable()->isConsumePackage()) {
            $form->add("unitsConsumed", IntegerType::class, [
                "label" => "Number of units consumed",
            ]);
        } else {
            $form->add("piecesConsumed", IntegerType::class, [
                "label" => "Number of pieces consumed",
            ]);
        }


        $builder->add($form);

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ConsumableLot::class,
        ]);

        parent::configureOptions($resolver);
    }
}