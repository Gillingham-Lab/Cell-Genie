<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Morphology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Morphology>
 */
class MorphologyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Morphology::class);
    }
}
