<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class CellCultureOtherEvent extends CellCultureEvent
{

}