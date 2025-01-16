<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Form\SaveableType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData
 * @extends SaveableType<TData>
 */
class SubstanceType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options["show_lots"]) {
            $builder->add(
                $builder->create("_lots", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Lots",
                ])
                ->add("lots", LotCollectionType::class, [
                    "label" => "Lots",
                    "entry_options" => [
                        "hideVendor" => $options["hide_lot_vendor"],
                    ],
                ])
            );
        }

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "show_lots" => false,
            "hide_lot_vendor" => false,
        ]);

        $resolver->addAllowedTypes("show_lots", "bool");
        $resolver->addAllowedTypes("hide_lot_vendor", "bool");

        parent::configureOptions($resolver);
    }
}