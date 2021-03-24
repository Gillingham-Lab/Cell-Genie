<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Morphology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Morphology|null find($id, $lockMode = null, $lockVersion = null)
 * @method Morphology|null findOneBy(array $criteria, array $orderBy = null)
 * @method Morphology[]    findAll()
 * @method Morphology[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MorphologyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Morphology::class);
    }

    // /**
    //  * @return Morphology[] Returns an array of Morphology objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Morphology
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
