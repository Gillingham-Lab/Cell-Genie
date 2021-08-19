<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Entity\CellAliquote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * @method CellAliquote|null find($id, $lockMode = null, $lockVersion = null)
 * @method CellAliquote|null findOneBy(array $criteria, array $orderBy = null)
 * @method CellAliquote[]    findAll()
 * @method CellAliquote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellAliquoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CellAliquote::class);
    }

    /**
     * @param Box[] $boxes
     * @return CellAliquote[]|null
     */
    public function findAllFromBoxes(array $boxes)
    {
        $qb = $this->createQueryBuilder("ca");

        return $qb->select("ca")
            ->distinct(True)
            ->leftJoin("ca.box", "b", Join::ON)
            ->where("ca.box IN (:boxes)")
            ->setParameter("boxes", $boxes)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return CellAliquote[] Returns an array of CellAliquote objects
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
    public function findOneBySomeField($value): ?CellAliquote
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
