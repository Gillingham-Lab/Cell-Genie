<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\ExperimentalRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalRun>
 */
class ExperimentalRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalRun::class);
    }

    /**
     * @return ExperimentalRun[]
     */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder("e")
            ->andWhere("e.owner = :owner")
            ->setParameter("owner", $owner)
            ->orderBy("e.modifiedAt", "DESC")
            ->addOrderBy("e.createdAt", "DESC")
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return ExperimentalRun[]
     */
    public function findNotByOwner(User $owner): array
    {
        return $this->createQueryBuilder("e")
            ->andWhere("e.owner != :owner")
            ->setParameter("owner", $owner)
            ->orderBy("e.modifiedAt", "DESC")
            ->addOrderBy("e.createdAt", "DESC")
            ->getQuery()
            ->getResult()
            ;
    }
}
