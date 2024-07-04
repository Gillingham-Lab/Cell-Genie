<?php
declare(strict_types=1);

namespace App\Repository\Instrument;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Instrument|null findOneBy(array $criteria, array $orderBy = null)
 * @method Instrument[]    findAll()
 * @method Instrument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstrumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Instrument::class);
    }

    public function find($id, $lockMode=null, $lockVersion=null): ?Instrument
    {
        return $this->createQueryBuilder("i")
            ->addSelect("iu")
            ->leftJoin("i.users", "iu")
            ->leftJoin("iu.user", "u")
            ->where("i.id = :id")
            ->andWhere("u.isActive = TRUE")
            ->groupBy("i")
            ->addGroupBy("iu")
            ->setParameter("id", $id, "ulid")
            ->getQuery()->getOneOrNullResult();
    }

    public function findAllWithUserRole(User $user)
    {
        $instruments = $this->createQueryBuilder("i")
            ->select("i as instrument")
            ->addSelect("CASE WHEN(iu.role is null) THEN 'untrained' ELSE iu.role END AS role")
            ->addSelect("iu2")
            ->leftJoin("i.users", "iu", conditionType: "WITH", condition: "iu.user = :user")
            ->leftJoin("i.users", "iu2")
            ->leftJoin("iu.user", "u")
            ->where("u.isActive = TRUE")
            ->orWhere("iu.role IS NULL")
            ->setParameter("user", $user->getId()->toRfc4122())
        ;

        return $instruments->getQuery()->getResult();
    }
}
