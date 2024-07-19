<?php

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Repository\User\UserGroupRepository;
use App\Repository\User\UserRepository;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;

/**
 * @extends ServiceEntityRepository<Oligo>
 *
 * @method Oligo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Oligo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Oligo[]    findAll()
 * @method Oligo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OligoRepository extends ServiceEntityRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;

    protected const LotAvailableQuery = "SUM(CASE WHEN l.availability = 'available' THEN 1 ELSE 0 END)";

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Oligo::class);
    }

    public function add(Oligo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Oligo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array[Oligo, int]
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
        $oligo->setExtinctionCoefficient($data["extinctionCoefficient"]);
        $oligo->setMolecularMass($data["molecularMass"]);
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
            #->addSelect("cs")
            #->addSelect("ce")
            ->leftJoin("c.lots", "l")
            #->leftJoin("c.startConjugate", "cs")
            #->leftJoin("c.endConjugate", "ce")
            ->groupBy("c")
            ->addGroupBy("l")
            #->addGroupBy("cs")
            #->addGroupBy("ce")
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
            "sequence" => $searchService->searchWithStringLike($queryBuilder, "c.sequence", $searchValue),
            "oligoType" => $searchService->searchWithString($queryBuilder, "c.oligoTypeEnum", $searchValue),
            "startConjugate" => $searchService->searchWithUlid($queryBuilder, "cs.ulid", $searchValue),
            "endConjugate" => $searchService->searchWithUlid($queryBuilder, "ce.ulid", $searchValue),
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
