<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExperimentalMeasurement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentalMeasurement|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalMeasurement|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalMeasurement[]    findAll()
 * @method ExperimentalMeasurement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalMeasurementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalMeasurement::class);
    }

    // /**
    //  * @return ExperimentalCondition[] Returns an array of ExperimentalCondition objects
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
    public function findOneBySomeField($value): ?ExperimentalCondition
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
