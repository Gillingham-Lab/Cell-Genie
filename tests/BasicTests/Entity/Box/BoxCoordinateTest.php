<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Box;

use App\Entity\BoxCoordinate;
use PHPUnit\Framework\TestCase;

class BoxCoordinateTest extends TestCase
{
    public function testSimpleCoordinates()
    {
        $testList = [
            "A1" => [1, 1],
            "A2" => [1, 2],
            "B1" => [2, 1],
            "G1" => [7, 1],
            "G8" => [7, 8],
            "AA23" => [27, 23],
            "AAA5789" => [703, 5789],
        ];

        $i = 0;
        foreach ($testList as $stringCoordinate => $result) {
            $i++;
            $coordinateObject = new BoxCoordinate($stringCoordinate);
            $coordinates = $coordinateObject->getIntCoordinates();

            $this->assertSame($result[0], $coordinates[0], "Test #{$i}: Row number mismatch");
            $this->assertSame($result[1], $coordinates[1], "Test #{$i}: Col number mismatch");
        }
    }
}