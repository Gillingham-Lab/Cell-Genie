<?php
declare(strict_types=1);

namespace App\Doctrine\Migration;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

trait AddIdColumnTrait
{
    public function addIdColumn(
        Table $table,
        string $columnName = "id",
        bool $noPrimaryKey = false,
    ): void {
        $table->addColumn($columnName, Types::GUID)
            ->setNotnull(true)
            ->setComment("(DC2Type:ulid)")
        ;

        if (!$noPrimaryKey) {
            $table->setPrimaryKey([$columnName]);
        }
    }
}