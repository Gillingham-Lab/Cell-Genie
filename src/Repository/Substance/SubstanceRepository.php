<?php

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\Substance\SubstanceLot;
use App\Entity\Lot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Substance>
 *
 * @method Substance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Substance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Substance[]    findAll()
 * @method Substance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Substance::class);
    }

    public function add(Substance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Substance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByLot(string|Lot $lot): ?Substance
    {
        return $this->createQueryBuilder("s")
            ->leftJoin("s.lots", "l")
            ->groupBy("s.ulid")
            ->addGroupBy("l.id")
            ->where("l.id = :lot")
            ->setParameter("lot", ($lot instanceof Lot ? $lot->getId() : $lot), "ulid")
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneSubstanceLotByLot(string|lot $lot): ?SubstanceLot
    {
        $result = $this->createQueryBuilder("s")
            ->select("s")
            ->addSelect("l")
            ->leftJoin("s.lots", "l")
            ->groupBy("s.ulid")
            ->addGroupBy("l.id")
            ->where("l.id = :lot")
            ->setParameter("lot", ($lot instanceof Lot ? $lot->getId() : $lot), "ulid")
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($result === null) {
            return null;
        }

        foreach ($result->getLots() as $lot) {
            if ($lot->getId() === null) {}
        }

        return new SubstanceLot($result[0], $result[1]);
    }

    /** @return Substance[] */
    public function findAllWithLot(): array
    {
        return $this->createQueryBuilder("s")
            ->select("s")
            ->addSelect("l")
            ->leftJoin("s.lots", "l")
            ->groupBy("s.ulid")
            ->addGroupBy("l.id")
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return SubstanceLot[]
     */
    private function turnIntoSubstanceLot($results): array
    {
        $return = [];
        /** @var Substance $result */
        foreach ($results as $result) {
            foreach ($result->getLots() as $lot) {
                $return[] = new SubstanceLot($result, $lot);
            }
        }

        return $return;
    }

    /**
     * @return SubstanceLot[]
     */
    public function findAllSubstanceLots(): array
    {
        $results = $this->createQueryBuilder("s")
            ->select("s")
            ->addSelect("l")
            ->leftJoin("s.lots", "l")
            ->groupBy("s.ulid")
            ->addGroupBy("l.id")
            ->getQuery()
            ->getResult()
        ;

        return $this->turnIntoSubstanceLot($results);
    }

    public function findAllSubstanceLotsInBox(Box $box): array
    {
        $results = $this->createQueryBuilder("s")
            ->select("s")
            ->addSelect("l")
            ->addSelect("b")
            ->leftJoin("s.lots", "l")
            ->leftJoin("l.box", "b")
            ->groupBy("s.ulid")
            ->addGroupBy("l.id")
            ->addGroupBy("b")
            ->where("b.ulid = :ulid")
            ->setParameter("ulid", $box->getUlid(), "ulid")
            ->getQuery()
            ->getResult()
        ;

        return $this->turnIntoSubstanceLot($results);
    }
}
