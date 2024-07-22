<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureOtherEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Epitope;
use App\Entity\Lot;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class EntityTest extends AbstractExtension
{
    function getTests(): array
    {
        return [
            new TwigTest("integer", fn(mixed $object) => is_int($object)),

            new TwigTest("cell", fn(?object $substance) => $substance instanceof Cell),

            new TwigTest("cellCultureEvent", fn(mixed $object) => $object instanceof CellCultureEvent),
            new TwigTest("cellCultureOtherEvent", fn(mixed $object) => $object instanceof CellCultureOtherEvent),
            new TwigTest("cellCultureTestEvent", fn(mixed $object) => $object instanceof CellCultureTestEvent),
            new TwigTest("cellCultureSplittingEvent", fn(mixed $object) => $object instanceof CellCultureSplittingEvent),

            new TwigTest("substance", fn(mixed $substance) => $substance instanceof Substance),
            new TwigTest("substanceAntibody", fn(mixed $substance) => $substance instanceof Antibody),
            new TwigTest("substanceChemical", fn(mixed $substance) => $substance instanceof Chemical),
            new TwigTest("substanceProtein", fn(mixed $substance) => $substance instanceof Protein),
            new TwigTest("substanceOligo", fn(mixed $substance) => $substance instanceof Oligo),
            new TwigTest("substancePlasmid", fn(mixed $substance) => $substance instanceof Plasmid),

            new TwigTest("lot", fn(mixed $lot) => $lot instanceof Lot),

            new TwigTest("epitope", fn(mixed $epitope) => $epitope instanceof Epitope),

            new TwigTest("box", fn(mixed $box) => $box instanceof Box),
            new TwigTest("rack", fn(mixed $rack) => $rack instanceof Rack),

            new TwigTest("float", fn(mixed $x) => is_float($x)),
            new TwigTest("int", fn(mixed $x) => is_int($x)),
            new TwigTest("array", fn(mixed $x) => is_array($x)),
            new TwigTest("object", fn(mixed $x) => is_object($x)),
        ];
    }
}