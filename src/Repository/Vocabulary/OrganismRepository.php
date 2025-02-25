<?php
declare(strict_types=1);

namespace App\Repository\Vocabulary;

use App\Entity\DoctrineEntity\Vocabulary\Organism;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organism>
 */
class OrganismRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organism::class);
    }
}
