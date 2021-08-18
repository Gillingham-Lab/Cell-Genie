<?php

namespace App\Repository;

use App\Entity\AntibodyHost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AntibodyHost|null find($id, $lockMode = null, $lockVersion = null)
 * @method AntibodyHost|null findOneBy(array $criteria, array $orderBy = null)
 * @method AntibodyHost[]    findAll()
 * @method AntibodyHost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AntibodyHostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AntibodyHost::class);
    }

    // /**
    //  * @return AntibodyHost[] Returns an array of AntibodyHost objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AntibodyHost
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
