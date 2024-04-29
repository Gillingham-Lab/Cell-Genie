<?php
declare(strict_types=1);

namespace App\Service\Storage;

use App\Entity\BoxMap;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Lot;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class StorageBoxService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Security $security,
    ) {

    }

    /**
     * @param Cell|Substance $entity
     * @return Collection<int, CellAliquot|Lot>
     */
    private function getEntries(Cell|Substance $entity): Collection
    {
        if ($entity instanceof Cell) {
            $entries = $entity->getCellAliquots();
        } else {
            $entries = $entity->getLots();
        }

        return $entries;
    }

    /**
     * Returns a list of boxes related to a cell or a substance.
     * @param Cell|Substance $entity
     * @return Box[] A list of unique boxes.
     */
    public function getBoxes(Cell|Substance $entity): array
    {
        $boxes = [];
        /** @var CellAliquot|Lot $entry */
        foreach ($this->getEntries($entity) as $entry) {
            $box = $entry->getBox();

            if (!$box) {
                continue;
            }

            if (!isset($boxes[$box->getUlid()->toBase58()])) {
                $boxes[$box->getUlid()->toBase58()] = $box;
            }
        }

        return $boxes;
    }

    /**
     * @param Cell|Substance $entity
     * @param Box[] $boxes
     * @return BoxMap[]
     */
    public function getBoxMaps(Cell|Substance $entity, ?array $boxes = null): array
    {
        // Retrieve boxes if null
        $boxes = $boxes ?? $this->getBoxes($entity);

        // Create a list of maps
        $maps = [];
        foreach ($boxes as $box) {
            $maps[$box->getUlid()->toBase58()] = BoxMap::fromBox($box);
        }

        // Fill the maps
        /** @var CellAliquot|Lot $entry */
        foreach ($this->getEntries($entity) as $entry) {
            $boxId = $entry->getBox()->getUlid()->toBase58();
            $coordinate = $entry->getBoxCoordinate();
            $vialCount = $entry instanceof CellAliquot ? $entry->getVials() : $entry->getNumberOfAliquotes();

            if (!isset($maps[$boxId])) {
                $this->logger->warning("Box from an aliquot was not in the box map.", ["service" => self::class, "method" => "getBoxMaps"]);
                continue;
            }

            if (
                ($entry instanceof CellAliquot and $this->security->isGranted("consume", $entry))
                or
                ($entry instanceof Lot and $this->security->isGranted("view", $entry))
            ) {
                $maps[$boxId]->add($entry, $vialCount, $coordinate);
            }
        }

        return $maps;
    }
}