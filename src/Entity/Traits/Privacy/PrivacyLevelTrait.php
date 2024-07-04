<?php
declare(strict_types=1);

namespace App\Entity\Traits\Privacy;

use App\Genie\Enums\PrivacyLevel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait PrivacyLevelTrait
{
    #[ORM\Column(type: "smallint", nullable: false, enumType: PrivacyLevel::class, options: ["default" => PrivacyLevel::Public])]
    #[Assert\NotBlank]
    private PrivacyLevel $privacyLevel = PrivacyLevel::Public;

    public function getPrivacyLevel(): PrivacyLevel
    {
        return $this->privacyLevel;
    }

    public function setPrivacyLevel(PrivacyLevel $privacyLevel): self
    {
        $this->privacyLevel = $privacyLevel;

        return $this;
    }
}
