<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

abstract class InputType
{
    const CHECK_TYPE = "check";
    const INTEGER_TYPE = "integer";
    const FLOAT_TYPE = "float";
    const CHOICE_TYPE = "choice";
    const FREE_TYPE = "free";

    const TYPES = [
        self::CHECK_TYPE,
        self::INTEGER_TYPE,
        self::FLOAT_TYPE,
        self::CHOICE_TYPE,
        self::FREE_TYPE
    ];

    #[ORM\Column(type: "string", length: 30, nullable: false)]
    private ?string $type = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $config = null;


    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        if (array_search($type, self::TYPES, strict: true) !== false) {
            throw new InvalidArgumentException("ExperimentalCondition::type must be one of check, integer, float, choice, free");
        }

        $this->type = $type;
        return $this;
    }

    public function getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }
}