<?php
declare(strict_types=1);

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\User\UserGroup;
use App\Repository\Traits\SearchTermTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;

/**
 * @method Cell|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cell|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cell[]    findAll()
 * @method Cell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellRepository extends ServiceEntityRepository
{
    use SearchTermTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cell::class);
    }

    public function findCellByIdOrNumber(string $numberOrId): Cell
    {
        $qb = $this->createQueryBuilder("c");

        $qb = $qb->select("c")
            ->addSelect("CASE WHEN c.id = :id THEN 1 ELSE 0 END AS HIDDEN sortCondition")
            ->where("c.id = :id")
            ->orWhere("c.cellNumber = :number")
            ->orderBy("sortCondition", "DESC")
            ->setMaxResults(1)
            ->setParameter("id", intval($numberOrId), "integer")
            ->setParameter("number", $numberOrId, "string")
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getCellsWithAliquotes(?array $orderBy = null)
    {
        return $this->getBaseQuery()->getQuery()->getResult();
    }

    public function searchCellsWithAliquots(string $searchTerm, ?array $orderBy = null)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm);

        $qb = $this->getBaseQuery($orderBy)
            ->orWhere("LOWER(cg.number) LIKE :searchTerm")
            ->orWhere("LOWER(cg.rrid) LIKE :searchTerm")
            ->orWhere("LOWER(cg.cellosaurusId) LIKE :searchTerm")
            ->orWhere("LOWER(cg.name) LIKE :searchTerm")
            ->orWhere("LOWER(c.name) LIKE :searchTerm")
            ->orWhere("LOWER(c.cellNumber) LIKE :searchTerm")
            ->setParameter("searchTerm", mb_strtolower($searchTerm))
        ;

        return $qb->getQuery()->getResult();
    }


    public function fetchByProtein(Protein $protein)
    {
        $qb = $this->createQueryBuilder("c");

        $qb = $qb
            ->select("c")
            ->addSelect("cp")
            ->leftJoin("c.cellProteins", "cp", conditionType: Join::ON)
            ->groupBy("c.id")
            ->addGroupBy("cp.id")
            ->orderBy("c.cellNumber")
            ->where("cp.associatedProtein = :protein")
            ->setParameter("protein", $protein->getUlid(), "ulid")
        ;

        return $qb->getQuery()->getResult();
    }

    private function getBaseQuery(?array $orderBy = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder("c");

        $qb = $qb
            ->select("c")
            ->addSelect("cg")
            ->addSelect("ca")
            ->addSelect("m")
            ->addSelect("o")
            ->addSelect("t")
            ->leftJoin("c.cellGroup", "cg", conditionType: Join::ON)
            ->leftJoin("c.cellAliquotes", "ca", conditionType: Join::ON)
            ->leftJoin("cg.morphology", "m", conditionType: Join::ON)
            ->leftJoin("cg.organism", "o", conditionType: Join::ON)
            ->leftJoin("cg.tissue", "t", conditionType: Join::ON)
            ->groupBy("c.id")
            ->addGroupBy("ca.id")
            ->addGroupBy("m.id")
            ->addGroupBy("o.id")
            ->addGroupBy("t.id")
            ->addGroupBy("cg.id")
        ;

        if ($orderBy) {
            foreach ($orderBy as $col => $order) {
                $qb = $qb->addOrderBy("c.".$col, $order);
            }
        }

        return $qb;
    }
}
