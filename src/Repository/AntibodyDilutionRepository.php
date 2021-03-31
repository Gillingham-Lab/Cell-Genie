<?php

namespace App\Repository;

use App\Entity\AntibodyDilution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AntibodyDilution|null find($id, $lockMode = null, $lockVersion = null)
 * @method AntibodyDilution|null findOneBy(array $criteria, array $orderBy = null)
 * @method AntibodyDilution[]    findAll()
 * @method AntibodyDilution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AntibodyDilutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AntibodyDilution::class);
    }

    // /**
    //  * @return AntibodyDilution[] Returns an array of AntibodyDilution objects
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
    public function findOneBySomeField($value): ?AntibodyDilution
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
