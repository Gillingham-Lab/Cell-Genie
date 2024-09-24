<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Fields;

use App\Entity\Traits\Fields\IdTrait;
use App\Service\Doctrine\Type\Ulid;
use PHPUnit\Framework\TestCase;

class IdTraitTest extends TestCase
{
    public function testIfGetIdWithoutAnIdReturnsNull(): void
    {
        $testClass = $this->getObjectForTrait(IdTrait::class);

        $this->assertNull($testClass->getId());
    }

    public function testIfGenerateIdSetsAnIdAndIsReturnedByGetId(): void
    {
        $testClass = new class {
            use IdTrait;

            public function __construct()
            {
                $this->generateId();
            }
        };

        $this->assertNotNull($testClass->getId());
        $this->assertInstanceOf(Ulid::class, $testClass->getId());
    }
}
