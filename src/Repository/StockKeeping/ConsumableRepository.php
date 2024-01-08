<?php
declare(strict_types=1);

namespace App\Repository\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Consumable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consumable[]    findAll()
 * @method Consumable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Consumable|null find($id, $lockMode = null, $lockVersion = null)
 */
class ConsumableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consumable::class);
    }
}