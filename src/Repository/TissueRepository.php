<?php

namespace App\Repository;

use App\Entity\Tissue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tissue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tissue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tissue[]    findAll()
 * @method Tissue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TissueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tissue::class);
    }

    // /**
    //  * @return Tissue[] Returns an array of Tissue objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tissue
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
