<?php
declare(strict_types=1);

namespace App\Repository\Form;

use App\Entity\DoctrineEntity\Form\FormRow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormRow>
 */
class FormRowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormRow::class);
    }
}
