<?php

namespace App\Entity;

use App\Repository\LotRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LotRepository::class)
 */
class Lot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="ulid", unique=True)
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     */
    private ?Ulid $id = null;

    /**
     * @ORM\Column(type="string", length=20)
     */
    #[Assert\NotBlank]
    private ?string $number = null;

    /**
     * @ORM\Column(type="string", length=50)
     */
    #[Assert\NotBlank]
    private ?string $lotNumber = null;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Assert\NotBlank]
    private ?DateTimeInterface $boughtOn = null;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Assert\NotBlank]
    private ?DateTimeInterface $openedOn = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    #[Assert\NotBlank]
    private ?User $boughtBy = null;

    /**
     * @ORM\Column(type="string", length=10)
     */
    #[Assert\NotBlank]
    private ?string $amount = null;

    /**
     * @ORM\Column(type="string", length=15)
     */
    #[Assert\NotBlank]
    private ?string $purity = null;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private ?string $aliquoteSize = null;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    #[Assert\NotBlank]
    private ?int $numberOfAliquotes = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $comment = null;

    public function __toString()
    {
        return $this->getNumber() ?? "???";
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getLotNumber(): ?string
    {
        return $this->lotNumber;
    }

    public function setLotNumber(string $lotNumber): self
    {
        $this->lotNumber = $lotNumber;

        return $this;
    }

    public function getBoughtOn(): ?DateTimeInterface
    {
        return $this->boughtOn;
    }

    public function setBoughtOn(DateTimeInterface $boughtOn): self
    {
        $this->boughtOn = $boughtOn;

        return $this;
    }

    public function getOpenedOn(): ?DateTimeInterface
    {
        return $this->openedOn;
    }

    public function setOpenedOn(DateTimeInterface $openedOn): self
    {
        $this->openedOn = $openedOn;

        return $this;
    }

    public function getBoughtBy(): ?User
    {
        return $this->boughtBy;
    }

    public function setBoughtBy(?User $boughtBy): self
    {
        $this->boughtBy = $boughtBy;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPurity(): ?string
    {
        return $this->purity;
    }

    public function setPurity(string $purity): self
    {
        $this->purity = $purity;

        return $this;
    }

    public function getAliquoteSize(): ?string
    {
        return $this->aliquoteSize;
    }

    public function setAliquoteSize(?string $aliquoteSize): self
    {
        $this->aliquoteSize = $aliquoteSize;

        return $this;
    }

    public function getNumberOfAliquotes(): ?int
    {
        return $this->numberOfAliquotes;
    }

    public function setNumberOfAliquotes(?int $numberOfAliquotes): self
    {
        $this->numberOfAliquotes = $numberOfAliquotes;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
