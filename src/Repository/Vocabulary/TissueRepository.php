<?php
declare(strict_types=1);

namespace App\Repository\Vocabulary;

use App\Entity\DoctrineEntity\Vocabulary\Tissue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tissue>
 */
class TissueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tissue::class);
    }
}
