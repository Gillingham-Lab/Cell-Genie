<?php

namespace App\Service;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Collection\CollectionInterface;

class IconService
{
    public function __construct(
        private EntityManagerInterface $entityManager
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
            default => null,
        };
    }
}