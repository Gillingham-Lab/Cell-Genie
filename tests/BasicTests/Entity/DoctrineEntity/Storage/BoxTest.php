<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\DoctrineEntity\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use PHPUnit\Framework\TestCase;

class BoxTest extends TestCase
{
    public function testToString(): void
    {
        $box = new Box();
        $name = "Box 1";

        // Test if no name was set
        $this->assertSame("no rack | no name (1 × 1)", (string) $box);

        // Set name and test
        $box->setName($name);
        $this->assertSame("no rack | {$name} (1 × 1)", (string) $box);

        // Set parent and test
        $rack = $this->createMock(Rack::class);
        $rack
            ->method("getPathName")
            ->willReturn("Rack 1")
        ;

        $box->setRack($rack);
        $this->assertSame("Rack 1 | {$name} (1 × 1)", (string) $box);
    }

    public function testToPathname(): void
    {
        $box = new Box();
        $rack = $this->createMock(Rack::class);
        $rack->method("getPathName")->willReturn("Rack 1");

        // Check without set rack
        $this->assertSame("no rack", $box->getPathName());

        // Check with rack
        $box->setRack($rack);
        $this->assertSame("Rack 1", $box->getPathName());
    }

    public function testName(): void
    {
        $box = new Box();
        $name = "Box 1";

        $this->assertNull($box->getName());

        $box->setName($name);
        $this->assertSame($name, $box->getName());
    }

    public function testRows(): void
    {
        $box = new Box();
        $rows = 6;

        $this->assertSame(1, $box->getRows());

        $box->setRows($rows);
        $this->assertSame($rows, $box->getRows());
    }

    public function testCols(): void
    {
        $box = new Box();
        $cols = 6;

        $this->assertSame(1, $box->getCols());

        $box->setCols($cols);
        $this->assertSame($cols, $box->getCols());
    }

    public function testDescription(): void
    {
        $box = new Box();
        $description = "This is a nice box.";

        $this->assertNull($box->getDescription());

        $box->setDescription($description);
        $this->assertSame($description, $box->getDescription());
    }

    public function testRack(): void
    {
        $box = new Box();
        $rack = $this->createMock(Rack::class);

        $this->assertNull($box->getRack());

        $box->setRack($rack);
        $this->assertSame($rack, $box->getRack());

        $box->setRack(null);
        $this->assertNull($box->getRack());
    }
}
