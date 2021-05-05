<?php

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
            ->addSelect("sa")
            ->leftJoin("a.proteinTarget", "pt", conditionType: Join::ON)
            ->leftJoin("a.secondaryAntibody", "sa", conditionType: Join::ON)
            ->groupBy("a.id")
            ->addGroupBy("pt.id")
            ->addGroupBy("sa.id")
            ->having("count(distinct pt.id) > 0")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSecondaryAntibodies($withCount = false)
    {
        $qb = $this->createQueryBuilder("sa");

        if ($withCount) {
            $qb = $qb->addSelect("count(distinct pa) as targets");
        }

        return $qb
            #->addSelect("-1 as target_count")
            #->addSelect("count(sa.id) as antibody_count")
            #->join("antibody_protein", "ap", conditionType: Join::ON, condition: "a.id = ap.antibody_id")
            ->leftJoin("sa.antibodies", "pa", conditionType: Join::ON)
            #->leftJoin("a.antibodies", "sa", conditionType: Join::WITH)
            ->groupBy("sa.id")
            #->addGroupBy("sa.id")
            ->having("count(distinct pa.id) > 0")
            #->andHaving("count(sa.secondaryAntibody.id) = 0")
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
