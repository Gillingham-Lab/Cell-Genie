<?php
declare(strict_types=1);

namespace App\Repository\Substance;

use App\Entity\DoctrineEntity\Substance\Plasmid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Plasmid|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plasmid|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plasmid[]    findAll()
 * @method Plasmid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlasmidRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plasmid::class);
    }

    /**
     * @return array[Oligo, int]
     */
    public function findAllWithLotCount(): array
    {
        return $this->createQueryBuilder("p")
            ->addSelect("COUNT(l)")
            ->leftJoin("p.lots", "l")
            ->groupBy("p.ulid")
            ->addGroupBy("l.id")
            ->addOrderBy("p.number", "ASC")
            ->addOrderBy("p.shortName", "ASC")
            ->addOrderBy("p.longName", "ASC")
            ->getQuery()
            ->getResult()
            ;
    }
}
