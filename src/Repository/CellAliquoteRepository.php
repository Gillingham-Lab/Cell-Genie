<?php

namespace App\Repository;

use App\Entity\CellAliquote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
