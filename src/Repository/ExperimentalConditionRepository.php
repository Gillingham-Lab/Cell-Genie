<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExperimentalCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentalCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalCondition[]    findAll()
 * @method ExperimentalCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalCondition::class);
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
