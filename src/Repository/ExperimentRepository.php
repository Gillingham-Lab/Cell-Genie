<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Experiment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Experiment>
 */
class ExperimentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experiment::class);
    }

    /**
     * @return Experiment[]
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
     * @return Experiment[]
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
