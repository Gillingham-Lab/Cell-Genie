<?php

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\Substance\SubstanceLot;
use App\Service\Doctrine\Type\Ulid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template TSubstance of Substance
 * @extends ServiceEntityRepository<TSubstance>
 */
class SubstanceRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     * @param class-string<TSubstance>|null $class
     */
    public function __construct(ManagerRegistry $registry, ?string $class = null)
    {
        parent::__construct($registry, $class ?? Substance::class);
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

    private function createSubstanceLotBaseQuery(): QueryBuilder
    {
        return $this->createQueryBuilder("s")
            ->select("s")
            ->addSelect("l")
            ->leftJoin("s.lots", "l")
            ->groupBy("s")
            ->addGroupBy("l")
            ->addGroupBy("l")
            ;
    }

    public function findOneSubstanceLotByLot(string|lot $lot): ?SubstanceLot
    {
        $result = $this->createSubstanceLotBaseQuery()
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

    /**
     * @param array<int, Lot|Ulid|string> $lots
     * @return array<int, SubstanceLot>
     */
    public function findSubstanceLotsByLots(array $lots): array
    {
        $lotIds = [];
        foreach ($lots as $lot) {
            if ($lot instanceof Lot) {
                $lotIds[] = $lot->getId()->toRfc4122();
            } elseif ($lot instanceof Ulid) {
                $lotIds[] = $lot->toRfc4122();
            } else {
                $lotIds[] = $lot;
            }
        }

        $results = $this->createSubstanceLotBaseQuery()
            ->where("l.id IN (:lots)")
            ->setParameter("lots", $lotIds)
            ->getQuery()
            ->getResult()
        ;

        if (count($results) === 0) {
            return [];
        }

        return $this->turnIntoSubstanceLot($results);
    }

    /**
     * @return Substance[]
     */
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
     * @param Substance[] $results
     * @return SubstanceLot[]
     */
    private function turnIntoSubstanceLot(array $results): array
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

    /**
     * @param Box $box
     * @return SubstanceLot[]
     */
    public function findAllSubstanceLotsInBox(Box $box): array
    {
        $results = $this->createQueryBuilder("s")
            ->select("s")
            ->addSelect("l")
            ->addSelect("b")
            ->leftJoin("s.lots", "l")
            ->leftJoin("l.box", "b")
            #->groupBy("s")
            #->addGroupBy("l")
            #->addGroupBy("b")
            ->where("b.ulid = :ulid")
            ->setParameter("ulid", $box->getUlid(), "ulid")
            ->getQuery()
            ->getResult()
        ;

        return $this->turnIntoSubstanceLot($results);
    }
}
