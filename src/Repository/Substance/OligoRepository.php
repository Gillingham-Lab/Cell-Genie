<?php

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Oligo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Oligo>
 *
 * @method Oligo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Oligo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Oligo[]    findAll()
 * @method Oligo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OligoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Oligo::class);
    }

    public function add(Oligo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Oligo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array[Oligo, int]
     */
    public function findAllWithLotCount(): array
    {
        return $this->createQueryBuilder("o")
            ->addSelect("COUNT(l)")
            ->leftJoin("o.lots", "l")
            ->groupBy("o.ulid")
            ->addGroupBy("l.id")
            ->addOrderBy("o.shortName", "ASC")
            ->getQuery()
            ->getResult()
        ;
    }
}
