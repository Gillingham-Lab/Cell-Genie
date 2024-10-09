<?php
declare(strict_types=1);

namespace App\Tests\TestTraits;

use Symfony\Component\DomCrawler\Form;

trait NestedFormAssertions
{
    private function linearizeNestedArray(string $formName, array $expected): array
    {
        $linearizedArray = [];

        foreach ($expected as $key => $value) {
            if (is_array($value)) {
                $prefix = $formName . '[' . $key . ']';
                $linearizedArray = array_merge($linearizedArray, $this->linearizeNestedArray($prefix, $value));
            } else {
                $linearizedArray[$formName . '[' . $key . ']'] = $value;
            }
        }

        return $linearizedArray;
    }

    public function assertNestedFormValues(Form $form, string $formName, array $expected): void
    {
        // Linearize array
        $expectedValues = $this->linearizeNestedArray($formName, $expected);
        $currentValues = $form->getValues();

        foreach ($expectedValues as $key => $value) {
            $this->assertArrayHasKey($key, $currentValues, message: "Key {$key} does not exists in the given form. Only keys available are:\n" . implode("\n\t- ", array_keys($currentValues))."\n\nIf the value is null, it might not be retrieved as an value.");
            $this->assertSame($value, $currentValues[$key]);
        }
    }
}