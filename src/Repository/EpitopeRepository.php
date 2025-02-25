<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\Epitope;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Epitope>
 */
class EpitopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Epitope::class);
    }
}
