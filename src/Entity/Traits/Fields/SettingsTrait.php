<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use App\Entity\Param\ParamBag;
use Doctrine\ORM\Mapping as ORM;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;

trait SettingsTrait
{
    #[ORM\Column(type: JsonDocumentType::NAME, nullable: true)]
    private ?ParamBag $settings;

    public function getSettings(): ParamBag
    {
        if ($this->settings === null) {
            $this->settings = new ParamBag();
        }

        return $this->settings;
    }

    public function setSettings(?ParamBag $settings): static
    {
        if ($settings === null) {
            $this->settings = new ParamBag();
        } else {
            $this->settings = $settings;
        }

        return $this;
    }
}