<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\Epitope;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\SubstanceLot;
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

    public function getEntityClass(?object $object): ?string
    {
        if ($object === null) {
            return null;
        }

        try {
            if ($object instanceof SubstanceLot) {
                $object = $object->getLot();
            }

            return $this->entityManager->getClassMetadata(get_class($object))->getName();
        } catch (MappingException $mappingException) {
            return $object::class;
        }
    }

    public function getPath(null|object $object): ?string
    {
        if ($object === null) {
            return null;
        }

        try {
            if ($object instanceof SubstanceLot) {
                $object = $object->getLot();
            }

            $class = $this->entityManager->getClassMetadata(get_class($object))->getName();

            return match($class) {
                Cell::class => $this->router->generate("app_cell_view_number", ["cellNumber" => $object->getCellNumber()]),
                CellGroup::class => $this->router->generate("app_cells_group", ["cellGroup" => $object->getId()]),
                Plasmid::class, Oligo::class, Protein::class, Chemical::class, Antibody::class => $this->router->generate("app_substance_view", ["substance" => $object->getUlid()]),
                Epitope::class => $this->router->generate("app_epitope_view", ["epitope" => $object->getId()]),
                Box::class => $this->router->generate("app_storage_view_box", ["box" => $object->getUlid()]),
                Rack::class => $this->router->generate("app_storage_view_rack", ["rack" => $object->getUlid()]),
                Lot::class => $this->router->generate("app_substance_lot_view", ["lot" => $object->getId()]),
                ExperimentalRunCondition::class => $this->router->generate("app_experiments_run_view", ["run" => $object->getExperimentalRun()->getId()]),
                default => null,
            };
        } catch (MappingException $mappingException) {
            return null;
        }
    }
}