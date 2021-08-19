<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="ulid", unique=True)
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     */
    private ?Ulid $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $contentType;

    /**
     * @ORM\Column(type="blob")
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private int $contentSize;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $uploadedBy = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private string $title = "";

    /**
     * @ORM\Column(type="text")
     */
    private string $description = "";

    /**
     * @ORM\OneToOne(targetEntity=FileBlob::class, inversedBy="fileData", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private FileBlob $fileBlob;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $uploadedOn = null;

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
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

    public function getContentSize(): ?int
    {
        return $this->contentSize;
    }

    public function setContentSize(int $contentSize): self
    {
        $this->contentSize = $contentSize;

        return $this;
    }

    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?User $uploadedBy): self
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFileBlob(): ?FileBlob
    {
        return $this->fileBlob;
    }

    public function setFileBlob(FileBlob $fileBlob): self
    {
        $this->fileBlob = $fileBlob;

        return $this;
    }

    public function getUploadedOn(): ?\DateTimeInterface
    {
        return $this->uploadedOn;
    }

    public function setUploadedOn(?\DateTimeInterface $uploadedOn): self
    {
        $this->uploadedOn = $uploadedOn;

        return $this;
    }
}
