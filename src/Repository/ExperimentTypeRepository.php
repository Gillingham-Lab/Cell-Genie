<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Antibody;
use App\Entity\Cell;
use App\Entity\ExperimentType;
use App\Entity\Protein;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentType[]    findAll()
 * @method ExperimentType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentType::class);
    }

    public function findByCell(Cell $cell)
    {
        return $this->createQueryBuilder("et")
            ->distinct()
            ->leftJoin("et.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.cells", "ce", conditionType: Join::ON)
            ->andWhere("ce = :cell")
            ->setParameter("cell", $cell)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByProtein(Protein $protein)
    {
        return $this->createQueryBuilder("et")
            ->distinct()
            ->leftJoin("et.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.proteinTargets", "p", conditionType: Join::ON)
            ->andWhere("p = :protein")
            ->setParameter("protein", $protein)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByAntibody(Antibody $antibody)
    {
        return $this->createQueryBuilder("et")
            ->distinct()
            ->leftJoin("et.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.antibodyDilutions", "dil", conditionType: Join::ON)
            ->leftJoin("dil.antibody", "ab", conditionType: Join::ON)
            ->andWhere("ab = :antibody")
            ->setParameter("antibody", $antibody)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return ExperimentType[] Returns an array of ExperimentType objects
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
    public function findOneBySomeField($value): ?ExperimentType
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
