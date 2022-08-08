<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Box|null find($id, $lockMode = null, $lockVersion = null)
 * @method Box|null findOneBy(array $criteria, array $orderBy = null)
 * @method Box[]    findAll()
 * @method Box[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Box::class);
    }

    public function findByAliquotedCell(Cell $cell)
    {
        /*return $this->createQueryBuilder("b")
            ->leftJoin("b.cellAliquotes", "a", conditionType: Join::ON)
            ->where("a.cell = :val")
            ->andWhere("a.vials > 0")
            ->setParameter("val", $cell)
            ->getQuery()
            ->getResult()
        ;*/

        return $this->createQueryBuilder("b")
            ->distinct(true)
            ->select("b")
            ->leftJoin(CellAliquot::class, "ca", conditionType: Join::WITH, condition: "ca.box = b")
            ->where("ca.cell = :val")
            ->andWhere("ca.vials > 0")
            ->setParameter("val", $cell)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Box[] Returns an array of Box objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Box
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
