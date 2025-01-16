<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consumable>
 */
class ConsumableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consumable::class);
    }

    /**
     * @return Consumable[]
     */
    public function findAllWithRequiredOrders(): array
    {
        $qb = $this->createQueryBuilder("c")
            ->addSelect("cl")
            ->select("c")
            ->leftJoin("c.lots", "cl")
            ->leftJoin("c.lots", "cl2")
            ->groupBy("c")
            ->addGroupBy("cl")
            ->having("c.consumePackage = True AND SUM(cl2.numberOfUnits - cl2.unitsConsumed) < c.orderLimit")
            ->orHaving("c.consumePackage = False AND SUM(cl2.numberOfUnits * cl2.unitSize - cl2.unitsConsumed) < c.orderLimit")
            ->orderBy("c.category")
            ->addOrderBy("c.longName");

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Consumable[]
     */
    public function findAllWithCriticallyRequiredOrders(): array
    {
        $qb = $this->createQueryBuilder("c")
            ->addSelect("cl")
            ->select("c")
            ->leftJoin("c.lots", "cl")
            ->leftJoin("c.lots", "cl2")
            ->groupBy("c")
            ->addGroupBy("cl")
            ->having("c.consumePackage = True AND SUM(cl2.numberOfUnits - cl2.unitsConsumed) < c.criticalLimit")
            ->orHaving("c.consumePackage = False AND SUM(cl2.numberOfUnits * cl2.unitSize - cl2.unitsConsumed) < c.criticalLimit")
            ->orderBy("c.category")
            ->addOrderBy("c.longName");

        return $qb->getQuery()->getResult();
    }
}