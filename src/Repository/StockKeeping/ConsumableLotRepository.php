<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Genie\Enums\Availability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConsumableLot|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsumableLot[]    findAll()
 * @method ConsumableLot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method ConsumableLot|null find($id, $lockMode = null, $lockVersion = null)
 */
class ConsumableLotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumableLot::class);
    }

    /**
     * @param Rack $rack
     * @return ConsumableLot[]
     */
    public function getLotsByLocation(Rack $rack)
    {
        $qb = $this->createQueryBuilder("cl")
            ->leftJoin("cl.consumable", "c")
            ->where("cl.location = :location")
            ->andWhere("cl.availability != :unavailable")
            ->setParameter("location", $rack->getUlid())
            ->setParameter("unavailable", Availability::Empty)
        ;

        return $qb->getQuery()->getResult();
    }
}
