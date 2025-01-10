<?php
declare(strict_types=1);

namespace App\Genie\Codec;

use App\Genie\Enums\DatumEnum;
use App\Service\Doctrine\Type\Ulid;
use Doctrine\Common\Util\ClassUtils;
use InvalidArgumentException;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

/**
 * Encodes values into binary data and decoded binary data into values.
 * Value type must be set by using DatumEnum.
 */
class ExperimentValueCodec
{
    public function __construct(
        private DatumEnum $type
    ) {
    }

    public function normalize(mixed $value, DatumEnum $type): mixed
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
                dump($value);
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
        } elseif ($this->type === DatumEnum::Date) {
            $value = $value->getTimestamp();
        }

        return $value;
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
            DatumEnum::Uuid, DatumEnum::EntityReference => $value,
            DatumEnum::String => (string)$value,
            DatumEnum::Date => pack("J", $value),
            DatumEnum::Image => empty($value) ? "" : (string)$value,
        };

        return $encodedValue;
    }

    public function decode($stream): mixed
    {
        if (is_resource($stream)) {
            $value = stream_get_contents($stream, -1, 0);
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
            DatumEnum::String => $value,
            DatumEnum::Uuid => Ulid::fromBinary($value),
            DatumEnum::EntityReference, DatumEnum::Date => $this->denormalize($value),
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

    public function denormalize($value)
    {
        if ($this->type === DatumEnum::Date) {
            $timestamp = unpack("J", $value)[1];
            $datetime = new DateTime();
            return $datetime->setTimestamp($timestamp);
        } elseif ($this->type === DatumEnum::EntityReference) {
            if (str_starts_with($value, "\0\0\0\0\0\0\0\0")) {
                $id = unpack("P2", $value)[2];
            } else {
                $id = Ulid::fromBinary(substr($value, 0, 16));
            }

            $className = substr($value, 16);
            return [$id, $className];
        }
    }
}