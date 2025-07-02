<?php
declare(strict_types=1);

namespace App\Genie\Codec;

use App\Genie\Enums\DatumEnum;
use App\Service\Doctrine\Type\Ulid;
use DateTime;
use Doctrine\Persistence\Proxy;
use InvalidArgumentException;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

/**
 * Encodes values into binary data and decoded binary data into values.
 * Value type must be set by using DatumEnum.
 * @template TType of DatumEnum
 * @phpstan-type DatumReturnType (
 *   TType is DatumEnum::EntityReference ? array{Ulid|int, class-string} : (
 *       TType is DatumEnum::Uuid ? Uuid : (
 *           TType is DatumEnum::Date ? DateTime : (
 *               TType is DatumEnum::String|DatumEnum::Image ? string : (
 *                   TType is DatumEnum::ErrorFloat ? array{float, float, float, float} : (
 *                      TType is DatumEnum::Float32|DatumEnum::Float64 ? float : int
 *                   )
 *               )
 *           )
 *       )
 *   )
 *  )
 */
readonly class ExperimentValueCodec
{
    public function __construct(
        /** @var TType */
        private DatumEnum $type
    ) {
    }

    public function normalizeUuidDatum(string|AbstractUid $value): string
    {
        if ($value instanceof AbstractUid) {
            $value = $value->toBinary();
        } else {
            $value = Uuid::fromString($value)->toBinary();
        }

        return $value;
    }

    /**
     * @param array{AbstractUid, class-string}|object $value
     * @return string
     */
    public function normalizeEntityDatum(array|object $value): string
    {
        if (is_object($value)) {
            // Check if id method exists
            if (method_exists($value, "getUlid")) {
                $id = $value->getUlid();
            } elseif (method_exists($value, "getId")) {
                $id = $value->getId();
            } else {
                throw new InvalidArgumentException("A value for entityReference must have an getId / getUlid method");
            }

            if ($value instanceof Proxy) {
                $className = get_parent_class($value);
            } else {
                $className = get_class($value);
            }
        } else {
            $id = $value[0];
            $className = $value[1];
        }

        // Convert ID to binary. Uuid is 16 Bytes.
        if ($id instanceof AbstractUid) {
            $normalizedValue = $id->toBinary();
        } elseif (is_int($id)) {
            // If the ID is numeric, we pack first 8 bytes of 0, then the ID
            $normalizedValue = pack("P", 0) . pack("P", $id);
        } else {
            $type = get_debug_type($id);
            throw new InvalidArgumentException("The ID of the entity must either be an uid or an number, {$type} given.");
        }

        return $normalizedValue . $className;
    }

    public function normalizeDateDatum(DateTime $value): int
    {
        return $value->getTimestamp();
    }

    public function encode(mixed $value): string
    {
        $encodedValue = match($this->type) {
            DatumEnum::Int, DatumEnum::Int64 => pack("J", $value),
            DatumEnum::Int32, DatumEnum::UInt32 => pack("N", $value),
            DatumEnum::Int16, DatumEnum::UInt16 => pack("n", $value),
            DatumEnum::Int8 => pack("c", $value),
            DatumEnum::UInt8 => pack("C", $value),
            DatumEnum::Float32 => pack("G", $value),
            DatumEnum::Float64 => pack("E", $value),
            DatumEnum::ErrorFloat => $this->packErrorFloat($value),
            DatumEnum::Uuid => $this->normalizeUuidDatum($value),
            DatumEnum::EntityReference => $this->normalizeEntityDatum($value),
            DatumEnum::String => (string)$value,
            DatumEnum::Date => pack("J", $this->normalizeDateDatum($value)),
            DatumEnum::Image => empty($value) ? "" : (string)$value,
        };

        return $encodedValue;
    }

    public function packErrorFloat(mixed $value): string
    {
        if (is_float($value)) {
            $value = [$value, 0, 0, 0];
        } elseif (count($value) === 2) {
            $value = [...$value, 0, 0];
        } elseif (!(count($value) === 4)) {
            throw new InvalidArgumentException("An ErrorFloat must either be a single value, two values, or four values");
        }

        return pack("EEEE", ... $value);
    }

    /**
     * @param resource|string $stream
     * @return DatumReturnType
     */
    public function decode($stream): mixed
    {
        if (is_resource($stream)) {
            $value = stream_get_contents($stream, -1, 0);

            if ($value === false) {
                throw new InvalidArgumentException("Reading the stream failed.");
            }
        } else {
            $value = $stream;
        }

        $decodedValue = match($this->type) {
            DatumEnum::Int, DatumEnum::Int64 => unpack("J", $value)[1],
            DatumEnum::Int32, DatumEnum::UInt32 => unpack("N", $value)[1],
            DatumEnum::Int16, DatumEnum::UInt16 => unpack("n", $value)[1],
            DatumEnum::Int8 => unpack("c", $value)[1],
            DatumEnum::UInt8 => unpack("C", $value)[1],
            DatumEnum::Float32 => unpack("G", $value)[1],
            DatumEnum::Float64 => unpack("E", $value)[1],
            DatumEnum::ErrorFloat => $this->unpackErrorFloat($value),
            DatumEnum::String => $value,
            DatumEnum::Uuid => Ulid::fromBinary($value),
            DatumEnum::EntityReference => $this->denormalizeEntityDatum($value),
            DatumEnum::Date => $this->denormalizeDateDatum($value),
            DatumEnum::Image => $value,
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

    /**
     * @param string $value
     * @return array{float, float, float, float}
     */
    public function unpackErrorFloat(string $value): array
    {
        return unpack("E*", $value);
    }

    public function denormalizeDateDatum(string $value): DateTime
    {
        $timestamp = unpack("J", $value)[1];
        $datetime = new DateTime();
        return $datetime->setTimestamp($timestamp);
    }

    /**
     * @param string $value
     * @return array{0: int|Ulid, 1: class-string}
     */
    public function denormalizeEntityDatum(string $value): array
    {
        if (str_starts_with($value, "\0\0\0\0\0\0\0\0")) {
            $id = unpack("P2", $value)[2];
        } else {
            $id = Ulid::fromBinary(substr($value, 0, 16));
        }

        $className = substr($value, 16);
        return [$id, $className];
    }
}