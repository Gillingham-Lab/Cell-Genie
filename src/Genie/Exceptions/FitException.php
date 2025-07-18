<?php
declare(strict_types=1);

namespace App\Genie\Exceptions;

/**
 * Gets thrown if an exception occurs during fitting a model.
 *
 * If the fit still properly returns data (for example in case a warning gets issued), the exception will contain that
 * data in self::getContent().
 */
class FitException extends GinException
{
    /**
     * @param string[] $warnings
     * @param string[] $errors
     * @param string|null $content
     */
    public function __construct(
        private readonly array $warnings = [],
        private readonly array $errors = [],
        private readonly ?string $content = null,
    ) {
        parent::__construct("An error occured during a fit.", 0);
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
