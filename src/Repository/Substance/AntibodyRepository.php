<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\Epitope;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\HasAvailableLotSearchTrait;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use function \str_ends_with;
use function \str_starts_with;

/**
 * @extends SubstanceRepository<Antibody>
 * @implements PaginatedRepositoryInterface<Antibody>
 */
class AntibodyRepository extends SubstanceRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;
    use HasAvailableLotSearchTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Antibody::class);
    }

    public function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("a")
            ->addSelect("eph")
            ->addSelect("ept")
            ->addSelect("COUNT(l) AS lotCount")
            ->addSelect($this::LotAvailableQuery . " AS hasAvailableLot")
            ->leftJoin("a.epitopeTargets", "ept", conditionType: Join::ON)
            ->leftJoin("a.epitopes", "eph", conditionType: Join::ON)
            ->leftJoin("a.lots", "l")
            ->groupBy("a")
            ->addGroupBy("ept")
            ->addGroupBy("eph")
            ->orderBy("a.number");

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

        $expressions = $this->searchService->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "antibodyNumber" => $searchService->searchWithStringLike($queryBuilder, "a.number", $searchValue),
            "antibodyType" => $searchService->searchWithStringLike($queryBuilder, "a.type", $searchValue),
            "rrid" => $searchService->searchWithStringLike($queryBuilder, "a.rrid", $searchValue),
            "antibodyName" => $queryBuilder->expr()->orX(
                $searchService->searchWithStringLike($queryBuilder, "a.shortName", $searchValue),
                $searchService->searchWithStringLike($queryBuilder, "a.longName", $searchValue),
            ),
            "internallyValidated" => $searchService->searchWithBool($queryBuilder, "a.validatedInternally", $searchValue),
            "externallyValidated" => $searchService->searchWithBool($queryBuilder, "a.validatedExternally", $searchValue),
            "hasEpitope" => $searchService->searchWithUlid($queryBuilder, "eph.id", $searchValue),
            "targetsEpitope" => $searchService->searchWithUlid($queryBuilder, "ept.id", $searchValue),
            default => null,
        });

        $queryBuilder = $this->searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);
        $queryBuilder = $this->addHasAvailableLotSearch($queryBuilder, $searchFields);

        return $queryBuilder;
    }
}
