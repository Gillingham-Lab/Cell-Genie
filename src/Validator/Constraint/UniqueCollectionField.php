<?php
declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Validator\UniqueCollectionFieldValidator;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class UniqueCollectionField extends Constraint
{
    public string $message = 'The field {field} must be unique.';
    public string $localMessage = 'This field must be unique, but appears here a second time.';

    public function __construct(
        public readonly string $field,
        public readonly ?string $errorPath = null,
        ?string $message = null,
        ?string $localMessage = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);

        $this->message = $message ?? $this->message;
        $this->localMessage = $localMessage ?? $this->localMessage;

        $this->message = str_replace("{field}", $this->field, $this->message);
    }

    public function validatedBy(): string
    {
        return UniqueCollectionFieldValidator::class;
    }
}
