<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\Box;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Lot;
use App\Entity\User;
use App\Entity\Vendor;
use App\Form\SaveableType;
use App\Form\VendorType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class LotType extends SaveableType
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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
                ->add("boughtOn", DateType::class, options: [
                    "widget" => "single_text",
                    "label" => "Bought on (or made on)",
                ])
                ->add("boughtBy", EntityType::class, options: [
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
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
                ])
                ->add("openedOn", DateType::class, options: [
                    "widget" => "single_text",
                    "label" => "Opened on",
                ])
                ->add("comment", TextareaType::class, options: [
                    "label" => "Comment",
                    "help" => "Anything what you think is important. Important impurities?",
                    "required" => false,
                ])
            )
            ->add(
                $builder->create("storage", FormType::class, options: [
                    "inherit_data" => true,
                    "label" => "Storage",
                ])
                ->add("box", EntityType::class, options: [
                    "class" => Box::class,
                    "label" => "Storage location",
                    "help" => "Which box is the Aliquot located in",

                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder("b")
                            ->addOrderBy("b.name", "ASC");
                    },
                    "group_by" => function(Box $choice, $key, $value) {
                        return ($choice->getRack());
                    },
                    'empty_data' => null,
                    "placeholder" => "Empty",
                    "required" => false,
                    "attr"  => [
                        "class" => "gin-fancy-select",
                        "data-allow-empty" => "true",
                    ],
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
                    "required" => true,
                ])
                ->add("aliquoteSize", TextType::class, options: [
                    "label" => "Size of each aliquot",
                    "required" => false,
                ])
            )
        ;

        if ($options["hideVendor"] !== true) {
            $builder
                ->add(
                    $builder->create("vendor", VendorType::class, options: [
                        "inherit_data" => true,
                        "label" => "Vendor"
                    ])
                )
            ;
        }

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Lot::class,
            "hideVendor" => false,
        ]);

        $resolver->setAllowedTypes("hideVendor", "bool");

        parent::configureOptions($resolver);
    }
}
