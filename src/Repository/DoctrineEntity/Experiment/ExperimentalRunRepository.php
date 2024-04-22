<?php

namespace App\Repository\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalRun>
 *
 * @method ExperimentalRun|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalRun|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalRun[]    findAll()
 * @method ExperimentalRun[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalRun::class);
    }

//    /**
//     * @return ExperimentalRun[] Returns an array of ExperimentalRun objects
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

//    public function findOneBySomeField($value): ?ExperimentalRun
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
