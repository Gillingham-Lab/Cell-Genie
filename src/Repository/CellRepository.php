<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Cell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getCellsWithAliquotes(?array $orderBy = null)
    {
        $qb = $this->createQueryBuilder("c");

        $qb = $qb
            ->select("c")
            ->addSelect("ca")
            ->addSelect("m")
            ->addSelect("o")
            ->addSelect("t")
            ->leftJoin("c.cellAliquotes", "ca", conditionType: Join::ON)
            ->leftJoin("c.morphology", "m", conditionType: Join::ON)
            ->leftJoin("c.organism", "o", conditionType: Join::ON)
            ->leftJoin("c.tissue", "t", conditionType: Join::ON)
            ->groupBy("c.id")
            ->addGroupBy("ca.id")
            ->addGroupBy("m.id")
            ->addGroupBy("o.id")
            ->addGroupBy("t.id")
        ;

        if ($orderBy) {
            foreach ($orderBy as $col => $order) {
                $qb->addOrderBy("c.".$col, $order);
            }
        }

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
