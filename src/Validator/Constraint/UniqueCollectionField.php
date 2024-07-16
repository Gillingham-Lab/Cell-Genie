<?php
declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Validator\UniqueCollectionFieldValidator;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UniqueCollectionField extends Constraint
{
    public string $message = 'The field {field} must be unique.';
    public string $localMessage = 'This field is duplicated.';

    public function __construct(
        public readonly string $field,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);

        $this->message = str_replace("{field}", $this->field, $this->message);
    }

    public function validatedBy(): string
    {
        return UniqueCollectionFieldValidator::class;
    }
}