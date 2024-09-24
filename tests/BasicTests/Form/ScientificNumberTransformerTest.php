<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Form;

use App\Form\ScientificNumberTransformer;
use PHPUnit\Framework\TestCase;

class ScientificNumberTransformerTest extends TestCase
{
    /**
     * @return array<string, string|array<string>>
     */
    private function getTransformerDefaults(): array
    {
        return [
            "nan_values" => ["NaN", "NA", "<NA>"],
            "inf_values" => ["Inf", "Inf"],
            "ninf_values" => ["-Inf"],
            "nan_value" => "NaN",
            "inf_value" => "Inf",
            "ninf_value" => "-Inf",
        ];
    }
    public function testNormToViewTransformationWorksForNaNs(): void
    {
        $arguments = $this->getTransformerDefaults();

        $values = ["NaN", "NA"];

        foreach ($values as $string) {
            $arguments["nan_value"] = $string;
            $transformer = new ScientificNumberTransformer(... $arguments);

            $this->assertSame($string, $transformer->transform(NAN));
        }
    }

    public function testNormToViewTransformationWorksForPositiveInf(): void
    {
        $arguments = $this->getTransformerDefaults();

        $values = ["+Inf", "Inf", "n.d."];

        foreach ($values as $string) {
            $arguments["inf_value"] = $string;
            $transformer = new ScientificNumberTransformer(... $arguments);

            $this->assertSame($string, $transformer->transform(INF));
        }
    }

    public function testNormToViewTransformationWorksForNegativeInf(): void
    {
        $arguments = $this->getTransformerDefaults();

        $values = ["-Inf", "pInf"];

        foreach ($values as $string) {
            $arguments["ninf_value"] = $string;
            $transformer = new ScientificNumberTransformer(... $arguments);

            $this->assertSame($string, $transformer->transform(-INF));
        }
    }

    public function testNormToViewTransformationWorksForNormalFloats(): void
    {
        $arguments = $this->getTransformerDefaults();
        $transformer = new ScientificNumberTransformer(... $arguments);

        $tests = [
            [2.5, "2.5"],
            [1.0, "1"],
            [0.5, "0.5"],
        ];

        foreach ($tests as [$float, $string]) {
            $this->assertSame($string, $transformer->transform($float));
        }
    }

    public function testViewToNormTransformationWorksForNaNs(): void
    {
        $arguments = $this->getTransformerDefaults();

        $values = ["NaN", "NA"];

        foreach ($values as $string) {
            $arguments["nan_values"] = [$string];
            $transformer = new ScientificNumberTransformer(... $arguments);

            $this->assertNan($transformer->reverseTransform($string));
            $this->assertNan($transformer->reverseTransform(strtolower($string)));
            $this->assertNan($transformer->reverseTransform(strtoupper($string)));
        }
    }

    public function testViewToNormTransformationWorksForPositiveInfinity(): void
    {
        $arguments = $this->getTransformerDefaults();

        $values = ["Inf", "+Inf"];

        foreach ($values as $string) {
            $arguments["inf_values"] = [$string];
            $transformer = new ScientificNumberTransformer(... $arguments);

            $this->assertInfinite($transformer->reverseTransform($string));
            $this->assertGreaterThan(0, $transformer->reverseTransform($string));
            $this->assertInfinite($transformer->reverseTransform(strtolower($string)));
            $this->assertGreaterThan(0, $transformer->reverseTransform(strtolower($string)));
            $this->assertInfinite($transformer->reverseTransform(strtoupper($string)));
            $this->assertGreaterThan(0, $transformer->reverseTransform(strtoupper($string)));
        }
    }

    public function testViewToNormTransformationWorksForNegativeInfinity(): void
    {
        $arguments = $this->getTransformerDefaults();

        $values = ["-Inf"];

        foreach ($values as $string) {
            $arguments["ninf_values"] = [$string];
            $transformer = new ScientificNumberTransformer(... $arguments);

            $this->assertInfinite($transformer->reverseTransform($string));
            $this->assertLessThan(0, $transformer->reverseTransform($string));
            $this->assertInfinite($transformer->reverseTransform(strtolower($string)));
            $this->assertLessThan(0, $transformer->reverseTransform(strtolower($string)));
            $this->assertInfinite($transformer->reverseTransform(strtoupper($string)));
            $this->assertLessThan(0, $transformer->reverseTransform(strtoupper($string)));
        }
    }

    public function testViewToNormTransformationReturnsNanForUnknownParameters(): void
    {
        $arguments = $this->getTransformerDefaults();
        $transformer = new ScientificNumberTransformer(... $arguments);

        $this->assertNan($transformer->reverseTransform("n.d."));
    }

    public function testViewToNormTransformationWorksForNormalFloats(): void
    {
        $arguments = $this->getTransformerDefaults();
        $transformer = new ScientificNumberTransformer(... $arguments);

        $tests = [
            [2.5, "2.5"],
            [1.0, "1"],
            [0.5, "0.5"],
        ];

        foreach ($tests as [$float, $string]) {
            $this->assertSame($float, $transformer->reverseTransform($string));
        }
    }
}
