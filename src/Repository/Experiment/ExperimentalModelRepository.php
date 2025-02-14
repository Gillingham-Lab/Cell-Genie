<?php
declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalModel>
 */
class ExperimentalModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalModel::class);
    }

    /**
     * @return ExperimentalModel[]
     */
    public function getModelsForConditions(string $model, ExperimentalRunCondition ... $conditions): array
    {
        if (count($conditions) === 0) {
            return [];
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb2 = $this->getEntityManager()->createQueryBuilder();

        $subQuery = $qb2
            ->select("cem.id")
            ->from(ExperimentalRunCondition::class, "cond")
            ->leftJoin("cond.models", "cem")
            ->where("cem.model = :model")
            ->andWhere("cond.id IN (:conditions)")
            ->getDQL();

        return $this->createQueryBuilder('em')
            ->where(
                $qb->expr()->in("em.id", $subQuery)
            )
            ->setParameter("conditions", array_map(fn (ExperimentalRunCondition $c) => $c->getId(), $conditions))
            ->setParameter("model", $model)
            ->getQuery()
            ->getResult()
        ;
    }
}