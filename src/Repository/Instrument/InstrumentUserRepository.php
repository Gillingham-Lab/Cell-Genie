<?php
declare(strict_types=1);

namespace App\Repository\Instrument;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\InstrumentUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InstrumentUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstrumentUser::class);
    }

    public function findAllInstrumentUsers(Instrument $instrument)
    {
        $qb = $this->createQueryBuilder("iu")
            ->select("iu")
            ->addSelect("u")
            ->addSelect("g")
            ->leftJoin("iu.user", "u")
            ->leftJoin("u.group", "g")
            ->where("iu.instrument = :instrument")
            ->setParameter("instrument", $instrument->getId()->toRfc4122())
        ;

        return $qb->getQuery()->getResult();
    }
}