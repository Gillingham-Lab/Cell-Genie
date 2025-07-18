<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SmilesViewer
{
    public string $smiles;
    public string $key;
    public float $padding = 20.;
    public bool $showSmiles = false;
    public float $size = 10.;
}
