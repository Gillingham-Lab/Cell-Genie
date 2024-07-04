<?php

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalDatum>
 *
 * @method ExperimentalDatum|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalDatum|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalDatum[]    findAll()
 * @method ExperimentalDatum[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalDatumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalDatum::class);
    }

//    /**
//     * @return ExperimentalDatum[] Returns an array of ExperimentalDatum objects
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

//    public function findOneBySomeField($value): ?ExperimentalDatum
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
