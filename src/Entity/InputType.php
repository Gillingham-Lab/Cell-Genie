<?php
declare(strict_types=1);

namespace App\Entity;

use App\Service\Doctrine\Type\Ulid;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
abstract class InputType
{
    const CHECK_TYPE = "check";
    const INTEGER_TYPE = "integer";
    const FLOAT_TYPE = "float";
    const CHOICE_TYPE = "choice";
    const FREE_TYPE = "free";
    const CHEMICAL_TYPE = "chemical";
    const PROTEIN_TYPE = "protein";
    const SUBSTANCE_TYPE = "substance";
    const LOT_TYPE = "lot";

    const TYPES = [
        self::FREE_TYPE,
        self::CHECK_TYPE,
        self::INTEGER_TYPE,
        self::FLOAT_TYPE,
        self::CHOICE_TYPE,
        self::CHEMICAL_TYPE,
        self::PROTEIN_TYPE,
        self::SUBSTANCE_TYPE,
        self::LOT_TYPE,
    ];

    const LABEL_TYPES = [
        "Free" => self::FREE_TYPE,
        "Check" => self::CHECK_TYPE,
        "Integer" => self::INTEGER_TYPE,
        "Float" => self::FLOAT_TYPE,
        "Choice" => self::CHOICE_TYPE,
        "Chemical" => self::CHEMICAL_TYPE,
        "Protein" => self::PROTEIN_TYPE,
        "Substance" => self::SUBSTANCE_TYPE,
        "Lot" => self::LOT_TYPE,
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
            $labels = implode(", ", self::TYPES);
            throw new InvalidArgumentException("ExperimentalCondition::type must be one of {$labels}; but '{$type}' was given.");
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

    abstract public function getTitle(): string;
    abstract public function getDescription(): ?string;
    abstract public function getId(): ?Ulid;
}