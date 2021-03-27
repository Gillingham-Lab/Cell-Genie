<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Protein;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Protein|null find($id, $lockMode = null, $lockVersion = null)
 * @method Protein|null findOneBy(array $criteria, array $orderBy = null)
 * @method Protein[]    findAll()
 * @method Protein[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProteinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Protein::class);
    }

    // /**
    //  * @return Protein[] Returns an array of Protein objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Protein
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
