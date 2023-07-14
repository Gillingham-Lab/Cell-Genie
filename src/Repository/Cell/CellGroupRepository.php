<?php
declare(strict_types=1);

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CellGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CellGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CellGroup[]    findAll()
 * @method CellGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CellGroup::class);
    }

    public function getGroupsWithCellsAndAliquots(?array $orderBy = null)
    {
        $qb = $this->createQueryBuilder("cg");

        $qb = $qb
            ->select("cg")
            ->addSelect("cgc")
            ->addSelect("c")
            ->addSelect("ca")
            ->addSelect("m")
            ->addSelect("o")
            ->addSelect("t")
            ->leftJoin("cg.children", "cgc", conditionType: Join::ON)
            ->leftJoin("cg.cells", "c", conditionType: Join::ON)
            ->leftJoin("c.cellAliquotes", "ca", conditionType: Join::ON)
            ->leftJoin("cg.morphology", "m", conditionType: Join::ON)
            ->leftJoin("cg.organism", "o", conditionType: Join::ON)
            ->leftJoin("cg.tissue", "t", conditionType: Join::ON)
            ->groupBy("cg")
            ->addGroupBy("cgc")
            ->addGroupBy("c")
            ->addGroupBy("m")
            ->addGroupBy("o")
            ->addGroupBy("t")
            ->addGroupBy("ca")
        ;

        if ($orderBy) {
            foreach ($orderBy as $col => $order) {
                $qb = $qb->addOrderBy("c.".$col, $order);
            }
        }

        return $qb->getQuery()->getResult();
    }
}