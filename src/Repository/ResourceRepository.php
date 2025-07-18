<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\Resource;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resource>
 */
class ResourceRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, Resource::class);
    }

    /**
     * @return array<int, array{category: string}>
     */
    public function findCategories(?string $searchTerm = null): array
    {
        $qb = $this->createQueryBuilder("r")
            ->distinct(true)
            ->select("r.category")
            ->groupBy("r.category")
            ->orderBy("r.category");

        if ($searchTerm !== null) {
            $qb = $qb->where($this->searchService->searchWith($qb, "r.category", "string", $searchTerm));
        }

        return $qb->getQuery()->getScalarResult();
    }
}
