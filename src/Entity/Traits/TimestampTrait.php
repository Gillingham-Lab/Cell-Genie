<?php
declare(strict_types=1);

namespace App\Entity\Traits;

trait TimestampTrait
{
    use CreatedAtTrait;
    use ModifiedAtTrait;

    public function updateTimestamps(): void
    {
        $this->updateCreatedAt();
        $this->updateModifiedAt();
    }
}
