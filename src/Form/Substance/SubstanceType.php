<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Form\SaveableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class SubstanceType extends AbstractType
{
    public function getParent(): string
    {
        return SaveableType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options["show_lots"]) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder, $options) {
                $event->getForm()->add(
                    $builder->create("_lots", FormType::class, [
                        "inherit_data" => true,
                        "label" => "Lots",
                        "auto_initialize" => false
                    ])
                    ->add("lots", LotCollectionType::class, [
                        "label" => "Lots",
                        "entry_options" => [
                            "hideVendor" => $options["hide_lot_vendor"],
                        ],
                        "required" => true,
                    ])->getForm()
                );
            });
        }
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