<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organism;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Organism|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organism|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organism[]    findAll()
 * @method Organism[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganismRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organism::class);
    }

    // /**
    //  * @return Organism[] Returns an array of Organism objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Organism
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
