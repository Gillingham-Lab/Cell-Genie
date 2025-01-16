<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Genie\Enums\Availability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsumableLot>
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
    public function getLotsByLocation(Rack $rack): array
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
