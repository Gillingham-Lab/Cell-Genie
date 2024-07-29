<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class PlasmidViewer
{
    public ?string $sequence;
    public ?int $length;
    public array $annotations;

    #[PreMount]
    public function preMount(array $props)
    {
        if ($props["annotations"] instanceof Collection) {
            $props["annotations"] = $props["annotations"]->toArray();
        }

        return $props;
    }
}