<?php
declare(strict_types=1);

namespace App\Repository\Instrument;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\InstrumentUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InstrumentUser>
 */
class InstrumentUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstrumentUser::class);
    }

    /**
     * @param Instrument $instrument
     * @return InstrumentUser[]
     */
    public function findAllInstrumentUsers(Instrument $instrument): array
    {
        $qb = $this->createQueryBuilder("iu")
            ->select("iu")
            ->addSelect("u")
            ->addSelect("g")
            ->leftJoin("iu.user", "u")
            ->leftJoin("u.group", "g")
            ->where("iu.instrument = :instrument")
            ->andWhere("u.isActive = TRUE")
            ->setParameter("instrument", $instrument->getId()->toRfc4122())
        ;

        return $qb->getQuery()->getResult();
    }
}