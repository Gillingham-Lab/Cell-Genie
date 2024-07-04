<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Enums\DatumEnum;
use App\Repository\Experiment\ExperimentalDatumRepository;
use App\Service\Doctrine\Type\Ulid;
use App\Service\Doctrine\Type\UlidType;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExperimentalDatumRepository::class)]
#[ORM\Table("new_experimental_datum")]
#[ORM\Index(fields: ["referenceUuid"])]
final class ExperimentalDatum
{
    use IdTrait;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, enumType: DatumEnum::class)]
    private ?DatumEnum $type = null;

    /** @var ?resource */
    #[ORM\Column(type: Types::BINARY)]
    private $value = null;

    #[ORM\Column(type: UlidType::NAME, nullable: true, insertable: false, updatable: false, columnDefinition: "uuid GENERATED ALWAYS AS (CASE WHEN type = 'entityReference' OR type = 'uuid' THEN CAST(ENCODE(substring(value from 0 for 17), 'hex') AS uuid) ELSE null END) STORED", generated: "ALWAYS")]
    private ?Ulid $referenceUuid = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?DatumEnum
    {
        return $this->type;
    }

    public function setType(DatumEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->decode($this->value);
    }

    public function setValue(mixed $value): self
    {
        $this->value = $this->encode($value);

        return $this;
    }

    public function asBase64(): ?string
    {
        if (is_null($this->value)) {
            return null;
        }

        if (is_string($this->value)) {
            return base64_encode($this->value);
        }

        return base64_encode(stream_get_contents($this->value, -1, 0));
    }

    private function encode($value): string
    {
        if ($this->type === null) {
            throw new LogicException("The type of a value must be set before you set the value.");
        }

        $value = $this->normalize($value);

        $encodedValue = match($this->type) {
            DatumEnum::Int, DatumEnum::Int64 => pack("P", $value),
            DatumEnum::Int32, DatumEnum::UInt32 => pack("V", $value),
            DatumEnum::Int16, DatumEnum::UInt16 => pack("v", $value),
            DatumEnum::Int8 => pack("c", $value),
            DatumEnum::UInt8 => pack("C", $value),
            DatumEnum::Float32 => pack("g", $value),
            DatumEnum::Float64 => pack("e", $value),
            DatumEnum::Uuid, DatumEnum::EntityReference => $value,
            DatumEnum::String => (string)$value,
        };

        return $encodedValue;
    }

    private function normalize($value)
    {
        if ($this->type === DatumEnum::Uuid) {
            if ($value instanceof AbstractUid) {
                $value = $value->toBinary();
            } else {
                $value = Uuid::fromString($value)->toBinary();
            }
        } elseif ($this->type === DatumEnum::EntityReference) {
            // Check if id method exists
            if (method_exists($value, "getUlid")) {
                $id = $value->getUlid();
            } elseif (method_exists($value, "getId")) {
                $id = $value->getId();
            } else {
                throw new InvalidArgumentException("A value for entityReference must have an getId / getUlid method");
            }

            // Convert ID to binary. Uud is 16 Bytes.
            if ($id instanceof AbstractUid) {
                $normalizedValue = $id->toBinary();
            } elseif (is_int($id)) {
                // If the ID is numeric, we pack first 8 bytes of 0, then the ID
                $normalizedValue = pack("P", 0) . pack("P", $id);
            } else {
                $type = get_debug_type($id);
                throw new InvalidArgumentException("The ID of the entity must either be an uid or an number, {$type} given.");
            }

            // Finally, we add the FQCN
            $className = ClassUtils::getClass($value);
            $value = $normalizedValue . $className;
        }

        return $value;
    }

    private function decode($stream): mixed
    {
        if ($this->type === null) {
            throw new LogicException("The value can only be decoded if a type is set.");
        }

        if (is_resource($stream)) {
            $value = stream_get_contents($stream, -1, 0);
        } else {
            $value = $stream;
        }

        $decodedValue = match($this->type) {
            DatumEnum::Int, DatumEnum::Int64 => unpack("P", $value)[1],
            DatumEnum::Int32, DatumEnum::UInt32 => unpack("V", $value)[1],
            DatumEnum::Int16, DatumEnum::UInt16 => unpack("v", $value)[1],
            DatumEnum::Int8 => unpack("c", $value)[1],
            DatumEnum::UInt8 => unpack("C", $value)[1],
            DatumEnum::Float32 => unpack("g", $value)[1],
            DatumEnum::Float64 => unpack("e", $value)[1],
            DatumEnum::String => $value,
            DatumEnum::Uuid => Ulid::fromBinary($value),
            DatumEnum::EntityReference => $this->denormalize($value),
        };

        // We always store as little-endian. As PHP only supports unsigned integers with guaranteed byte order, the
        // value first gets unpacked as an unsigned integer and then gets converted to the signed variant.
        if ($this->type === DatumEnum::Int64 or $this->type === DatumEnum::Int) {
            $decodedValue = $decodedValue >= 2**63 ? $decodedValue - 2**64 : $decodedValue;
        } elseif ($this->type === DatumEnum::Int32) {
            $decodedValue = $decodedValue >= 2**31 ? $decodedValue - 2**32 : $decodedValue;
        } elseif ($this->type === DatumEnum::Int16) {
            $decodedValue = $decodedValue >= 2**15 ? $decodedValue - 2**16 : $decodedValue;
        }

        return $decodedValue;
    }

    public function denormalize($value)
    {
        if (str_starts_with($value, "\0\0\0\0\0\0\0\0")) {
            $id = unpack("P2", $value)[2];
        } else {
            $id = Ulid::fromBinary(substr($value, 0, 16));
        }

        $className = substr($value, 16);
        return [$id, $className];
    }

    public function getReferenceUuid(): ?Ulid
    {
        return $this->referenceUuid;
    }
}
