<?php
declare(strict_types=1);

namespace App\Form\StockKeeping;

use App\Entity\DoctrineEntity\Storage\Rack;
use App\Form\SaveableType;
use App\Genie\Enums\Availability;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class QuickOrderType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("lotIdentifier", TextType::class, [
                "label" => "Lot#",
                "required" => false,
                "constraints" => [
                ],
            ])
            ->add("times", IntegerType::class, [
                "label" => "# times",
                "required" => true,
                "constraints" => [
                    new Assert\Range(min: 0),
                    new Assert\NotBlank(),
                ],
            ])
            ->add("numberOfUnits", IntegerType::class, [
                "label" => "Units per lot",
                "required" => true,
                "empty_data" => 1,
                "constraints" => [
                    new Assert\Range(min: 0)
                ],
            ])
            ->add("unitSize", IntegerType::class, [
                "label" => "Pcs per unit",
                "required" => true,
                "empty_data" => 1,
                "constraints" => [
                    new Assert\Range(min: 0)
                ],
            ])
            ->add("priceValue", MoneyType::class, options: [
                "label" => "Price",
                "required" => false,
                'empty_data' => null,
                "currency" => false,
                "divisor" => 1000,
            ])
            ->add("priceCurrency", CurrencyType::class, options: [
                "label" => "Currency",
                "required" => false,
                "empty_data" => "CHF",
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
            ->add("status", EnumType::class, [
                "label" => "Status",
                "required" => true,
                "class" => Availability::class,
                "constraints" => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add("location", EntityType::class, [
                "class" => Rack::class,
                "label" => "Location",
                "help" => "Typical location this consumable can be found. Will be used as default for lots and can be customized for each lot.",
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
        ;

        parent::buildForm($builder, $options);
    }
}