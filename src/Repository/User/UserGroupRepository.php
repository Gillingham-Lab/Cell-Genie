<?php
declare(strict_types=1);

namespace App\Repository\User;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<UserGroup>
 */
class UserGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserGroup::class);
    }

    public function findGroupByUser(User $user): ?UserGroup
    {
        return $this->createQueryBuilder("ug")
            ->leftJoin("ug.users", "u", Join::ON)
            ->where("u.id = :user")
            ->setParameter("user", $user->getId(), UlidType::NAME)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}