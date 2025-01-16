<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\NameTrait;
use App\Entity\Traits\Fields\NumberTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Experiment\ExperimentalDesignRepository;
use App\Validator\Constraint\UniqueCollectionField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentalDesignRepository::class)]
#[ORM\Table("new_experimental_design")]
#[Gedmo\Loggable()]
class ExperimentalDesign implements PrivacyAwareInterface
{
    use IdTrait;
    use NameTrait;
    use NumberTrait;
    use PrivacyAwareTrait;

    /**
     * @var Collection<int, ExperimentalDesignField>
     */
    #[ORM\OneToMany(mappedBy: "design", targetEntity: ExperimentalDesignField::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\OrderBy(["role" => "ASC", "weight" => "ASC"])]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: "A Experimental design must have at least 1 field")]
    #[UniqueCollectionField("formRow.label")]
    #[UniqueCollectionField("formRow.fieldName", errorPath: "formRow.label", localMessage: "This field converts internally to {{value}}, which is not unique. All non-alphanumeric characters are removed. Eg, 'Date' and 'Date_' will both get converted to '_Date'.")]
    private Collection $fields;

    /**
     * @var Collection<int, ExperimentalRun>
     */
    #[ORM\OneToMany(mappedBy: "design", targetEntity: ExperimentalRun::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $runs;

    #[ORM\Column(type: "string", length: 10, nullable: false, options: ["default" => "???"])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 10,
    )]
    private ?string $number = null;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->runs = new ArrayCollection();
    }

    /** @return Collection<int, ExperimentalDesignField>  */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(ExperimentalDesignField $field): self
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setDesign($this);
        }

        return $this;
    }

    public function removeField(ExperimentalDesignField $field): self
    {
        if ($this->fields->contains($field)) {
            $this->fields->removeElement($field);
            $field->setDesign(null);
        }

        return $this;
    }

    /** @return Collection<int, ExperimentalRun> */
    public function getRuns(): Collection
    {
        return $this->runs;
    }

    public function addRun(ExperimentalRun $run): self
    {
        if (!$this->runs->contains($run)) {
            $this->runs->add($run);
            $run->setDesign($this);
        }

        return $this;
    }

    public function removeRun(ExperimentalRun $run): self
    {
        if ($this->runs->contains($run)) {
            $this->runs->removeElement($run);
            $run->setDesign(null);
        }

        return $this;
    }
}
