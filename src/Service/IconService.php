<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\Epitope;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\SubstanceLot;
use App\Genie\Enums\AntibodyType;
use Doctrine\ORM\EntityManagerInterface;

class IconService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @param object|array{object}|null $object
     * @return string|null|array{string, string}
     */
    public function get(null|object|array $object): string|array|null
    {
        if ($object === null) {
            return null;
        }

        if (is_array($object) || $object instanceof \ArrayAccess) {
            return $this->get($object[0]);
        }

        return match(get_class($object)) {
            SubstanceLot::class => "lot",
            default => match($this->entityManager->getClassMetadata(get_class($object))->getName()) {
                CellGroup::class, Cell::class => "cell",
                Plasmid::class => "plasmid",
                Oligo::class => "oligo",
                Chemical::class => "chemical",
                Protein::class => "protein",
                Antibody::class => match ($object->getType()) {
                    AntibodyType::Primary => "antibody.primary",
                    AntibodyType::Secondary => "antibody.secondary",
                    default => "antibody",
                },
                Epitope::class => "epitope",
                Box::class => "box",
                Rack::class => "location",
                Lot::class => "lot",
                ExperimentalRunCondition::class, ExperimentalRun::class => "experiment",
                Instrument::class => "instrument",
                Consumable::class => "consumable",
                ConsumableCategory::class => ["consumable", "box"],
                default => null,
            }
        };
    }
}