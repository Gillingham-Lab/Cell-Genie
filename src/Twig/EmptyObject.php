<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Instrument;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EmptyObject extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction("createEmptyCell", fn () => new Cell()),
            new TwigFunction("createEmptyInstrument", fn() => new Instrument()),
        ];
    }
}