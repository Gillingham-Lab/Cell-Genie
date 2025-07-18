<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Twig\Components\Experiment\Datum;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Metadata
{
    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public int $columns = 1;
    public int $md = 2;
    public int $xl = 4;

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    #[PreMount]
    public function preMount(array $attributes): array
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
                        "url" => [ExternalUrl::class, ["title" => $value[1]["title"], "href" => $value[1]["href"]]],
                        "entity" => [EntityReference::class, ["entity" => $value[1]]],
                        "boolean" => [Toggle::class, ["value" => $value[1]]],
                        "raw" => [Raw::class, ["body" => $value[1]]],
                        "date" => [Date::class, ["dateTime" => $value[1]]],
                        "datum" => [Datum::class, $value[1]],
                        "component" => [$value[1]["component"], $value[1]["props"]],
                        default => $value[1],
                    };
                }
            }

            $cleanedAttributes["data"][$title] = $value;
        }

        return $cleanedAttributes;
    }

    #[ExposeInTemplate]
    public function getColumnClass(): string
    {
        $classes = [];

        if ($this->columns) {
            $classes[] = "row-cols-{$this->columns}";
        }

        if ($this->md) {
            $classes[] = "row-cols-{$this->md}";
        }

        if ($this->xl) {
            $classes[] = "row-cols-{$this->xl}";
        }

        return implode(" ", $classes);
    }
}
