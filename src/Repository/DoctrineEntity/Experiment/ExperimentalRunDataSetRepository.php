<?php

namespace App\Repository\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalRunDataSet>
 *
 * @method ExperimentalRunDataSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalRunDataSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalRunDataSet[]    findAll()
 * @method ExperimentalRunDataSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalRunDataSetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalRunDataSet::class);
    }

//    /**
//     * @return ExperimentalRunDataSet[] Returns an array of ExperimentalRunDataSet objects
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

//    public function findOneBySomeField($value): ?ExperimentalRunDataSet
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
