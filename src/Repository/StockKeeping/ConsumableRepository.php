<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Consumable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consumable[]    findAll()
 * @method Consumable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Consumable|null find($id, $lockMode = null, $lockVersion = null)
 */
class ConsumableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consumable::class);
    }

    public function findAllWithRequiredOrders()
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

    public function findAllWithCriticallyRequiredOrders()
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