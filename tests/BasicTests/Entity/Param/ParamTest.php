<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Param;

use App\Entity\Param\Param;
use App\Entity\Param\ParamTypeEnum;
use PHPUnit\Framework\TestCase;
use TypeError;

class ParamTest extends TestCase
{
    public function testCreatingParamsWithNoTypes()
    {
        $boolParam = new Param(true);
        $this->assertTrue($boolParam->getValue());

        $stringParam = new Param("string");
        $this->assertSame("string", $stringParam->getValue());

        $intParam = new Param(1);
        $this->assertSame(1, $intParam->getValue());

        $floatParam = new Param(1.1);
        $this->assertSame(1.1, $floatParam->getValue());
    }

    public function testCreatingParamWithTypesMatching()
    {
        $boolParam = new Param(true, ParamTypeEnum::Bool);
        $this->assertTrue($boolParam->getValue());

        $stringParam = new Param("string", ParamTypeEnum::String);
        $this->assertSame("string", $stringParam->getValue());

        $intParam = new Param(1, ParamTypeEnum::Int);
        $this->assertSame(1, $intParam->getValue());

        $floatParam = new Param(1.1, ParamTypeEnum::Float);
        $this->assertSame(1.1, $floatParam->getValue());
    }

    /** list<array{mixed, ParamTypeEnum, string}> */
    public function valuesWithParamTypesNotMatching(): array
    {
        return [
            [true, ParamTypeEnum::String, "Value type must match ParamType. bool was given, but string was required."],
            [true, ParamTypeEnum::Int, "Value type must match ParamType. bool was given, but int was required."],
            [true, ParamTypeEnum::Float, "Value type must match ParamType. bool was given, but float was required."],
            ['st', ParamTypeEnum::Bool, "Value type must match ParamType. string was given, but bool was required."],
            ['st', ParamTypeEnum::Int, "Value type must match ParamType. string was given, but int was required."],
            ['st', ParamTypeEnum::Float, "Value type must match ParamType. string was given, but float was required."],
            [1000, ParamTypeEnum::Bool, "Value type must match ParamType. int was given, but bool was required."],
            [1000, ParamTypeEnum::String, "Value type must match ParamType. int was given, but string was required."],
            [1000, ParamTypeEnum::Float, "Value type must match ParamType. int was given, but float was required."],
            [1.10, ParamTypeEnum::Bool, "Value type must match ParamType. float was given, but bool was required."],
            [1.10, ParamTypeEnum::String, "Value type must match ParamType. float was given, but string was required."],
            [1.10, ParamTypeEnum::Int, "Value type must match ParamType. float was given, but int was required."],
        ];
    }

    /**
     * @dataProvider valuesWithParamTypesNotMatching
     */
    public function testCreatingParamWithTypesNotMatching(mixed $value, ParamTypeEnum $type, string $message): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage($message);
        new Param($value, $type);
    }

    public function testCallingSetValueOnExistingParam(): void
    {
        $param = new Param(1);
        $param->setValue(2);
        $this->assertSame(2, $param->getValue());
    }

    public function testSettingValueOnExistingParamWithDifferentTypeFails(): void
    {
        $param = new Param(1);
        $this->expectException(TypeError::class);
        $param->setValue("string");
    }

    public function testTypeConversionOnGetValueAs(): void
    {
        $param = new Param(1);
        $this->assertTrue($param->asBool());
        $this->assertSame(1, $param->asInt());
        $this->assertSame(1.0, $param->asFloat());
    }
}
