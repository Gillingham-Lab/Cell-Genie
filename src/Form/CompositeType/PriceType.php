<?php
declare(strict_types=1);

namespace App\Form\CompositeType;

use App\Entity\Embeddable\Price;
use App\Form\BasicType\FancyCurrencyType;
use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Price>
 */
class PriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("priceValue", MoneyType::class, options: [
                "label" => "Price",
                "required" => false,
                'empty_data' => null,
                "currency" => false,
                "divisor" => 1000,
            ])
            ->add("priceCurrency", FancyCurrencyType::class, options: [
                "label" => "Currency",
                "required" => false,
                "empty_data" => "CHF",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Price::class,
            "icon" => "price",
        ]);

        parent::configureOptions($resolver);
    }

    public function getParent(): string
    {
        return FormGroupType::class;
    }
}