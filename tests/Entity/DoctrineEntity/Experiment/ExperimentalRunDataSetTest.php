<?php
declare(strict_types=1);

namespace App\Tests\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use App\Genie\Enums\DatumEnum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExperimentalRunDataSetTest extends TestCase
{
    public function testExperimentalRun()
    {
        $set = new ExperimentalRunDataSet();
        $run = $this->createMock(ExperimentalRun::class);

        $run->expects($this->once())
            ->method("addDataSet")
            ->with($set)
        ;

        // First check if set to null
        $this->assertNull($set->getExperiment());

        // Set and check if set correctly
        $set->setExperiment($run);
        $this->assertSame($run, $set->getExperiment());

        // Set to null again
        $set->setExperiment(null);
        $this->assertNull($set->getExperiment());
    }

    public function testCondition()
    {
        $set = new ExperimentalRunDataSet();
        $condition = $this->createMock(ExperimentalRunCondition::class);

        // First check if set to null
        $this->assertNull($set->getCondition());

        // Set and check if set correctly
        $set->setCondition($condition);
        $this->assertSame($condition, $set->getCondition());

        // Set to null again
        $set->setCondition(null);
        $this->assertNull($set->getCondition());
    }

    public function testControlCondition()
    {
        $set = new ExperimentalRunDataSet();
        $condition = $this->createMock(ExperimentalRunCondition::class);

        // First check if set to null
        $this->assertNull($set->getControlCondition());

        // Set and check if set correctly
        $set->setControlCondition($condition);
        $this->assertSame($condition, $set->getControlCondition());

        // Set to null again
        $set->setControlCondition(null);
        $this->assertNull($set->getControlCondition());
    }

    public function testData()
    {
        $set = new ExperimentalRunDataSet();

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
        $this->assertCount(0, $set->getData());

        // Add Datasets
        $set->addData($mockData[0]);
        $set->addData($mockData[1]);

        // Assert collection is 2
        $this->assertCount(2, $set->getData());
        $this->assertContains($mockData[0], $set->getData());
        $this->assertContains($mockData[1], $set->getData());

        // Check key access
        $this->assertTrue($set->getData()->containsKey("key"));
        $this->assertTrue($set->getData()->containsKey("value"));
        $this->assertSame($mockData[0], $set->getData()["key"]);
        $this->assertSame($mockData[1], $set->getData()["value"]);
        $this->assertSame($mockData[0], $set->getDatum("key"));
        $this->assertSame($mockData[1], $set->getDatum("value"));

        // Remove a datum
        $set->removeData($mockData[0]);
        $this->assertCount(1, $set->getData());
        $this->assertNotContains($mockData[0], $set->getData());
        $this->assertContains($mockData[1], $set->getData());
        $this->assertFalse($set->getData()->containsKey("key"));
        $this->assertTrue($set->getData()->containsKey("value"));

        // Check exception
        $this->expectException(InvalidArgumentException::class);
        $set->getDatum("gatekeeper");
    }
}
