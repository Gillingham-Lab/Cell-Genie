<?php
declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentalDesign|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalDesign|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalDesign[]    findAll()
 * @method ExperimentalDesign[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalDesignRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ExperimentalDesign::class);
    }

    public function getPaginatedExperiments(
        ?array $orderBy = null,
        array $searchFields = [],
        int $page = 0,
        int $limit = 30,
    ): Paginator {
        $queryBuilder = $this->getBaseQuery();

        if ($orderBy !== null) {
            $queryBuilder = $this->addOrderBy($queryBuilder, $orderBy);
        }

        $queryBuilder = $queryBuilder
            ->setFirstResult($limit * $page)
            ->setMaxResults($limit)
        ;

        return new Paginator($queryBuilder, fetchJoinCollection: true);
    }

    private function addOrderBy(QueryBuilder $queryBuilder, array $orderBy): QueryBuilder
    {
        foreach ($orderBy as $fieldName => $order) {
            $field = match($fieldName) {
                "number" => "ed.number",
            };

            $order = match($order) {
                "DESC", "descending" => "DESC",
                default => "ASC",
            };

            $queryBuilder = $queryBuilder->addOrderBy($field, $order);
        }

        return $queryBuilder;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("ed");

        $qb = $qb
            ->select("ed");

        return $qb;
    }
}