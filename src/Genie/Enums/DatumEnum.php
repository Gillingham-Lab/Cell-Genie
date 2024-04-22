<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum DatumEnum: string
{
    case String = "string";
    case Int = "int";
    case Int8 = "int8";
    case Int16 = "int16";
    case Int32 = "int32";
    case Int64 = "int64";
    case UInt8 = "uint8";
    case UInt16 = "uint16";
    case UInt32 = "uint32";
    case Float32 = "float32";
    case Float64 = "float64";
    case Uuid = "uuid";
    case EntityReference = "entityReference";
}