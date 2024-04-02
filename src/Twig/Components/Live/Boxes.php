<?php

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Twig\Components\Trait\GeneratedIdTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class Boxes extends AbstractController
{
    use DefaultActionTrait;
    use GeneratedIdTrait;

    /** @var Boxes[] */
    public array $boxes = [];
    public ?CellAliquot $currentCellAliquot = null;
}