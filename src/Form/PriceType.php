<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Embeddable\Price;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
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
            ->add("priceCurrency", CurrencyType::class, options: [
                "label" => "Currency",
                "required" => false,
                "empty_data" => "CHF",
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Price::class
        ]);
        parent::configureOptions($resolver);
    }
}