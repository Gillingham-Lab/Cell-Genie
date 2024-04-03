<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Epitope;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EntityResolver
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $router,
    ) {

    }

    public function getPath(null|object $object): ?string
    {
        if ($object === null) {
            return null;
        }

        try {
            $class = $this->entityManager->getClassMetadata(get_class($object))->getName();

            return match($class) {
                Cell::class => $this->router->generate("app_cell_view_number", ["cellNumber" => $object->getCellNumber()]),
                CellGroup::class => $this->router->generate("app_cells_group", ["cellGroup" => $object->getId()]),
                Plasmid::class, Oligo::class, Protein::class, Chemical::class, Antibody::class => $this->router->generate("app_substance_view", ["substance" => $object->getUlid()]),
                Epitope::class => $this->router->generate("app_epitope_view", ["epitope" => $object->getId()]),
                default => null,
            };
        } catch (MappingException $mappingException) {
            return null;
        }
    }
}