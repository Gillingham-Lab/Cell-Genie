<?php

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalRunCondition>
 *
 * @method ExperimentalRunCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalRunCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalRunCondition[]    findAll()
 * @method ExperimentalRunCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalRunConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalRunCondition::class);
    }

//    /**
//     * @return ExperimentalRunCondition[] Returns an array of ExperimentalRunCondition objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ExperimentalRunCondition
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
