<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Chemical;
use App\Entity\DoctrineEntity\Cell\Cell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chemical|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chemical|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chemical[]    findAll()
 * @method Chemical[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChemicalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chemical::class);
    }

    public function findByCell(Cell $cell)
    {
        return $this->createQueryBuilder("c")
            ->leftJoin("c.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.cells", "ce", conditionType: Join::ON)
            ->andWhere("ce = :cell")
            ->setParameter("cell", $cell)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Chemical[] Returns an array of Chemical objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Chemical
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
