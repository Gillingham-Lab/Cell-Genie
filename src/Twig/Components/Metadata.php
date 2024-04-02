<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Twig\Components\Layout\Col;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Metadata
{
    public array $data = [];

    #[PreMount]
    public function preMount(array $attributes)
    {
        $cleanedAttributes = $attributes;
        $cleanedAttributes["data"] = [];

        foreach ($attributes["data"] as $title => $value) {
            if (is_array($value)) {
                if (count($value) === 0) {
                    $value = "-";
                } elseif (count($value) === 1) {
                    $value = $value[0];
                } else {
                    $value = match ($value[0]) {
                        "rrid" => [ExternalUrl::class, ["title" => $value[1], "href" => "https://scicrunch.org/resolver/{$value[1]}"]],
                        "cellosaurus" => [ExternalUrl::class, ["title" => $value[1], "href" => "https://web.expasy.org/cellosaurus/{$value[1]}"]],
                        "entity" => [EntityReference::class, ["entity" => $value[1]]],
                        "boolean" => [Toggle::class, ["value" => $value[1]]],
                        default => $value[1],
                    };
                }
            }

            $cleanedAttributes["data"][$title] = $value;
        }

        return $cleanedAttributes;
    }
}