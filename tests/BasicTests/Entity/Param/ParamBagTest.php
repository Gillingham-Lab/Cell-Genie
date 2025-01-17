<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Param;

use App\Entity\Param\Param;
use App\Entity\Param\ParamBag;
use PHPUnit\Framework\TestCase;

class ParamBagTest extends TestCase
{
    public function testIfSettingValuesReturnsTheseValues(): void
    {
        $bag = new ParamBag();
        $bag["test1"] = 5;
        $bag["test2"] = "Hello World";

        $this->assertSame(5, $bag["test1"]);
        $this->assertSame("Hello World", $bag["test2"]);

        $param = $bag->getParam("test1");
        $this->assertInstanceOf(Param::class, $param);
        $this->assertSame(5, $param->getValue());

        $param = $bag->getParam("test2");
        $this->assertInstanceOf(Param::class, $param);
        $this->assertSame("Hello World", $param->getValue());
    }

    public function testIfGettingNonExistingValueReturnsDefault(): void
    {
        $bag = new ParamBag();
        $this->assertSame(1337, $bag->getParam("test1", 1337)->getValue());
    }

    public function testIfGettingNonExistingValueWithoutDefaultReturnsNull(): void
    {
        $bag = new ParamBag();
        $this->assertNull($bag->getParam("test1"));
    }

    public function testIfUnsettingValuesWorks()
    {
        $bag = new ParamBag();
        $bag["test1"] = 5;

        unset($bag["test1"]);

        $this->assertArrayNotHasKey("test1", $bag);
        $this->assertNull($bag->getParam("test1"));
    }

    public function testParamBagMergeFeature(): void
    {
        $bag1 = new ParamBag();
        $bag1["test1"] = 5;
        $bag1["test2"] = "Hello World";

        $bag2 = new ParamBag();
        $bag2["test1"] = 10;
        $bag2["test2"] = "Hello World 2";

        $mergedBag = $bag1->mergeBag($bag2);
        $this->assertSame(10, $mergedBag["test1"]);
        $this->assertSame("Hello World 2", $mergedBag["test2"]);
    }
}