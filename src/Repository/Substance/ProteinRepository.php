<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Epitope;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Protein|null find($id, $lockMode = null, $lockVersion = null)
 * @method Protein|null findOneBy(array $criteria, array $orderBy = null)
 * @method Protein[]    findAll()
 * @method Protein[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProteinRepository extends ServiceEntityRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;

    protected const LotAvailableQuery = "SUM(CASE WHEN l.availability = 'available' THEN 1 ELSE 0 END)";

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Protein::class);
    }

    public function findByCell(Cell $cell)
    {
        return $this->createQueryBuilder("p")
            ->leftJoin("p.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.cells", "ce", conditionType: Join::ON)
            ->andWhere("ce = :cell")
            ->setParameter("cell", $cell)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findWithAntibodies(Epitope $epitope = null, array $orderBy = null)
    {
        $qb = $this->createQueryBuilder("p")
            ->addSelect("ep")
            ->addSelect("ab")
            ->addSelect("pc")
            ->leftJoin("p.epitopes", "ep", conditionType: Join::ON)
            ->leftJoin("ep.antibodies", "ab", conditionType: Join::ON)
            ->leftJoin("p.children", "pc", conditionType: Join::ON)
            ->groupBy("p")
            ->addGroupBy("ep")
            ->addGroupBy("ab")
            ->addGroupBy("pc")
        ;

        if ($epitope) {
            $qb = $qb->where("ep.id = :epitope")
                ->setParameter("epitope", $epitope->getId(), "ulid");
        }

        if ($orderBy) {
            foreach ($orderBy as $col => $ord) {
                $qb = $qb->addOrderBy($col, $ord);
            }
        }

        $qb = $qb->addOrderBy("ab.number", "ASC");

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithAntibodiesAndLotCount(Epitope $epitope = null, array $orderBy = null)
    {
        $qb = $this->createQueryBuilder("p")
            ->addSelect("ep")
            ->addSelect("ab")
            ->addSelect("pc")
            ->addSelect("COUNT(l)")
            ->leftJoin("p.lots", "l")
            ->leftJoin("p.epitopes", "ep", conditionType: Join::ON)
            ->leftJoin("ep.antibodies", "ab", conditionType: Join::ON)
            ->leftJoin("p.children", "pc", conditionType: Join::ON)
            ->groupBy("p")
            ->addGroupBy("ep")
            ->addGroupBy("ab")
            ->addGroupBy("pc")
        ;

        if ($epitope) {
            $qb = $qb->where("ep.id = :epitope")
                ->setParameter("epitope", $epitope->getId(), "ulid");
        }

        if ($orderBy) {
            foreach ($orderBy as $col => $ord) {
                $qb = $qb->addOrderBy($col, $ord);
            }
        }

        $qb = $qb->addOrderBy("ab.number", "ASC");

        return $qb
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
            ->addSelect("ab")
            ->leftJoin("p.lots", "l")
            ->leftJoin("p.epitopes", "ep")
            ->leftJoin("ep.antibodies", "ab")
            ->groupBy("p")
            ->addGroupBy("ep")
            ->addGroupBy("ab")
            ->orderBy("p.shortName");

        return $qb;
    }

    private function getPaginatedQuery(): QueryBuilder
    {
        return $this->getBaseQuery()
            ->addSelect("pp")
            ->addSelect("pc")
            ->leftJoin("p.parents", "pp")
            ->leftJoin("p.children", "pc")
            ->addGroupBy("pp")
            ->addGroupBy("pc")
        ;
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
            "shortName" => $searchService->searchWithStringLike($queryBuilder, "p.shortName", $searchValue),
            "anyName" =>  $queryBuilder->expr()->orX(
                $searchService->searchWithStringLike($queryBuilder, "p.shortName", $searchValue),
                $searchService->searchWithStringLike($queryBuilder, "p.longName", $searchValue),
            ),
            "sequence" => $searchService->searchWithStringLike($queryBuilder, "p.fastaSequence", $searchValue),
            "originOrganism" => $searchService->searchWithInteger($queryBuilder, "p.organism", $searchValue),
            default => null,
        });

        $havingExpressions = $this->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "hasAvailableLot" => $searchValue === true ? $queryBuilder->expr()->gt($this::LotAvailableQuery, 0) : $queryBuilder->expr()->eq($this::LotAvailableQuery, 0),
            "hasAntibodies" => $searchValue === true ? $queryBuilder->expr()->gt("COUNT(ab)", 0) : $queryBuilder->expr()->eq("COUNT(ab)", 0),
            default => null,
        });

        $queryBuilder = $this->addExpressionsToSearchQuery($queryBuilder, $expressions);
        $queryBuilder = $this->addExpressionsToHavingQuery($queryBuilder, $havingExpressions);

        return $queryBuilder;
    }
}
