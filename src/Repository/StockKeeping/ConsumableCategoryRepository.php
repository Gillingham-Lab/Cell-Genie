<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConsumableCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsumableCategory[]    findAll()
 * @method ConsumableCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsumableCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumableCategory::class);
    }

    public function find($id, $lockMode = null, $lockVersion = null)
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
}