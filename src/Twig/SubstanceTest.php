<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Lot;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class SubstanceTest extends AbstractExtension
{
    function getTests(): array
    {
        return [
            new TwigTest("substance", fn(?object $substance) => $substance instanceof Substance),
            new TwigTest("substanceAntibody", fn(?object $substance) => $substance instanceof Antibody),
            new TwigTest("substanceChemical", fn(?object $substance) => $substance instanceof Chemical),
            new TwigTest("substanceProtein", fn(?object $substance) => $substance instanceof Protein),
            new TwigTest("substanceOligo", fn(?object $substance) => $substance instanceof Oligo),

            // Related
            new TwigTest("lot", fn(?object $lot) => $lot instanceof Lot),

            new TwigTest("integer", fn(mixed $object) => is_int($object))
        ];
    }
}