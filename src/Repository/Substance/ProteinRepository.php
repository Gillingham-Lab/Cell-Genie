<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Epitope;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Protein|null find($id, $lockMode = null, $lockVersion = null)
 * @method Protein|null findOneBy(array $criteria, array $orderBy = null)
 * @method Protein[]    findAll()
 * @method Protein[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProteinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Protein::class);
    }

    public function findByCell(Cell $cell)
    {
        return $this->createQueryBuilder("p")
            ->leftJoin("p.experiments", "e", conditionType: Join::ON)
            ->leftJoin("e.cells", "ce", conditionType: Join::ON)
            ->andWhere("ce = :cell")
            ->setParameter("cell", $cell)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findWithAntibodies(Epitope $epitope = null, array $orderBy = null)
    {
        $qb = $this->createQueryBuilder("p")
            ->addSelect("ep")
            ->addSelect("ab")
            ->addSelect("pc")
            ->leftJoin("p.epitopes", "ep", conditionType: Join::ON)
            ->leftJoin("ep.antibodies", "ab", conditionType: Join::ON)
            ->leftJoin("p.children", "pc", conditionType: Join::ON)
            ->groupBy("p.ulid")
            ->addGroupBy("ep.id")
            ->addGroupBy("ab.ulid")
            ->addGroupBy("pc.ulid")
        ;

        if ($epitope) {
            $qb = $qb->where("ep.id = :epitope")
                ->setParameter("epitope", $epitope->getId(), "ulid");
        }

        if ($orderBy) {
            foreach ($orderBy as $col => $ord) {
                $qb = $qb->addOrderBy($col, $ord);
            }
        }

        $qb = $qb->addOrderBy("ab.number", "ASC");

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithAntibodiesAndLotCount(Epitope $epitope = null, array $orderBy = null)
    {
        $qb = $this->createQueryBuilder("p")
            ->addSelect("ep")
            ->addSelect("ab")
            ->addSelect("pc")
            ->addSelect("COUNT(l)")
            ->leftJoin("p.lots", "l")
            ->leftJoin("p.epitopes", "ep", conditionType: Join::ON)
            ->leftJoin("ep.antibodies", "ab", conditionType: Join::ON)
            ->leftJoin("p.children", "pc", conditionType: Join::ON)
            ->groupBy("p.ulid")
            ->addGroupBy("ep.id")
            ->addGroupBy("ab.ulid")
            ->addGroupBy("pc.ulid")
        ;

        if ($epitope) {
            $qb = $qb->where("ep.id = :epitope")
                ->setParameter("epitope", $epitope->getId(), "ulid");
        }

        if ($orderBy) {
            foreach ($orderBy as $col => $ord) {
                $qb = $qb->addOrderBy($col, $ord);
            }
        }

        $qb = $qb->addOrderBy("ab.number", "ASC");

        return $qb
            ->getQuery()
            ->getResult()
            ;
    }
}
