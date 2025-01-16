<?php
declare(strict_types=1);

namespace App\Repository\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Box>
 */
class BoxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Box::class);
    }
}
