<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Rack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rack|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rack|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rack[]    findAll()
 * @method Rack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rack::class);
    }

    public function findAllWithBoxes()
    {
        return $this->createQueryBuilder("r")
            ->select("r")
            ->addSelect("rc")
            ->addSelect("b")
            ->leftJoin("r.boxes", "b")
            ->leftJoin("r.children", "rc")
            ->orderBy("r.name")
            ->addOrderBy("b.name")
            ->groupBy("r")
            ->addGroupBy("b")
            ->addGroupBy("rc")
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Rack[] Returns an array of Rack objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Rack
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
