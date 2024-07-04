<?php
declare(strict_types=1);

namespace App\Repository\Form;

use App\Entity\DoctrineEntity\Form\FormRow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FormRow|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormRow|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormRow[]    findAll()
 * @method FormRow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormRowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, FormRow::class);
    }
}