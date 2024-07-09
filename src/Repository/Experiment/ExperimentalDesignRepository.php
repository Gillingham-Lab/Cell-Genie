<?php
declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalDesign>
 *
 * @method ExperimentalDesign|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalDesign|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalDesign[]    findAll()
 * @method ExperimentalDesign[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalDesignRepository extends ServiceEntityRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;

    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ExperimentalDesign::class);
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

    private function getPaginatedQuery(): QueryBuilder
    {
        return $this->getBaseQuery();
    }

    private function getPaginatedCountQuery(): QueryBuilder
    {
        return $this->getBaseQuery();
    }

    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder
    {
        return $queryBuilder;
    }
}