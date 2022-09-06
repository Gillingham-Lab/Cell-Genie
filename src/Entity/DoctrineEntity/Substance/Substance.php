<?php

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Traits\NameTrait;
use App\Entity\Traits\NewIdTrait;
use App\Repository\Substance\SubstanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: SubstanceRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "substance_type", type: "string")]
#[Gedmo\Loggable]
class Substance
{
    use NewIdTrait;
    use NameTrait;
}
