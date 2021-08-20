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

    public function findPrimaryAntibodies(): array
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

    public function findSecondaryAntibodies($withCount = false): array
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

    public function findBySearchTerm(string $searchTerm): array
    {
        if (!str_starts_with($searchTerm, "%") and !str_starts_with($searchTerm, "^")) {
            $searchTerm = "%" . $searchTerm;
        }

        if (!str_ends_with($searchTerm, "%") and !str_starts_with($searchTerm, "$")) {
            $searchTerm = $searchTerm . "%";
        }

        return $this->createQueryBuilder("sa")
            ->distinct(True)
            ->addSelect("ho.name as hostOrganism")
            ->addSelect("ht.name as hostTarget")
            ->leftJoin("sa.hostOrganism", "ho")
            ->leftJoin("sa.hostTarget", "ht")
            ->leftJoin("sa.proteinTarget", "pt")
            ->where("sa.shortName LIKE :val")
            ->orWhere("sa.longName LIKE :val")
            ->orWhere("sa.detection LIKE :val")
            ->orWhere("sa.number LIKE :val")
            ->orWhere("ht.name LIKE :val")
            ->orWhere("ho.name LIKE :val")
            ->orWhere("pt.shortName LIKE :val")
            ->orWhere("pt.longName LIKE :val")
            ->orWhere("sa.clonality LIKE :val")
            ->orWhere("sa.usage LIKE :val")
            ->orderBy("sa.shortName")
            ->setParameter("val", $searchTerm)
            ->getQuery()
            ->getResult();
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
