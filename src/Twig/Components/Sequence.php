<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Form\Substance\OligoType;
use App\Genie\Enums\OligoTypeEnum;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Sequence
{
    public string $sequence;
    public OligoTypeEnum $type;
    public OligoTypeEnum $defaultType = OligoTypeEnum::Peptide;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    #[PreMount]
    public function onMount(array $data): array
    {
        $resolver = new OptionsResolver();

        $resolver
            ->define("sequence")
            ->required()
            ->allowedTypes("string")
        ;

        $resolver
            ->define("type")
            ->required()
            ->allowedTypes("string", OligoTypeEnum::class, "null")
            ->normalize(function (Options $options, null|string|OligoTypeEnum $value): OligoTypeEnum {
                if ($value instanceof OligoTypeEnum) {
                    return $value;
                } elseif ($value === null) {
                    return $options["defaultType"];
                } else {
                    return OligoTypeEnum::from($value);
                }
            })
        ;

        $resolver
            ->define("defaultType")
            ->default(OligoTypeEnum::Peptide)
            ->allowedTypes("string", OligoTypeEnum::class, "null")
            ->normalize(function (Options $options, string|OligoTypeEnum $value): OligoTypeEnum {
                if ($value instanceof OligoTypeEnum) {
                    return $value;
                } else {
                    return OligoTypeEnum::from($value);
                }
            })
        ;

        return $resolver->resolve($data);
    }
}