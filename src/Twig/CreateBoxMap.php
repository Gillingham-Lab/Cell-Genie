<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\Box;
use App\Entity\BoxMap;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CreateBoxMap extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction("initBoxMap", fn (Box $box) => BoxMap::fromBox($box))
        ];
    }
}