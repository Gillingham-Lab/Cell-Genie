<?php
declare(strict_types=1);

namespace App\Form\StockKeeping;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Entity\Rack;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\LongNameType;
use App\Form\SaveableType;
use App\Form\User\PrivacyAwareType;
use App\Form\VendorType;
use App\Form\VisualisationType;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsumableType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("_general", LongNameType::class, [
                    "inherit_data" => true,
                    "label" => "General information",
                ])
                ->add("comment", CKEditorType::class, [
                    "label" => "Comment",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("category", EntityType::class, [
                    "label" => "Category",
                    "class" => ConsumableCategory::class,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                    "group_by" => function ($e) {
                        return $e->getParent()?->getLongName();
                    },
                    'empty_data' => null,
                    'by_reference' => true,
                    "multiple" => false,
                    "required" => false,
                    "placeholder" => "Empty",
                ])
                ->add("productNumber", TextType::class, [
                    "label" => "Product Number",
                    "help" => "Can be the same as the vendor product number, but could differ if the vendor is only reselling (such as Brunschwig)"
                ])
                ->add("unitSize", IntegerType::class, [
                    "label" => "Pieces per package unit",
                    "help" => "For example, if a typical order comes with 5 packs, each with 200 tubes, then this should be 200."
                ])
                ->add("numberOfUnits", IntegerType::class, [
                    "label" => "Number of units per package",
                    "help" => "If a typical order comes with 5 packs, then this should be 5. However, if a package only individual units, this should be 1.",
                ])
                ->add("consumePackage", CheckboxType::class, [
                    "label" => "Consume full unit?",
                    "help" => "If activated, the consume button in a lot will consume a full unit instead of a specific amount of pieces. Turn it on 
                        if you expect people to take out a full bag each time from a common stock.",
                    "required" => false,
                    "empty_data" => null,
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
                ->add("instruments", EntityType::class, [
                    "label" => "Instruments",
                    "help" => "Choose instruments that use this consumable. The consumables will then appear on the instruments page.",
                    "class" => Instrument::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("i")
                            // Show ony main instruments
                            ->andWhere("i.parent is null")
                            // Sort by instrument ID
                            ->addOrderBy("i.instrumentNumber", "ASC")
                        ;
                    },
                    "label_html" => true,
                    "choice_label" => function (Instrument $instrument): string {
                        return "{$instrument->getInstrumentNumber()} | {$instrument->getLongName()}";
                    },
                    'empty_data' => [],
                    'by_reference' => false,
                    "placeholder" => "Empty",
                    "required" => false,
                    "multiple" => true,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership"
                ])
            )
            ->add(
                $builder->create("_visualisation", VisualisationType::class, [
                    #"inherit_data" => true,
                    "label" => "Visualisation",
                    "empty_data" => null,
                    "required" => false,
                ])
            )
            ->add(
                $builder->create("_ordering", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Stock management and Ordering",
                ])
                ->add("_vendor", VendorType::class, [
                    "inherit_data" => true,
                    "label" => "Vendor",
                ])
                ->add("idealStock", NumberType::class, [
                    "label" => "Ideal stock",
                    "help" => "If the option 'consume package' is turned on, the warning is displayed if the number of packages is less or equal that number. If not, it is the number of pieces."
                ])
                ->add("orderLimit", NumberType::class, [
                    "label" => "Minimum number before ordering is recommended",
                    "help" => "If the option 'consume package' is turned on, the warning is displayed if the number of packages is less or equal that number. If not, it is the number of pieces."
                ])
                ->add("criticalLimit", NumberType::class, [
                    "label" => "Absolute minimum before ordering is required",
                    "help" => "If the option 'consume package' is turned on, the warning is displayed if the number of packages is less or equal that number. If not, it is the number of pieces."
                ])
                ->add("pricePerPackage", NumberType::class, [
                    "label" => "Price per package",
                ])
                ->add("expectedDeliveryTime", TextType::class, [
                    "label" => "Expected delivery time",
                    "help" => "An approximate time until delivery usually arrives.",
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
            "data_class" => Consumable::class,
        ]);

        parent::configureOptions($resolver);
    }
}