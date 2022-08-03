<?php
declare(strict_types=1);

namespace App\Twig;

use App\Entity\Epitope;
use App\Entity\EpitopeHost;
use App\Entity\EpitopeProtein;
use App\Entity\EpitopeSmallMolecule;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class EpitopeTests extends AbstractExtension
{
    function getTests()
    {
        return [
            new TwigTest("epitope", fn(?object $epitope) => $epitope instanceof Epitope),
            new TwigTest("epitopeHost", fn(?object $epitope) => $epitope instanceof EpitopeHost),
            new TwigTest("epitopeProtein", fn(?object $epitope) => $epitope instanceof EpitopeProtein),
            new TwigTest("epitopeSmallMolecule", fn(?object $epitope) => $epitope instanceof EpitopeSmallMolecule),
        ];
    }
}