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

    public function findAllBetween(\DateTimeInterface $start, \DateTimeInterface $end, string $incubator = null, string $scientist = null)
    {
        // Joins on events not possible due to https://github.com/doctrine/orm/pull/9743
        $qb = $this->createQueryBuilder("cc");

        $qb = $qb
            ->select("cc")
            ->addSelect("ca")
            ->addSelect("c")
            ->addSelect("co")
            ->addSelect("ce")
            ->addSelect("CASE WHEN cc.aliquot IS NULL THEN 1 ELSE 0 END AS HIDDEN priority")
            ->leftJoin("cc.aliquot", "ca", conditionType: Join::ON)
            ->leftJoin("ca.cell", "c", conditionType: Join::ON)
            ->leftJoin("cc.owner", "co", conditionType: Join::ON)
            ->leftJoin("cc.events", "ce", conditionType: Join::ON)
            ->addGroupBy("cc.id")
            ->addGroupBy("ca.id")
            ->addGroupBy("c.id")
            ->addGroupBy("co.id")
            ->addGroupBy("ce.id")
            ->orderBy("priority", "ASC")
            ->addOrderBy("cc.incubator", "ASC")
            ->addOrderBy("co.fullName", "ASC")
            ->where(
                $qb->expr()
                    ->orX("cc.unfrozenOn >= :start and cc.unfrozenOn <= :end")
                    ->add("cc.trashedOn >= :start and cc.trashedOn <= :end")
                    ->add("cc.unfrozenOn < :start and cc.trashedOn IS NULL")
                    ->add("cc.unfrozenOn < :start and cc.trashedOn > :end")
            )
            /*->where("cc.unfrozenOn >= :start and cc.unfrozenOn <= :end")
            ->orWhere("cc.trashedOn >= :start and cc.trashedOn <= :end")
            ->orWhere("cc.unfrozenOn < :start and cc.trashedOn IS NULL")
            ->orWhere("cc.unfrozenOn < :start and cc.trashedOn > :end")*/
            ->setParameter("start", $start)
            ->setParameter("end", $end)
        ;

        if ($incubator) {
            $qb->andWhere("cc.incubator LIKE :incubator");
            $qb->setParameter("incubator", "%$incubator%");
        }

        if ($scientist) {
            $qb->andWhere("co.fullName LIKE :scientist");
            $qb->setParameter("scientist", "%$scientist%");
        }

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
