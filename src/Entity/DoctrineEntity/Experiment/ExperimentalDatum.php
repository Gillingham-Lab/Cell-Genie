<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Codec\ExperimentValueCodec;
use App\Genie\Enums\DatumEnum;
use App\Repository\Experiment\ExperimentalDatumRepository;
use App\Service\Doctrine\Type\Ulid;
use App\Service\Doctrine\Type\UlidType;
use DateTime;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExperimentalDatumRepository::class)]
#[ORM\Table("new_experimental_datum")]
#[ORM\Index(fields: ["referenceUuid"])]
class ExperimentalDatum
{
    use IdTrait;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, enumType: DatumEnum::class)]
    private ?DatumEnum $type = null;

    /** @var resource|string|null */
    #[ORM\Column(type: Types::BINARY)]
    private $value = null;

    #[ORM\Column(
        type: UlidType::NAME,
        nullable: true,
        insertable: false,
        updatable: false,
        columnDefinition: "uuid GENERATED ALWAYS AS (CASE WHEN type = 'entityReference' OR type = 'uuid' THEN CAST(ENCODE(substring(value from 0 for 17), 'hex') AS uuid) ELSE null END) STORED",
        generated: "ALWAYS"
    )]
    private ?Ulid $referenceUuid = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?DatumEnum
    {
        return $this->type;
    }

    public function setType(DatumEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): mixed
    {
        $codec = new ExperimentValueCodec($this->type);
        return $codec->decode($this->value);
    }

    public function setValue(mixed $value): self
    {
        if ($value !== null) {
            if ($this->type === null) {
                throw new LogicException("The type of a value must be set before you set the value.");
            }

            $codec = new ExperimentValueCodec($this->type);
            $this->value = $codec->encode($value);
        }

        return $this;
    }

    public function asBase64(): ?string
    {
        if (is_null($this->value)) {
            return null;
        }

        if (is_string($this->value)) {
            return base64_encode($this->value);
        }

        return base64_encode(stream_get_contents($this->value, -1, 0));
    }

    public function getReferenceUuid(): ?Ulid
    {
        return $this->referenceUuid;
    }
}
