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

    const LABEL_TYPES = [
        "Check" => self::CHECK_TYPE,
        "Integer" => self::INTEGER_TYPE,
        "Float" => self::FLOAT_TYPE,
        "Choice" => self::CHOICE_TYPE,
        "Free" => self::FREE_TYPE,
    ];

    #[ORM\Column(type: "string", length: 30, nullable: false)]
    protected ?string $type = null;

    #[ORM\Column(type: "text", nullable: false)]
    protected ?string $config = "";

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        if (array_search($type, self::TYPES, strict: true) === false) {
            throw new InvalidArgumentException("ExperimentalCondition::type must be one of check, integer, float, choice, free, but '{$type}' was given.");
        }

        $this->type = $type;
        return $this;
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(?string $config): self
    {
        if ($config === null) {
            $config = "";
        }

        $this->config = $config;

        return $this;
    }
}