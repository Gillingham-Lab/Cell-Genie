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
    public protected(set) bool $isComposite = false;
    public protected(set) array $individualSequences = [];

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

        $data = $resolver->resolve($data);

        $this->detectAndParseCompositeSequences($data["sequence"]);

        return $data;
    }

    public function detectAndParseCompositeSequences(string $sequence): void
    {
        $parts = explode(">", $sequence);

        if (count($parts) > 1) {
            $this->isComposite = true;
            foreach ($parts as $part) {
                if (trim($part) === "") {
                    continue;
                }
                $subParts = explode("\n", $part, 2);
                $subParts = array_map("trim", $subParts);

                if ($subParts[0] === "") {
                    continue;
                }

                if (count($subParts) < 2) {
                    $this->individualSequences[] = ["title" => $subParts[0], "sequence" => ""];
                } else {
                    $this->individualSequences[] = ["title" => $subParts[0], "sequence" => $subParts[1]];
                }
            }
        }
    }
}
