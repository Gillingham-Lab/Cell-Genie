<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\Epitope;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use function \str_ends_with;
use function \str_starts_with;

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

    public function findAnyAntibody(?Epitope $epitope = null): array
    {
        $qb = $this->createQueryBuilder("a")
            ->addSelect("eph")
            ->addSelect("ept")
            ->leftJoin("a.epitopeTargets", "ept", conditionType: Join::ON)
            ->leftJoin("a.epitopes", "eph", conditionType: Join::ON)
            ->groupBy("a.ulid")
            ->addGroupBy("ept.id")
            ->addGroupBy("eph.id")
            ->orderBy("a.number");

        if ($epitope !== null) {
            $qb = $qb->andWhere("ept.id = :epitope")
                ->setParameter("epitope", $epitope->getId(), "ulid");
        }

        return $qb->getQuery()
            ->getResult()
            ;
    }

    /*public function findPrimaryAntibodies(): array
    {
        $qb = $this->createQueryBuilder("a");

        return $qb
            ->addSelect("ho.shortName as hostName")
            ->leftJoin("a.epitopeTargets", "ep", conditionType: Join::ON)
            ->leftJoin("a.hostOrganism", "ho", conditionType: Join::ON)
            ->groupBy("a.ulid")
            ->addGroupBy("ep.id")
            ->addGroupBy("ho.id")
            ->where("ep NOT INSTANCE OF ?0")
            ->having("count(distinct ep.id) > 0")
            ->setParameters([EpitopeHost::class])
            ->orderBy("a.number")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSecondaryAntibodies($withCount = false): array
    {
        $qb = $this->createQueryBuilder("a");

        return $qb
            ->addSelect("ho.shortName as hostName")
            ->addSelect("ep.shortName as epitopeName")
            ->leftJoin("a.epitopeTargets", "ep", conditionType: Join::WITH, condition: "ep NOT INSTANCE OF ?0")
            ->leftJoin("a.hostOrganism", "ho", conditionType: Join::ON)
            ->groupBy("a.ulid")
            ->addGroupBy("ep.id")
            ->addGroupBy("ho.id")
            #->where("ep INSTANCE OF ?0")
            #->having("count(distinct ep.id) > 0")
            ->setParameters([EpitopeHost::class])
            ->orderBy("a.number")
            ->getQuery()
            ->getResult()
        ;
    }*/

    public function findBySearchTerm(string $searchTerm): array
    {
        if (!str_starts_with($searchTerm, "%") and !str_starts_with($searchTerm, "^")) {
            $searchTerm = "%" . $searchTerm;
        }

        if (!str_ends_with($searchTerm, "%") and !str_starts_with($searchTerm, "$")) {
            $searchTerm = $searchTerm . "%";
        }

        return $this->createQueryBuilder("a")
            ->distinct(True)
            ->addSelect("ho.shortName as hostName")
            ->addSelect("ep.shortName as epitopeName")
            ->leftJoin("a.hostOrganism", "ho")
            ->leftJoin("a.epitopeTargets", "ep")
            #->leftJoin("a.proteinTarget", "pt")
            ->where("a.shortName LIKE :val")
            ->orWhere("a.longName LIKE :val")
            ->orWhere("a.detection LIKE :val")
            ->orWhere("a.number LIKE :val")
            ->orWhere("ho.shortName LIKE :val")
            ->orWhere("ep.shortName LIKE :val")
            #->orWhere("pt.shortName LIKE :val")
            #->orWhere("pt.longName LIKE :val")
            ->orWhere("a.clonality LIKE :val")
            ->orWhere("a.usage LIKE :val")
            ->orderBy("a.number")
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
