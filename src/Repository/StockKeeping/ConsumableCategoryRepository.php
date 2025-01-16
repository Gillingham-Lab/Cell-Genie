<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsumableCategory>
 */
class ConsumableCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumableCategory::class);
    }

    /**
     * @param $id
     * @param $lockMode
     * @param $lockVersion
     * @return ?ConsumableCategory
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->createQueryBuilder("cc")
            ->addSelect("c")
            ->leftJoin("cc.consumables", "c")
            ->where("cc.id = :id")
            ->groupBy("cc")
            ->addGroupBy("c")
            ->setParameter("id", $id, "ulid")
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return ConsumableCategory[]
     */
    public function findAllWithConsumablesAndLots(): array {
        return $this->createQueryBuilder("cc")
            ->addSelect("c")
            ->addSelect("cl")
            ->leftJoin("cc.consumables", "c")
            ->leftJoin("c.lots", "cl")
            ->groupBy("cc")
            ->addGroupBy("c")
            ->addGroupBy("cl")
            ->getQuery()->getResult();
    }
}