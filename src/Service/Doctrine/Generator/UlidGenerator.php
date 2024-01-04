<?php
declare(strict_types=1);

namespace App\Service\Doctrine\Generator;

use App\Service\Doctrine\Type\Ulid;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\Uid\Factory\UlidFactory;

class UlidGenerator extends AbstractIdGenerator
{
    private ?UlidFactory $factory;

    public function __construct(UlidFactory $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * doctrine/orm < 2.11 BC layer.
     */
    public function generate(EntityManager $em, $entity): Ulid
    {
        return $this->generateId($em, $entity);
    }

    public function generateId(EntityManagerInterface $em, $entity): Ulid
    {
        if ($this->factory) {
            return $this->factory->create();
        }

        return new Ulid();
    }
}
