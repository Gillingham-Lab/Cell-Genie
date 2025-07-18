<?php declare(strict_types=1);

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CellCulture>
 */
class CellCultureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CellCulture::class);
    }

    /**
     *  @return CellCulture[]
     */
    public function findAllBetween(
        DateTimeInterface $start,
        DateTimeInterface $end,
        ?string $incubator = null,
        ?string $scientist = null,
    ): array {
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
            ->orderBy("priority", "ASC")
            ->addOrderBy("cc.incubator", "ASC")
            ->addOrderBy("co.fullName", "ASC")
            ->where(
                $qb->expr()
                    ->orX("cc.unfrozenOn >= :start and cc.unfrozenOn <= :end")
                    ->add("cc.trashedOn >= :start and cc.trashedOn <= :end")
                    ->add("cc.unfrozenOn < :start and cc.trashedOn IS NULL")
                    ->add("cc.unfrozenOn < :start and cc.trashedOn > :end"),
            )
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
