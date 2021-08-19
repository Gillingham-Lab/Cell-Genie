<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\FileBlobRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;

/**
 * @ORM\Entity(repositoryClass=FileBlobRepository::class)
 */
class FileBlob
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="ulid", unique=True)
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     */
    private ?Ulid $id;

    /**
     * @ORM\Column(type="blob")
     */
    private mixed $content;

    /**
     * @ORM\OneToOne(targetEntity=File::class, mappedBy="fileBlob", cascade={"persist", "remove"})
     */
    private File $fileData;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getFileData(): ?File
    {
        return $this->fileData;
    }

    public function setFileData(File $fileData): self
    {
        // set the owning side of the relation if necessary
        if ($fileData->getFileBlob() !== $this) {
            $fileData->setFileBlob($this);
        }

        $this->fileData = $fileData;

        return $this;
    }
}
