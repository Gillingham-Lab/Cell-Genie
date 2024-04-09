<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Box;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Epitope;
use App\Entity\Rack;
use App\Genie\Enums\AntibodyType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Collection\CollectionInterface;

class IconService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function get(null|object|array $object): ?string
    {
        if ($object === null) {
            return null;
        }

        if (is_array($object) || $object instanceof \ArrayAccess) {
            return $this->get($object[0]);
        }

        return match($this->entityManager->getClassMetadata(get_class($object))->getName()) {
            CellGroup::class, Cell::class => "cell",
            Plasmid::class => "plasmid",
            Oligo::class => "oligo",
            Chemical::class => "chemical",
            Protein::class => "protein",
            Antibody::class => match ($object->getType()) {
                AntibodyType::Primary => "antibody.primary",
                AntibodyType::Secondary => "antibody.secondary",
            },
            Epitope::class => "epitope",
            Box::class => "box",
            Rack::class => "locaiton",
            default => null,
        };
    }
}