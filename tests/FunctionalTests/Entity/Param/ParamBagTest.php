<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Entity\Param;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParamBagTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
    }
}