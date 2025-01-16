<?php
declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalDesignField>
 */
class ExperimentalDesignFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalDesignField::class);
    }
}