<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\BoxMap;
use App\Entity\DoctrineEntity\Storage\Box;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CreateBoxMap extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction("initBoxMap", fn (Box $box) => BoxMap::fromBox($box))
        ];
    }
}