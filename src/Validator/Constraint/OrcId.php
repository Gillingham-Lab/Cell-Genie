<?php
declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Validator\OrcIdValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OrcId extends Constraint
{
    public string $invalidOrcid = "The given value is not a valid orcid.";

    #[HasNamedArguments]
    public function __construct(
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return OrcIdValidator::class;
    }
}
