<?php
declare(strict_types=1);

namespace App\Genie;

use App\Entity\ExperimentalRun;

class DataSet
{
    public function __construct(
        private ExperimentalRun $experimentalRun,
    ) {
    }

    public function parse(bool $normalize = false): string
    {

    }
}