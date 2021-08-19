<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Antibody;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Antibody|null find($id, $lockMode = null, $lockVersion = null)
 * @method Antibody|null findOneBy(array $criteria, array $orderBy = null)
 * @method Antibody[]    findAll()
 * @method Antibody[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AntibodyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Antibody::class);
    }

    public function findPrimaryAntibodies()
    {
        return $this->createQueryBuilder("a")
            ->addSelect("pt")
            ->addSelect("ho.name as hostOrganism")
            ->leftJoin("a.proteinTarget", "pt", conditionType: Join::ON)
            ->leftJoin("a.hostOrganism", "ho", conditionType: Join::ON)
            ->groupBy("a.id")
            ->addGroupBy("pt.id")
            ->addGroupBy("ho.id")
            ->having("count(distinct pt.id) > 0")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSecondaryAntibodies($withCount = false)
    {
        $qb = $this->createQueryBuilder("sa");

        return $qb
            ->addSelect("ht.name as hostName")
            ->leftJoin("sa.hostTarget", "ht", conditionType: Join::ON)
            ->groupBy("sa.id")
            ->addGroupBy("ht.id")
            ->having("count(distinct ht.id) > 0")
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Antibody[] Returns an array of Antibody objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Antibody
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
