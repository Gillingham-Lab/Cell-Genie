<?php

namespace App\Repository;

use App\Entity\BoxEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoxEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoxEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoxEntry[]    findAll()
 * @method BoxEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoxEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoxEntry::class);
    }

    // /**
    //  * @return BoxEntry[] Returns an array of BoxEntry objects
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
    public function findOneBySomeField($value): ?BoxEntry
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
