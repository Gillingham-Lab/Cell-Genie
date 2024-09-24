<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Genie\Enums\DatumEnum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExperimentalRunConditionTest extends TestCase
{
    public function testExperimentalRun(): void
    {
        $condition = new ExperimentalRunCondition();
        $run = $this->createMock(ExperimentalRun::class);
        $run->expects($this->once())
            ->method("addCondition")
            ->with($condition)
        ;

        // Assert default is null
        $this->assertNull($condition->getExperimentalRun());

        // Set and test
        $condition->setExperimentalRun($run);
        $this->assertSame($run, $condition->getExperimentalRun());

        // Set to null again
        $condition->setExperimentalRun(null);
        $this->assertNull($condition->getExperimentalRun());
    }

    public function testName(): void
    {
        $condition = new ExperimentalRunCondition();
        $name = "spam and eggs with spam";

        // Assert default is null
        $this->assertNull($condition->getName());

        // Set and test
        $condition->setName($name);
        $this->assertSame($name, $condition->getName());
    }

    public function testControl(): void
    {
        $condition = new ExperimentalRunCondition();

        // Assert default is false
        $this->assertFalse($condition->isControl());

        // Set to true
        $condition->setControl(true);

        // Check if true
        $this->assertTrue($condition->isControl());
    }

    public function testData(): void
    {
        $condition = new ExperimentalRunCondition();

        $mockData = [
            (new ExperimentalDatum())
                ->setName("key")
                ->setType(DatumEnum::String)
                ->setValue("keyValue"),
            (new ExperimentalDatum())
                ->setName("value")
                ->setType(DatumEnum::Int8)
                ->setValue(42),
        ];

        // Assert collection is empty
        $this->assertCount(0, $condition->getData());

        // Add Datasets
        $condition->addData($mockData[0]);
        $condition->addData($mockData[1]);

        // Assert collection is 2
        $this->assertCount(2, $condition->getData());
        $this->assertContains($mockData[0], $condition->getData());
        $this->assertContains($mockData[1], $condition->getData());

        // Check key access
        $this->assertTrue($condition->getData()->containsKey("key"));
        $this->assertTrue($condition->getData()->containsKey("value"));
        $this->assertSame($mockData[0], $condition->getData()["key"]);
        $this->assertSame($mockData[1], $condition->getData()["value"]);
        $this->assertSame($mockData[0], $condition->getDatum("key"));
        $this->assertSame($mockData[1], $condition->getDatum("value"));

        // Remove a datum
        $condition->removeData($mockData[0]);
        $this->assertCount(1, $condition->getData());
        $this->assertNotContains($mockData[0], $condition->getData());
        $this->assertContains($mockData[1], $condition->getData());
        $this->assertFalse($condition->getData()->containsKey("key"));
        $this->assertTrue($condition->getData()->containsKey("value"));

        // Check exception
        $this->expectException(InvalidArgumentException::class);
        $condition->getDatum("gatekeeper");
    }
}
