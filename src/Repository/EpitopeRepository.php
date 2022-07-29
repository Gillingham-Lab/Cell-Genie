<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Epitope;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Epitope|null find($id, $lockMode = null, $lockVersion = null)
 * @method Epitope|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collection<int, Epitope>    findAll()
 * @method Collection<int, Epitope>    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpitopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Epitope::class);
    }
}
