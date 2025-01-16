<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\ExperimentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentType>
 */
class ExperimentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentType::class);
    }

    /**
     * @return ExperimentType[]
     */
    public function findAllWithExperiments(): array
    {
        return $this->createQueryBuilder("et")
            ->addSelect("exps")
            #->addSelect("COUNT(et.children) AS number_of_childrens")
            ->leftJoin("et.experiments", "exps", conditionType: Join::ON)
            #->groupBy("et.id")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ExperimentType[]
     */
    public function findByCell(Cell $cell): array
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

    /**
     * @return ExperimentType[]
     */
    public function findByProtein(Protein $protein): array
    {
        return $this->createQueryBuilder("et")
            ->distinct()
            ->leftJoin("et.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.proteinTargets", "p", conditionType: Join::ON)
            ->andWhere("p = :protein")
            ->setParameter("protein", $protein->getUlid(), type: "ulid")
            ->getQuery()
            ->getResult()
        ;
    }
}
