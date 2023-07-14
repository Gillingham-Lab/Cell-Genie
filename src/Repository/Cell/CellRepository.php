<?php
declare(strict_types=1);

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\User\UserGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    // /**
    //  * @return Cell[] Returns an array of Cell objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cell
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
