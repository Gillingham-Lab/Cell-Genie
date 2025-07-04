<?php
declare(strict_types=1);

namespace App\Twig\Components\Cell;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class CellGroupCount
{
    public CellGroup $group;
}