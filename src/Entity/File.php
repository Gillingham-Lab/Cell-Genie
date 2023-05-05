<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\DoctrineEntity\User\User;
use App\Repository\FileRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "ulid", unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $contentType = "";

    #[ORM\Column(type: "integer", nullable: false, options: ["default" => 0])]
    private ?int $orderValue = 0;

    #[ORM\Column(type: "string", length: 255, options: ["default" => ""])]
    private string $originalFileName = "";

    #[ORM\Column(type: "integer")]
    private int $contentSize = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $uploadedBy = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\OneToOne(inversedBy: "fileData", targetEntity: FileBlob::class, cascade: ["persist", "remove"], fetch: "EXTRA_LAZY")]
    #[ORM\JoinColumn(nullable: false)]
    private ?FileBlob $fileBlob = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Assert\GreaterThanOrEqual("1970-01-01 00:00:00")]
    private ?DateTime $uploadedOn = null;

    private bool $freshlyUploaded = false;

    public function setFromFile(UploadedFile $uploadedFile)
    {
        try {
            $this->setContentType($uploadedFile->getMimeType());
        } catch (\LogicException) {
            $this->setContentType($uploadedFile->getClientMimeType());
        }

        $this->setContentSize($uploadedFile->getSize());
        $this->setOriginalFileName($uploadedFile->getClientOriginalName());

        if (!$this->fileBlob) {
            $this->fileBlob = new FileBlob();
            $this->fileBlob->setFileData($this);
        }

        $this->fileBlob->setContent($uploadedFile->getContent());
        $this->setUploadedOn(new DateTime("now"));
        $this->freshlyUploaded = true;
    }

    public function __toString(): string
    {
        $size = round($this->contentSize/(1024*1024), 2);
        if ($size > 0) {
            $size = ", {$size} MiB";
        } else {
            $size = "";
        }

        $filename = strlen($this->originalFileName) > 0 ? $this->originalFileName : "unknown";

        return "{$this->title} ({$filename}{$size})";
    }

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

    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(string $originalFileName): self
    {
        $this->originalFileName = $originalFileName;

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

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
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

    public function getOrderValue(): ?int
    {
        return $this->orderValue;
    }

    public function setOrderValue(?int $orderValue): self
    {
        $this->orderValue = $orderValue;
        return $this;
    }

    public function isFreshlyUploaded(): bool
    {
        return $this->freshlyUploaded;
    }
}
