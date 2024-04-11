<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Plasmid|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plasmid|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plasmid[]    findAll()
 * @method Plasmid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlasmidRepository extends ServiceEntityRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;

    protected const LotAvailableQuery = "SUM(CASE WHEN l.availability = 'available' THEN 1 ELSE 0 END)";

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Plasmid::class);
    }

    /**
     * @return array[Oligo, int]
     */
    public function findAllWithLotCount(): array
    {
        return $this->createQueryBuilder("p")
            ->addSelect("COUNT(l)")
            ->leftJoin("p.lots", "l")
            ->groupBy("p.ulid")
            ->addGroupBy("l.id")
            ->addOrderBy("p.number", "ASC")
            ->addOrderBy("p.shortName", "ASC")
            ->addOrderBy("p.longName", "ASC")
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
            ->groupBy("p.ulid")
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

    private function addOrderBy(QueryBuilder $queryBuilder, array $orderBy): QueryBuilder
    {
        return $queryBuilder;
    }

    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder
    {
        $searchService = $this->searchService;

        $expressions = $this->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
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

        $havingExpressions = $this->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "hasAvailableLot" => $searchValue === true ? $queryBuilder->expr()->gt($this::LotAvailableQuery, 0) : $queryBuilder->expr()->eq($this::LotAvailableQuery, 0),
            "expressesProtein" => $searchValue === true ? $queryBuilder->expr()->gt("COUNT(ep)", 0) : $queryBuilder->expr()->eq("COUNT(ep)", 0),
            default => null,
        });

        $queryBuilder = $this->addExpressionsToSearchQuery($queryBuilder, $expressions);
        $queryBuilder = $this->addExpressionsToHavingQuery($queryBuilder, $havingExpressions);

        return $queryBuilder;
    }
}
