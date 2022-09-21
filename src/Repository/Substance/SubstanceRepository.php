<?php

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Substance;
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

//    /**
//     * @return Substance[] Returns an array of Substance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Substance
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
