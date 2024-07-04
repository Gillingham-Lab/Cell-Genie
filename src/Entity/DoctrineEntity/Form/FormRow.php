<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Form;

use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Enums\FormRowTypeEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity()]
#[Gedmo\Loggable()]
final class FormRow
{
    use IdTrait;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank()]
    public ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $help = null;

    #[Column(enumType: FormRowTypeEnum::class)]
    #[Assert\NotBlank()]
    public ?FormRowTypeEnum $type = null;

    #[Column(type: Types::JSON)]
    public array $configuration = [];

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getFieldName(): ?string
    {
        $label = preg_replace("/[^[:alnum:]]/u", "", $this->getLabel());
        $match = preg_match("/^[0-9]/");

        if ($match === 0) {
            $label = "_" . $label;
        }

        return $label;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): self
    {
        $this->help = $help;
        return $this;
    }

    public function getType(): ?FormRowTypeEnum
    {
        return $this->type;
    }

    public function setType(?FormRowTypeEnum $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }
}