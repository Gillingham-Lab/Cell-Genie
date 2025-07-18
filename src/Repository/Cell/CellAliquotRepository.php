<?php
declare(strict_types=1);

namespace App\Repository\Cell;

use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Storage\Box;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CellAliquot>
 */
class CellAliquotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CellAliquot::class);
    }

    /**
     * @param Box[] $boxes
     * @return CellAliquot[]|null
     */
    public function findAllFromBoxes(array $boxes)
    {
        $qb = $this->createQueryBuilder("ca");

        $boxUlids = [];
        foreach ($boxes as $box) {
            $boxUlids[] = $box->getUlid()->toRfc4122();
        }

        return $qb->select("ca")
            ->distinct(true)
            ->leftJoin("ca.box", "b", Join::ON)
            ->where("ca.box IN (:boxes)")
            ->setParameter("boxes", $boxUlids)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return CellAliquote[] Returns an array of CellAliquote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CellAliquote
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
