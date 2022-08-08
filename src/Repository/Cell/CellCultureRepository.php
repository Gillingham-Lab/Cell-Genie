<?php

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CellCulture>
 *
 * @method CellCulture|null find($id, $lockMode = null, $lockVersion = null)
 * @method CellCulture|null findOneBy(array $criteria, array $orderBy = null)
 * @method CellCulture[]    findAll()
 * @method CellCulture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CellCultureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CellCulture::class);
    }

    public function findAllBetween(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $qb = $this->createQueryBuilder("cc");

        $qb = $qb
            ->select("cc")
            ->addSelect("CASE WHEN cc.aliquot IS NULL THEN 1 ELSE 0 END AS HIDDEN priority")
            ->addGroupBy("cc.id")
            ->orderBy("priority", "DESC")
            ->where("cc.unfrozenOn >= :start and cc.unfrozenOn <= :end")
            ->orWhere("cc.trashedOn >= :start and cc.trashedOn <= :end")
            ->orWhere("cc.unfrozenOn < :start and cc.trashedOn IS NULL")
            ->orWhere("cc.unfrozenOn < :start and cc.trashedOn > :end")
            ->setParameter("start", $start)
            ->setParameter("end", $end)
        ;

        return $qb->getQuery()->getResult();
    }


    public function add(CellCulture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CellCulture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
