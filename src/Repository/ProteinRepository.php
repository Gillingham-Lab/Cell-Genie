<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Antibody;
use App\Entity\Cell;
use App\Entity\Epitope;
use App\Entity\Protein;
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
            ->leftJoin("p.epitopes", "ep", conditionType: Join::ON)
            ->leftJoin("ep.antibodies", "ab", conditionType: Join::ON)
            ->groupBy("p.ulid")
            ->addGroupBy("ep.id")
            ->addGroupBy("ab.ulid")
        ;

        if ($epitope) {
            $qb = $qb->where("ep.id = :epitope")
                ->setParameter("epitope", $epitope->getId(), "ulid");
        }

        if ($orderBy) {
            foreach ($orderBy as $col => $ord) {
                $qb = $qb->orderBy($col, $ord);
            }
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Protein[] Returns an array of Protein objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Protein
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
