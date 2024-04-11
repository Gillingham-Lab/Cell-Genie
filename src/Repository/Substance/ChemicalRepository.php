<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Chemical|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chemical|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chemical[]    findAll()
 * @method Chemical[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChemicalRepository extends ServiceEntityRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;

    protected const LotAvailableQuery = "SUM(CASE WHEN l.availability = 'available' THEN 1 ELSE 0 END)";

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Chemical::class);
    }

    public function findByCell(Cell $cell)
    {
        return $this->createQueryBuilder("c")
            ->leftJoin("c.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.cells", "ce", conditionType: Join::ON)
            ->andWhere("ce = :cell")
            ->setParameter("cell", $cell)
            ->getQuery()
            ->getResult()
        ;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("c")
            ->addSelect("COUNT(l) AS lotCount")
            ->addSelect($this::LotAvailableQuery . " AS hasAvailableLot")
            ->leftJoin("c.lots", "l")
            ->groupBy("c.ulid")
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

    private function addOrderBy(QueryBuilder $queryBuilder, array $orderBy): QueryBuilder
    {
        return $queryBuilder;
    }

    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder
    {
        $searchService = $this->searchService;

        $expressions = $this->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "shortName" => $searchService->searchWithStringLike($queryBuilder, "c.shortName", $searchValue),
            "anyName" =>  $queryBuilder->expr()->orX(
                $searchService->searchWithStringLike($queryBuilder, "c.shortName", $searchValue),
                $searchService->searchWithStringLike($queryBuilder, "c.longName", $searchValue),
                $searchService->searchWithStringLike($queryBuilder, "c.iupacName", $searchValue),
            ),
            "casNumber" => $searchService->searchWithStringLike($queryBuilder, "c.casNumber", $searchValue),
            default => null,
        });

        $havingExpressions = $this->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "hasAvailableLot" => $searchValue === true ? $queryBuilder->expr()->gt($this::LotAvailableQuery, 0) : $queryBuilder->expr()->eq($this::LotAvailableQuery, 0),
            default => null,
        });

        $queryBuilder = $this->addExpressionsToSearchQuery($queryBuilder, $expressions);
        $queryBuilder = $this->addExpressionsToHavingQuery($queryBuilder, $havingExpressions);

        return $queryBuilder;
    }
}
