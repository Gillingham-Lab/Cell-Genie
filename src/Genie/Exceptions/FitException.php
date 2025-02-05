<?php
declare(strict_types=1);

namespace App\Genie\Exceptions;

class FitException extends GinException
{
    public function __construct(
        private readonly array $warnings = [],
        private readonly array $errors = [],
        private readonly ?string $content = null,

    ){
        parent::__construct("An error occured during a fit.", 0);
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}