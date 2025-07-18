<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\HasAvailableLotSearchTrait;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends SubstanceRepository<Plasmid>
 * @implements PaginatedRepositoryInterface<Plasmid>
 */
class PlasmidRepository extends SubstanceRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;
    use HasAvailableLotSearchTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Plasmid::class);
    }

    /**
     * @return array<array{0: Plasmid, 1: int<0, max>}>
     */
    public function findAllWithLotCount(): array
    {
        return $this->createQueryBuilder("p")
            ->addSelect("COUNT(l)")
            ->leftJoin("p.lots", "l")
            ->groupBy("p")
            ->addGroupBy("l.id")
            ->getQuery()
            ->getResult()
        ;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("p")
            ->addSelect("COUNT(l) AS lotCount")
            ->addSelect($this::LotAvailableQuery . " AS hasAvailableLot")
            ->addSelect("ep")
            ->leftJoin("p.lots", "l")
            ->leftJoin("p.expressedProteins", "ep")
            ->groupBy("p")
            ->addGroupBy("ep")
            ->orderBy("p.number");

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

        $expressions = $searchService->createExpressions($searchFields, fn(string $searchField, mixed $searchValue): mixed => match ($searchField) {
            "number" => $searchService->searchWithString($queryBuilder, "p.number", $searchValue),
            "shortName" => $searchService->searchWithStringLike($queryBuilder, "p.shortName", $searchValue),
            "anyName" =>  $queryBuilder->expr()->orX(
                $searchService->searchWithStringLike($queryBuilder, "p.shortName", $searchValue),
                $searchService->searchWithStringLike($queryBuilder, "p.longName", $searchValue),
            ),
            "sequence" => $searchService->searchWithStringLike($queryBuilder, "p.sequence", $searchValue),
            "growthResistance" => $searchService->searchWithStringLike($queryBuilder, "p.growthResistance", $searchValue),
            "expressionOrganism" => $searchService->searchWithInteger($queryBuilder, "p.expressionIn", $searchValue),
            "expressionResistance" => $searchService->searchWithStringLike($queryBuilder, "p.expressionResistance", $searchValue),
            "expressedProtein" => $searchService->searchWithUlid($queryBuilder, "ep.ulid", $searchValue),
            default => null,
        });

        $havingExpressions = $searchService->createExpressions($searchFields, fn(string $searchField, mixed $searchValue): mixed => match ($searchField) {
            "expressesProtein" => $searchValue === true ? $queryBuilder->expr()->gt("COUNT(ep)", 0) : $queryBuilder->expr()->eq("COUNT(ep)", 0),
            default => null,
        });

        $queryBuilder = $searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);
        $queryBuilder = $searchService->addExpressionsToHavingQuery($queryBuilder, $havingExpressions);
        $queryBuilder = $this->addHasAvailableLotSearch($queryBuilder, $searchFields);

        return $queryBuilder;
    }
}
