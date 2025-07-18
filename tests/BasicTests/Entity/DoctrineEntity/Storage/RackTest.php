<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\DoctrineEntity\Storage;

use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use PHPUnit\Framework\TestCase;
use TypeError;

class RackTest extends TestCase
{
    public function testStringify(): void
    {
        $rack = new Rack();

        // Test that "no name" does not lead to an error
        $this->assertSame("unknown", (string) $rack);

        // Set name and test result
        $name = "Rack 3";
        $rack->setName($name);
        $this->assertSame($name, (string) $rack);
    }

    public function testName(): void
    {
        $rack = new Rack();
        $name = "Rack 1";

        $this->assertNull($rack->getName());

        $rack->setName($name);
        $this->assertSame($name, $rack->getName());

        $rack->setName(null);
        $this->assertNull($rack->getName());
    }

    public function testMaxBoxes(): void
    {
        $rack = new Rack();
        $max = 9;

        $this->assertSame(0, $rack->getMaxBoxes());

        $rack->setMaxBoxes($max);
        $this->assertSame($max, $rack->getMaxBoxes());

        $rack->setMaxBoxes(0);
        $this->assertSame(0, $rack->getMaxBoxes());

        $this->expectException(TypeError::class);
        $rack->setMaxBoxes(null);  // @phpstan-ignore argument.type
    }

    public function testPinCode(): void
    {
        $rack = new Rack();
        $pinCode = "E666";

        $this->assertNull($rack->getPinCode());

        $rack->setPinCode($pinCode);
        $this->assertSame($pinCode, $rack->getPinCode());

        $rack->setPinCode(null);
        $this->assertNull($rack->getPinCode());
    }

    public function testParent(): void
    {
        $rack = new Rack();
        $parentRack = $this->createMock(Rack::class);

        $this->assertNull($rack->getParent());

        $parentRack
            ->expects($this->once())
            ->method("addChild")
            ->with($rack)
        ;

        $rack->setParent($parentRack);
        $this->assertSame($parentRack, $rack->getParent());

        $rack->setParent(null);
        $this->assertNull($rack->getParent());
    }

    public function testChildren(): void
    {
        $rack = new Rack();

        $children = [
            $this->createMock(Rack::class),
            $this->createMock(Rack::class),
        ];

        $this->assertCount(0, $rack->getChildren());

        $children[1]
            ->expects($this->once())
            ->method("setParent")
            ->with($rack);

        array_map(fn(Rack $child) => $rack->addChild($child), $children);

        $this->assertCount(2, $rack->getChildren());
        array_map(fn(Rack $child) => $this->assertContains($child, $rack->getChildren()), $children);

        $children[0]
            ->expects($this->once())
            ->method("getParent")
            ->willReturn($rack)
        ;
        $children[0]
            ->expects($this->once())
            ->method("setParent")
            ->with(null)
        ;

        $rack->removeChild($children[0]);

        $this->assertCount(1, $rack->getChildren());
        $this->assertNotContains($children[0], $rack->getChildren());
        $this->assertContains($children[1], $rack->getChildren());
    }

    public function testBoxes(): void
    {
        $rack = new Rack();

        $boxes = [
            $this->createMock(Box::class),
            $this->createMock(Box::class),
        ];

        $this->assertCount(0, $rack->getBoxes());

        $boxes[1]
            ->expects($this->once())
            ->method("setRack")
            ->with($rack);

        array_map(fn(Box $box) => $rack->addBox($box), $boxes);

        $this->assertCount(2, $rack->getBoxes());
        array_map(fn(Box $box) => $this->assertContains($box, $rack->getBoxes()), $boxes);

        $boxes[0]
            ->expects($this->once())
            ->method("getRack")
            ->willReturn($rack)
        ;
        $boxes[0]
            ->expects($this->once())
            ->method("setRack")
            ->with(null)
        ;

        $rack->removeBox($boxes[0]);

        $this->assertCount(1, $rack->getBoxes());
        $this->assertNotContains($boxes[0], $rack->getBoxes());
        $this->assertContains($boxes[1], $rack->getBoxes());
    }
}
