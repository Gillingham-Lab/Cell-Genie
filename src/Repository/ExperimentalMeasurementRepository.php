<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExperimentalMeasurement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExperimentalMeasurement>
 */
class ExperimentalMeasurementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExperimentalMeasurement::class);
    }
}
