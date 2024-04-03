<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Table
{
    public array $table = [];

    #[PreMount]
    public function preMount($props)
    {
        if ($props["table"] instanceof \App\Entity\Table\Table) {
            $props["table"] = $props["table"]->toArray();
        }

        return $props;
    }
}