<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Genie\Enums\DatumEnum;
use App\Service\Doctrine\Type\Ulid;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

class ExperimentDatumTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function integerDatumProvider(): array
    {
        return [
            ["int64_positive", "int64", 2**62-1],
            ["int64_negative", "int64", -2**62-1],
            ["int64_positive", "int", 2**62-1],
            ["int64_negative", "int", -2**62-1],
            ["int32_positive", "int32", 2147483647],
            ["int32_negative", "int32", -2147483648],
            ["int16_positive", "int16", 32767],
            ["int16_negative", "int16", -32768],
            ["int8_positive", "int8", 127],
            ["int8_negative", "int8", -128],
            ["uint32_positive", "uint32", 2147483648],
            ["uint32_positive", "uint32", 4294967295],
            ["uint16_positive", "uint16", 32768],
            ["uint16_positive", "uint16", 65535],
            ["uint8_positive", "uint8", 128],
            ["uint8_positive", "uint8", 255],
        ];
    }

    /**
     * @dataProvider integerDatumProvider
     */
    public function testIntegerDatum(string $name, string $type, int $value): void
    {
        $datum = new ExperimentalDatum();
        $datum->setName($name);
        $datum->setType(DatumEnum::from($type));
        $datum->setValue($value);

        $this->entityManager->persist($datum);
        $this->entityManager->flush();

        $this->assertInstanceOf(Ulid::class, $datum->getId());
        $this->assertSame($value, $datum->getValue());
        $this->assertSame($name, $datum->getName());
        $this->assertSame(DatumEnum::from($type), $datum->getType());

        $id = $datum->getId();

        // Retrieve again
        $this->entityManager->clear();
        $repository = $this->entityManager->getRepository(ExperimentalDatum::class);
        $datum = $repository->find($id);

        $this->assertInstanceOf(Ulid::class, $datum->getId());
        $this->assertSame($value, $datum->getValue());
    }

    public function floatDatumProvider(): array
    {
        return [
            ["float32", "float32", 0.0001, 1],
            ["float32", "float32", 0.0001, 1.00001],
            ["float32", "float32", 0.0001, 10],
            ["float32", "float32", 0.0001, 10.00001],
            ["float64", "float64", 0.0001, 1],
            ["float64", "float64", 0.000000001, 1.00001],
            ["float64", "float64", 0.000000001, 1.000000001],
            ["float64", "float64", 0.000000001, 10],
            ["float64", "float64", 0.000000001, 10.00001],
            ["float64", "float64", 0.000000001, 10.000000001],
        ];
    }

    /**
     * @dataProvider floatDatumProvider
     */
    public function testFloatDatum(string $name, string $type, float $delta, float $value): void
    {
        $datum = new ExperimentalDatum();
        $datum->setName($name);
        $datum->setType(DatumEnum::from($type));
        $datum->setValue($value);

        $this->entityManager->persist($datum);
        $this->entityManager->flush();

        $this->assertInstanceOf(Ulid::class, $datum->getId());

        $this->assertEqualsWithDelta($value, $datum->getValue(), $delta);

        $id = $datum->getId();

        // Retrieve again
        $this->entityManager->clear();
        $repository = $this->entityManager->getRepository(ExperimentalDatum::class);
        $datum = $repository->find($id);

        $this->assertInstanceOf(Ulid::class, $datum->getId());
        $this->assertEqualsWithDelta($value, $datum->getValue(), $delta);
    }

    public function uidDatumProvider(): array
    {
        return [
            ["uuid4", "uuid", Uuid::v4()],
            ["uuid1", "uuid", Uuid::v1()],
            ["ulid", "uuid", new Ulid()],
        ];
    }

    /**
     * @dataProvider uidDatumProvider
     */
    public function testUidDatum(string $name, string $type, AbstractUid $value): void
    {
        $datum = new ExperimentalDatum();
        $datum->setName($name);
        $datum->setType(DatumEnum::from($type));
        $datum->setValue($value);

        $this->entityManager->persist($datum);
        $this->entityManager->flush();

        $this->assertInstanceOf(Ulid::class, $datum->getId());
        $this->assertInstanceOf(AbstractUid::class, $datum->getValue());
        $this->assertSame($value->toHex(), $datum->getValue()->toHex());

        $id = $datum->getId();

        // Retrieve again
        $this->entityManager->clear();
        $repository = $this->entityManager->getRepository(ExperimentalDatum::class);
        $datum = $repository->find($id);

        $this->assertInstanceOf(Ulid::class, $datum->getId());
        $this->assertInstanceOf(AbstractUid::class, $datum->getValue());
        $this->assertSame($value->toHex(), $datum->getValue()->toHex());
    }

    public function entityReferenceDatumProvider(): array
    {
        return [
            ["substance", DatumEnum::EntityReference, Antibody::class, ["number" => "AB001"]],
            ["cell", DatumEnum::EntityReference, Cell::class, ["cellNumber" => "CL001"]],
        ];
    }

    /**
     * @dataProvider entityReferenceDatumProvider
     */
    public function testEntityReference(string $name, DatumEnum $type, string $class, array $search)
    {
        $idObject = $this->entityManager->getRepository($class)->findOneBy($search);

        if (method_exists($idObject, "getUlid")) {
            $id = $idObject->getUlid();
        } else {
            $id = $idObject->getId();
        }
        $realClassName = ClassUtils::getClass($idObject);

        $datum = (new ExperimentalDatum())
            ->setName($name)
            ->setType($type)
            ->setValue($idObject)
        ;

        $this->assertSame((string)$id, (string)$datum->getValue()[0]);
        $this->assertSame($realClassName, $datum->getValue()[1]);

        // Persist
        $this->entityManager->persist($datum);
        $this->entityManager->flush();

        // Get Datum ID
        $datumId = $datum->getId();

        // Retrieve again
        $this->entityManager->clear();
        $repository = $this->entityManager->getRepository(ExperimentalDatum::class);
        $datum = $repository->find($datumId);

        $this->assertSame((string)$id, (string)$datum->getValue()[0]);
        $this->assertSame($realClassName, $datum->getValue()[1]);

        if ($id instanceof AbstractUid) {
            $this->assertSame((string)$id, (string)$datum->getReferenceUuid());
        } else {
            // Some entities (cells ...) still have numeric ids
            // thus, their ID does not match the ulid returned by getReferenceUuid
            // We need to check that differently
            $pseudoUuid = Uuid::fromBinary(pack("P", 0) . pack("P", $id));
            $this->assertSame((string)$pseudoUuid, (string)$datum->getReferenceUuid());
        }
    }

    public function testThrowsExceptionIfEntityDoesNotHaveGetIdMethod()
    {
        $class = new class() {

        };

        $object = new $class();

        $this->expectException(InvalidArgumentException::class);

        $datum = (new ExperimentalDatum())
            ->setName("datum-test")
            ->setType(DatumEnum::EntityReference)
            ->setValue($object)
        ;
    }

    public function testThrowsExceptionIfEntityIdIsNotSupported()
    {
        $class = new class() {
            public function getId(): string {
                return "001";
            }
        };

        $object = new $class();

        $this->expectException(InvalidArgumentException::class);

        $datum = (new ExperimentalDatum())
            ->setName("datum-test")
            ->setType(DatumEnum::EntityReference)
            ->setValue($object)
        ;
    }

    public function testThrowsExceptionIfDatumTypeIsNotSetAndValueIsSet()
    {
        $this->expectException(LogicException::class);

        $datum = (new ExperimentalDatum())
            ->setName("datum-test")
            ->setValue(15)
        ;
    }

    public function testThrowsExceptionIfDatumTypeIsNotSetAndValueIsRetrieved()
    {
        $this->expectException(LogicException::class);
        $datum = (new ExperimentalDatum())
            ->setName("datum-test")
        ;
        $datum->getValue();
    }

    public function testBase64()
    {
        $datum = (new ExperimentalDatum())
            ->setName("datum-test")
        ;

        $this->assertNull($datum->asBase64());

        $datum->setType(DatumEnum::UInt16);
        $datum->setValue(16);

        $this->assertSame("ABA=", $datum->asBase64());

        // Persist
        $this->entityManager->persist($datum);
        $this->entityManager->flush();

        // Get Datum ID
        $datumId = $datum->getId();

        // Retrieve again
        $this->entityManager->clear();
        $repository = $this->entityManager->getRepository(ExperimentalDatum::class);
        $datum = $repository->find($datumId);

        $this->assertSame("ABA=", $datum->asBase64());
    }

    public function uidDateProvider(): array
    {
        return [
            ["date1", "date", "2020-06-01"],
            ["date2", "date", "2020-06-01"],
            ["date2", "date", "2020-06-01"],
        ];
    }

    /**
     * @dataProvider uidDateProvider
     */
    public function testDateDatum(string $name, string $type, string $value): void
    {
        $date = new \DateTime($value);

        $datum = new ExperimentalDatum();
        $datum->setName($name);
        $datum->setType(DatumEnum::from($type));
        $datum->setValue($date);

        $this->entityManager->persist($datum);
        $this->entityManager->flush();

        $this->assertInstanceOf(Ulid::class, $datum->getId());
        $this->assertInstanceOf(\DateTime::class, $datum->getValue());
        $this->assertSame($date->getTimestamp(), $datum->getValue()->getTimestamp());

        $id = $datum->getId();

        // Retrieve again
        $this->entityManager->clear();
        $repository = $this->entityManager->getRepository(ExperimentalDatum::class);
        $datum = $repository->find($id);

        $this->assertInstanceOf(Ulid::class, $datum->getId());
        $this->assertInstanceOf(\DateTime::class, $datum->getValue());
        $this->assertSame($date->getTimestamp(), $datum->getValue()->getTimestamp());
    }
}