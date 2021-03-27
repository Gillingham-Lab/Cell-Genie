<?php

namespace App\Repository;

use App\Entity\CultureFlask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CultureFlask|null find($id, $lockMode = null, $lockVersion = null)
 * @method CultureFlask|null findOneBy(array $criteria, array $orderBy = null)
 * @method CultureFlask[]    findAll()
 * @method CultureFlask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CultureFlaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CultureFlask::class);
    }

    // /**
    //  * @return CultureFlask[] Returns an array of CultureFlask objects
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
    public function findOneBySomeField($value): ?CultureFlask
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
