<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExperimentalCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalCondition>
 */
class ExperimentalConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalCondition::class);
    }
}
