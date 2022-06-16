<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Repository\VocabularyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VocabularyRepository::class)]
#[ORM\UniqueConstraint(fields: ["name"])]
#[UniqueEntity(["name"])]
class Vocabulary
{
    use IdTrait;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = "";

    #[ORM\Column(type: 'array', options: ["default" => "a:0:{}"])]
    private ?array $vocabulary = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVocabulary(): array
    {
        return $this->vocabulary;
    }

    public function setVocabulary(array $vocabulary): self
    {
        $this->vocabulary = $vocabulary;

        return $this;
    }
}
