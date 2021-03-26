<?php

namespace App\Repository;

use App\Entity\ExperimentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentType[]    findAll()
 * @method ExperimentType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentType::class);
    }

    // /**
    //  * @return ExperimentType[] Returns an array of ExperimentType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ExperimentType
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
