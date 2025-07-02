<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\HasAvailableLotSearchTrait;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends SubstanceRepository<Chemical>
 * @implements PaginatedRepositoryInterface<Chemical>
 */
class ChemicalRepository extends SubstanceRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;
    use HasAvailableLotSearchTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Chemical::class);
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("c")
            ->addSelect("COUNT(l) AS lotCount")
            ->addSelect($this::LotAvailableQuery . " AS hasAvailableLot")
            ->leftJoin("c.lots", "l")
            ->groupBy("c")
            ->orderBy("c.shortName");

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

    /**
     * @param array<string, "ASC"|"DESC"> $orderBy
     */
    private function addOrderBy(QueryBuilder $queryBuilder, array $orderBy): QueryBuilder
    {
        return $queryBuilder;
    }

    /**
     * @param array<string, scalar> $searchFields
     */
    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder
    {
        $searchService = $this->searchService;

        $expressions = $searchService->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "shortName" => $searchService->searchWithStringLike($queryBuilder, "c.shortName", $searchValue),
            "anyName" =>  $queryBuilder->expr()->orX(
                $searchService->searchWithStringLike($queryBuilder, "c.shortName", $searchValue),
                $searchService->searchWithStringLike($queryBuilder, "c.longName", $searchValue),
                $searchService->searchWithStringLike($queryBuilder, "c.iupacName", $searchValue),
            ),
            "casNumber" => $searchService->searchWithStringLike($queryBuilder, "c.casNumber", $searchValue),
            default => null,
        });

        $queryBuilder = $searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);
        $queryBuilder = $this->addHasAvailableLotSearch($queryBuilder, $searchFields);

        return $queryBuilder;
    }
}
