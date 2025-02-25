<?php
declare(strict_types=1);

namespace App\Repository\File;

use App\Entity\DoctrineEntity\File\FileBlob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileBlob>
 */
class FileBlobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileBlob::class);
    }
}
