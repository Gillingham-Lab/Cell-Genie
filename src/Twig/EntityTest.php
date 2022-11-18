<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\Box;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureOtherEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Epitope;
use App\Entity\Lot;
use App\Entity\Rack;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class EntityTest extends AbstractExtension
{
    function getTests(): array
    {
        return [
            new TwigTest("integer", fn(mixed $object) => is_int($object)),

            new TwigTest("cell", fn(?object $substance) => $substance instanceof Cell),

            new TwigTest("cellCultureEvent", fn(?object $object) => $object instanceof CellCultureEvent),
            new TwigTest("cellCultureOtherEvent", fn(?object $object) => $object instanceof CellCultureOtherEvent),
            new TwigTest("cellCultureTestEvent", fn(?object $object) => $object instanceof CellCultureTestEvent),
            new TwigTest("cellCultureSplittingEvent", fn(?object $object) => $object instanceof CellCultureSplittingEvent),

            new TwigTest("substance", fn(?object $substance) => $substance instanceof Substance),
            new TwigTest("substanceAntibody", fn(?object $substance) => $substance instanceof Antibody),
            new TwigTest("substanceChemical", fn(?object $substance) => $substance instanceof Chemical),
            new TwigTest("substanceProtein", fn(?object $substance) => $substance instanceof Protein),
            new TwigTest("substanceOligo", fn(?object $substance) => $substance instanceof Oligo),
            new TwigTest("substancePlasmid", fn(?object $substance) => $substance instanceof Plasmid),

            new TwigTest("lot", fn(?object $lot) => $lot instanceof Lot),

            new TwigTest("epitope", fn(?object $epitope) => $epitope instanceof Epitope),

            new TwigTest("box", fn(?object $box) => $box instanceof Box),
            new TwigTest("rack", fn(?object $rack) => $rack instanceof Rack),
        ];
    }
}