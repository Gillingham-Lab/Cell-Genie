<?php
declare(strict_types=1);

namespace App\Entity\Embeddable;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Embeddable]
class Price
{
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Gedmo\Versioned]
    private ?int $priceValue = null;

    #[ORM\Column(type: Types::STRING, length: 3, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $priceCurrency = null;

    public function getPriceValue(): ?int
    {
        return $this->priceValue;
    }

    public function setPriceValue(?int $priceValue): self
    {
        $this->priceValue = $priceValue;
        return $this;
    }

    public function getPriceCurrency(): ?string
    {
        return $this->priceCurrency;
    }

    public function setPriceCurrency(?string $priceCurrency): self
    {
        $this->priceCurrency = $priceCurrency;
        return $this;
    }


}