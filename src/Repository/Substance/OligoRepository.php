<?php

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\HasAvailableLotSearchTrait;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Repository\User\UserGroupRepository;
use App\Repository\User\UserRepository;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;

/**
 * @extends SubstanceRepository<Oligo>
 * @implements PaginatedRepositoryInterface<Oligo>
 */
class OligoRepository extends SubstanceRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;
    use HasAvailableLotSearchTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Oligo::class);
    }

    /**
     * @return array<int, array{0: Oligo, 1: int<0, max>}>
     */
    public function findAllWithLotCount(): array
    {
        return $this->createQueryBuilder("o")
            ->addSelect("COUNT(l)")
            ->leftJoin("o.lots", "l")
            ->groupBy("o")
            ->addOrderBy("o.shortName", "ASC")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array{
     *     shortName: string,
     *     longName: string,
     *     comment: string,
     *     sequence: string,
     *     extinctionCoefficient: numeric-string,
     *     molecularMass: numeric-string,
     *     privacyLevel: value-of<PrivacyLevel>,
     *     owner: string,
     *     group: string,
     * } $data
     */
    public static function createFromArray(
        UserRepository $userRepository,
        UserGroupRepository $groupRepository,
        array $data
    ): Oligo {
        $oligo = new Oligo();
        $oligo->setShortName($data["shortName"]);
        $oligo->setLongName($data["longName"]);
        $oligo->setComment($data["comment"]);
        $oligo->setSequence($data["sequence"]);
        $oligo->setExtinctionCoefficient(floatval($data["extinctionCoefficient"]));
        $oligo->setMolecularMass(floatval($data["molecularMass"]));
        $oligo->setPrivacyLevel(PrivacyLevel::from(intval($data["privacyLevel"])));
        $oligo->setOwner($userRepository->find(Ulid::fromString($data["owner"])));
        $oligo->setGroup($groupRepository->find(Ulid::fromString($data["group"])));

        return $oligo;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("c")
            ->addSelect("COUNT(l) AS lotCount")
            ->addSelect($this::LotAvailableQuery . " AS hasAvailableLot")
            ->leftJoin("c.lots", "l")
            ->leftJoin("c.startConjugate", "cs")
            ->leftJoin("c.endConjugate", "ce")
            ->groupBy("c")
            ->addGroupBy("l")
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
            ),
            "sequence" => $searchService->searchWithStringLike($queryBuilder, "c.sequence", $searchValue),
            "oligoType" => $searchService->searchWithString($queryBuilder, "c.oligoTypeEnum", $searchValue),
            "startConjugate" => $searchService->searchWithUlid($queryBuilder, "cs.ulid", $searchValue),
            "endConjugate" => $searchService->searchWithUlid($queryBuilder, "ce.ulid", $searchValue),
            default => null,
        });

        $queryBuilder = $searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);
        $queryBuilder = $this->addHasAvailableLotSearch($queryBuilder, $searchFields);

        return $queryBuilder;
    }
}
