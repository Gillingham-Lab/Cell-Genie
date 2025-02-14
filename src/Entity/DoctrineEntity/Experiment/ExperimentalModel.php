<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\Experiment\ExperimentalModelRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ExperimentalModelRepository::class)]
class ExperimentalModel
{
    use IdTrait;

    #[ORM\Column(type: 'string', nullable: false)]
    public ?string $name = null;

    #[ORM\Column(type: "string", nullable: false)]
    public ?string $model = null;

    #[ORM\Column(type: 'string', nullable: true)]
    public ?string $referenceModel = null;

    #[ORM\ManyToOne(targetEntity: self::class, fetch: "LAZY")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    public ?ExperimentalModel $parent = null;

    /** @var null|array<string, mixed>  */
    #[ORM\Column(type: "json", nullable: true)]
    public ?array $configuration = [];

    /** @var null|array<string, mixed>  */
    #[ORM\Column(type: "json", nullable: true)]
    public ?array $result = [];

    public function __construct()
    {

    }

    public function __toString(): string
    {
        return $this->name ?? $this->model ?? "UnnamedModel";
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    /**
     * @param null|array<string, mixed> $configuration
     */
    public function setConfiguration(?array $configuration): static
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return null|array<string, mixed>
     */
    public function getResult(): ?array
    {
        return $this->result;
    }

    /**
     * @param null|array<string, mixed> $result
     */
    public function setResult(?array $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getParent(): ?ExperimentalModel
    {
        return $this->parent;
    }

    public function setParent(?ExperimentalModel $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    public function getReferenceModel(): ?string
    {
        return $this->referenceModel;
    }

    public function setReferenceModel(?string $referenceModel): static
    {
        $this->referenceModel = $referenceModel;
        return $this;
    }
}