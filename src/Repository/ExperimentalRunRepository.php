<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExperimentalRun;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExperimentalRun|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExperimentalRun|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExperimentalRun[]    findAll()
 * @method ExperimentalRun[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperimentalRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalRun::class);
    }

    public function findByOwner(User $owner)
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

    public function findNotByOwner(User $owner)
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
