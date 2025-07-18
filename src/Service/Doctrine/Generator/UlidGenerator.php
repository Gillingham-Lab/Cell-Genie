<?php
declare(strict_types=1);

namespace App\Service\Doctrine\Generator;

use App\Service\Doctrine\Type\Ulid;
use App\Service\Factory\UlidFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

class UlidGenerator extends AbstractIdGenerator
{
    private ?UlidFactory $factory;

    public function __construct(?UlidFactory $factory = null)
    {
        $this->factory = $factory;
    }

    public function generateId(EntityManagerInterface $em, object|null $entity): Ulid
    {
        if ($this->factory) {
            return $this->factory->create();
        }

        return new Ulid();
    }
}
