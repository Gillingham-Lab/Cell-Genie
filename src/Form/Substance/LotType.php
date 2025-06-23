<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\User\User;
use App\Form\BasicType\FancyEntityType;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\CompositeType\PrivacyAwareType;
use App\Form\CompositeType\VendorFieldType;
use App\Form\SaveableType;
use App\Form\Storage\BoxPositionType;
use App\Genie\Enums\Availability;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<Lot>
 */
class LotType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General",
                ])
                ->add("number", TextType::class, options: [
                    "label" => "Internal label",
                    "help" => "Usually gets appended to the substance name (eg, for AB001 it would be AB001.{number}).",
                ])
                ->add('lotNumber',TextType::class, options: [
                    "label" => "Lot number",
                    "help" => "Manufactures lot number (for publications)",
                ])
                ->add("availability", ChoiceType::class, [
                    "label" => "Type",
                    "help" => "Mark if the antibody is primary or secondary",
                    "required" => true,
                    "choices" => [
                        "Available" => Availability::Available,
                        "Ordered" => Availability::Ordered,
                        "In preparation" => Availability::InPreparation,
                        "Empty" => Availability::Empty,
                    ],
                ])
                ->add("boughtOn", DateType::class, options: [
                    "widget" => "single_text",
                    "label" => "Bought on (or made on)",
                ])
                ->add("boughtBy", FancyEntityType::class, options: [
                    "class" => User::class,
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("u")
                            ->addOrderBy("u.isActive", "DESC")
                            ->addOrderBy("u.fullName", "ASC");
                    },
                    "group_by" => function(User $choice, $key, $value) {
                        return ($choice->getIsActive() ? "Active" : "Inactive");
                    },
                    "label" => "Bought by",
                    "required" => true,
                    "allow_empty" => true,
                ])
                ->add("openedOn", DateType::class, options: [
                    "widget" => "single_text",
                    "label" => "Opened on",
                    "required" => false,
                    "html5" => true,
                    "empty_data" => "",
                ])
                ->add("comment", TextareaType::class, options: [
                    "label" => "Comment",
                    "help" => "Anything what you think is important. Important impurities?",
                    "required" => false,
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership",
                ])
            )
            ->add(
                $builder->create("storage", FormType::class, options: [
                    "inherit_data" => true,
                    "label" => "Storage",
                ])
                ->add("storageCoordinate", BoxPositionType::class, [

                ])
                ->add("amount", TextType::class, options: [
                    "label" => "Amount",
                    "help" => "Write down the amount with a unit",
                    "required" => true,
                ])
                ->add("purity", TextType::class, options: [
                    "label" => "Concentration",
                    "help" => "Write down the concentration with a unit. If not a solution, write 'neat' instead.",
                    "required" => true,
                ])
                ->add("numberOfAliquotes", IntegerType::class, options: [
                    "label" => "Number of Aliquots",
                    "required" => false,
                ])
                ->add("maxNumberOfAliquots", IntegerType::class, options: [
                    "label" => "Max Number of Aliquots",
                    "required" => false,
                ])
                ->add("aliquoteSize", TextType::class, options: [
                    "label" => "Size of each aliquot",
                    "required" => false,
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

        if ($options["hideVendor"] !== true) {
            $builder
                ->add(
                    $builder->create("vendor", VendorFieldType::class, options: [
                        "inherit_data" => true,
                        "label" => "Vendor"
                    ])
                )
            ;
        }

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Lot::class,
            "hideVendor" => false,
        ]);

        $resolver->setAllowedTypes("hideVendor", "bool");

        parent::configureOptions($resolver);
    }
}
