<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\DatumEnum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExperimentalRunTest extends TestCase
{
    public function testScientist()
    {
        $mockScientist = $this->createMock(User::class);
        $dataset = new ExperimentalRun();

        // Assert it is empty by default
        $this->assertNull($dataset->getScientist());

        // Set scientist
        $dataset->setScientist($mockScientist);
        $this->assertSame($mockScientist, $dataset->getScientist());
    }

    public function testName()
    {
        $dataset = new ExperimentalRun();

        // Assert it is empty by default
        $this->assertNull($dataset->getName());

        // Set name
        $name = "Miller-Urey-Experiment Nr. 1";
        $dataset->setName($name);
        $this->assertSame($name, $dataset->getName());
    }

    public function testConditions()
    {
        $dataset = new ExperimentalRun();

        $mockConditions = [
            $this->createMock(ExperimentalRunCondition::class),
            $this->createMock(ExperimentalRunCondition::class),
        ];

        $mockConditions[1]
            ->expects($this->once())
            ->method("setExperimentalRun")
            ->with($this->equalTo($dataset))
        ;

        // Assert collection is empty
        $this->assertCount(0, $dataset->getConditions());

        // Add conditions
        $dataset->addCondition($mockConditions[0]);
        $dataset->addCondition($mockConditions[1]);

        // Add other expectations
        $mockConditions[0]
            ->expects($this->once())
            ->method("getExperimentalRun")
            ->willReturn($dataset)
        ;
        $mockConditions[0]
            ->expects($this->once())
            ->method("setExperimentalRun")
            ->with($this->isNull())
        ;

        // Assert collection is 2
        $this->assertCount(2, $dataset->getConditions());
        $this->assertContains($mockConditions[0], $dataset->getConditions());
        $this->assertContains($mockConditions[1], $dataset->getConditions());

        // Remove a condition
        $dataset->removeCondition($mockConditions[0]);
        $this->assertCount(1, $dataset->getConditions());
        $this->assertNotContains($mockConditions[0], $dataset->getConditions());
        $this->assertContains($mockConditions[1], $dataset->getConditions());
    }

    public function testDataSet()
    {
        $dataset = new ExperimentalRun();

        $mockDataSets = [
            $this->createMock(ExperimentalRunDataSet::class),
            $this->createMock(ExperimentalRunDataSet::class),
        ];

        // Assert collection is empty
        $this->assertCount(0, $dataset->getDataSets());

        // We only check calls on the second dataset since we will be removing the first one
        $mockDataSets[1]
            ->expects($this->once())
            ->method("setExperiment")
            ->with($this->equalTo($dataset))
        ;

        // Add Datasets
        $dataset->addDataSet($mockDataSets[0]);
        $dataset->addDataSet($mockDataSets[1]);

        // Assert collection is 2
        $this->assertCount(2, $dataset->getDataSets());
        $this->assertContains($mockDataSets[0], $dataset->getDataSets());
        $this->assertContains($mockDataSets[1], $dataset->getDataSets());

        // Add other expectations
        $mockDataSets[0]
            ->expects($this->once())
            ->method("getExperiment")
            ->willReturn($dataset)
        ;
        $mockDataSets[0]
            ->expects($this->once())
            ->method("setExperiment")
            ->with($this->isNull())
        ;

        // Remove a dataset
        $dataset->removeDataSet($mockDataSets[0]);
        $this->assertCount(1, $dataset->getDataSets());
        $this->assertNotContains($mockDataSets[0], $dataset->getDataSets());
        $this->assertContains($mockDataSets[1], $dataset->getDataSets());
    }

    public function testData()
    {
        $run = new ExperimentalRun();

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
        $this->assertCount(0, $run->getData());

        // Add Datasets
        $run->addData($mockData[0]);
        $run->addData($mockData[1]);

        // Assert collection is 2
        $this->assertCount(2, $run->getData());
        $this->assertContains($mockData[0], $run->getData());
        $this->assertContains($mockData[1], $run->getData());

        // Check key access
        $this->assertTrue($run->getData()->containsKey("key"));
        $this->assertTrue($run->getData()->containsKey("value"));
        $this->assertSame($mockData[0], $run->getData()["key"]);
        $this->assertSame($mockData[1], $run->getData()["value"]);
        $this->assertSame($mockData[0], $run->getDatum("key"));
        $this->assertSame($mockData[1], $run->getDatum("value"));

        // Remove a datum
        $run->removeData($mockData[0]);
        $this->assertCount(1, $run->getData());
        $this->assertNotContains($mockData[0], $run->getData());
        $this->assertContains($mockData[1], $run->getData());
        $this->assertFalse($run->getData()->containsKey("key"));
        $this->assertTrue($run->getData()->containsKey("value"));

        // Check exception
        $this->expectException(InvalidArgumentException::class);
        $run->getDatum("gatekeeper");
    }
}
