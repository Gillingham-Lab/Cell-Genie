<?php
declare(strict_types=1);

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Repository\Traits\SearchTermTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CellGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CellGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CellGroup[]    findAll()
 * @method CellGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellGroupRepository extends ServiceEntityRepository
{
    use SearchTermTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CellGroup::class);
    }

    private function getBaseQuery(): QueryBuilder
    {
        return $this->createQueryBuilder("cg")
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
            ->addGroupBy("ca");
    }

    public function getGroupsWithCellsAndAliquots(?array $orderBy = null)
    {
        $qb = $this->getBaseQuery();

        if ($orderBy) {
            foreach ($orderBy as $col => $order) {
                $qb = $qb->addOrderBy("c.".$col, $order);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function searchGroupsWithCellsAndAliquots(string $searchTerm, ?array $orderBy = null)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm);

        $qb = $this->getBaseQuery()
            ->orWhere("LOWER(cg.number) LIKE :searchTerm")
            ->orWhere("LOWER(cg.rrid) LIKE :searchTerm")
            ->orWhere("LOWER(cg.cellosaurusId) LIKE :searchTerm")
            ->orWhere("LOWER(cg.name) LIKE :searchTerm")
            ->orWhere("LOWER(c.name) LIKE :searchTerm")
            ->orWhere("LOWER(c.cellNumber) LIKE :searchTerm")
            ->setParameter("searchTerm", mb_strtolower($searchTerm))
        ;

        if ($orderBy) {
            foreach ($orderBy as $col => $order) {
                $qb = $qb->addOrderBy("c.".$col, $order);
            }
        }

        return $qb->getQuery()->getResult();
    }
}