<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureOtherEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class CellCultureEventTest extends AbstractExtension
{
    function getTests(): array
    {
        return [
            new TwigTest("cellCultureEvent", fn(?object $object) => $object instanceof CellCultureEvent),
            new TwigTest("cellCultureOtherEvent", fn(?object $object) => $object instanceof CellCultureOtherEvent),
            new TwigTest("cellCultureTestEvent", fn(?object $object) => $object instanceof CellCultureTestEvent),
            new TwigTest("cellCultureSplittingEvent", fn(?object $object) => $object instanceof CellCultureSplittingEvent),
        ];
    }
}