<?php
declare(strict_types=1);

namespace App\Repository\Instrument;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @method Instrument|null find($id, $lockMode = null, $lockVersion = null)
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

    public function findAllWithUserRole(User $user)
    {
        $instruments = $this->createQueryBuilder("i")
            ->select("i as instrument")
            ->addSelect("CASE WHEN(iu.role is null) THEN 'untrained' ELSE iu.role END AS role")
            ->leftJoin("i.users", "iu", conditionType: "WITH", condition: "iu.user = :user")
            ->setParameter("user", $user->getId()->toRfc4122())
        ;

        return $instruments->getQuery()->getResult();
    }
}
