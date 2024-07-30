<?php
declare(strict_types=1);

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\User\UserGroup;
use App\Repository\Traits\SearchTermTrait;
use App\Service\Doctrine\SearchService;
use App\Service\Doctrine\Type\Ulid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cell|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cell|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cell[]    findAll()
 * @method Cell[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellRepository extends ServiceEntityRepository
{
    use SearchTermTrait;

    public function __construct(
        ManagerRegistry $registry,
        private SearchService $searchService,
    ) {
        parent::__construct($registry, Cell::class);
    }

    public function findCellByIdOrNumber(string $numberOrId): ?Cell
    {
        $qb = $this->createQueryBuilder("c");

        if (Ulid::isValid($numberOrId)) {
            $qb = $qb->where("c.id = :query");
        } else {
            $qb = $qb->where("c.cellNumber = :query");
        }

        $qb = $qb
            ->setParameter("query", $numberOrId)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getCellsWithAliquotes(
        ?array $orderBy = null,
        array $searchFields = [],
    ) {
        $queryBuilder = $this->getBaseQuery();

        if ($orderBy !== null) {
            $queryBuilder = $this->addOrderBy($queryBuilder, $orderBy);
        }

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function getPaginatedCellsWithAliquots(
        ?array $orderBy = null,
        array $searchFields = [],
        int $page = 0,
        int $limit = 30,
        bool $omitAliquots = false,
    ): Paginator {
        $queryBuilder = $this->getBaseQuery(omitAliquots: $omitAliquots);

        if ($orderBy !== null) {
            $queryBuilder = $this->addOrderBy($queryBuilder, $orderBy);
        }

        if (!empty($searchFields)) {
            $queryBuilder = $this->addSearchFields($queryBuilder, $searchFields);
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
                "group" => "go.group",
                "cellNumber" => "c.cellNumber",
                "name" => "c.name",
            };

            $order = match($order) {
                "DESC", "descending" => "DESC",
                default => "ASC",
            };

            $queryBuilder = $queryBuilder->addOrderBy($field, $order);
        }

        return $queryBuilder;
    }

    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder
    {
        $searchService = $this->searchService;

        $expressions = [];
        foreach ($searchFields as $searchField => $searchValue) {
            if ($searchValue === null or (is_string($searchValue) and strlen($searchValue) === 0)) {
                continue;
            }

            [$searchField, $searchType] = match($searchField) {
                "cellNumber" => ["c.cellNumber", "string"],
                "cellIdentifier" => [["cg.rrid", "cg.cellosaurusId"], "string"],
                "cellName" => ["c.name", "string"],
                "cellGroupName" => ["cg.name", "string"],
                "groupOwner" => ["go.id", "ulid"],
                "isCancer" => ["cg.isCancer", "bool"],
                "isEngineered" => ["c.isEngineered", "bool"],
                "organism" => ["cg.organism", "int"],
                "tissue" => ["cg.tissue", "int"],
            };

            $expressions[] = $this->searchService->searchWith($queryBuilder, $searchField, $searchType, $searchValue);
        }

        return match (count($expressions)) {
            0 => $queryBuilder,
            1 => $queryBuilder->andWhere($expressions[0]),
            default => $queryBuilder->andWhere($queryBuilder->expr()->andX(...$expressions)),
        };
    }

    public function searchCellsWithAliquots(string $searchTerm, ?array $orderBy = null)
    {
        $searchTerm = $this->prepareSearchTerm($searchTerm);

        $qb = $this->getBaseQuery($orderBy)
            ->orWhere("LOWER(cg.number) LIKE :searchTerm")
            ->orWhere("LOWER(cg.rrid) LIKE :searchTerm")
            ->orWhere("LOWER(cg.cellosaurusId) LIKE :searchTerm")
            ->orWhere("LOWER(cg.name) LIKE :searchTerm")
            ->orWhere("LOWER(c.name) LIKE :searchTerm")
            ->orWhere("LOWER(c.cellNumber) LIKE :searchTerm")
            ->setParameter("searchTerm", mb_strtolower($searchTerm))
        ;

        return $qb->getQuery()->getResult();
    }


    public function fetchByProtein(Protein $protein)
    {
        $qb = $this->createQueryBuilder("c");

        $qb = $qb
            ->select("c")
            ->addSelect("cp")
            ->leftJoin("c.cellProteins", "cp", conditionType: Join::ON)
            ->groupBy("c.id")
            ->addGroupBy("cp.id")
            ->orderBy("c.cellNumber")
            ->where("cp.associatedProtein = :protein")
            ->setParameter("protein", $protein->getUlid(), "ulid")
        ;

        return $qb->getQuery()->getResult();
    }

    private function getBaseQuery(?array $orderBy = null, bool $omitAliquots = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder("c");

        $qb = $qb
            ->select("c")
            ->addSelect("cg")
            ->addSelect("m")
            ->addSelect("o")
            ->addSelect("t")
            ->addSelect("go")
            ->leftJoin("c.cellGroup", "cg", conditionType: Join::ON)
            ->leftJoin("cg.morphology", "m", conditionType: Join::ON)
            ->leftJoin("cg.organism", "o", conditionType: Join::ON)
            ->leftJoin("cg.tissue", "t", conditionType: Join::ON)
            ->leftJoin("c.group", "go", conditionType: Join::ON)
            ->groupBy("c.id")
            ->addGroupBy("m.id")
            ->addGroupBy("o.id")
            ->addGroupBy("t.id")
            ->addGroupBy("cg.id")
            ->addGroupBy("go.id")
        ;

        if ($omitAliquots === false) {
            $qb = $qb
                ->addSelect("ca")
                ->leftJoin("c.cellAliquotes", "ca", conditionType: Join::ON)
                ->addGroupBy("ca.id")
            ;
        }

        if ($orderBy) {
            foreach ($orderBy as $col => $order) {
                $qb = $qb->addOrderBy("c.".$col, $order);
            }
        }

        return $qb;
    }
}
