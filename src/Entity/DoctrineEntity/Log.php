<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\Traits\Fields\DescriptionTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\TitleTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Entity\Traits\TimestampTrait;
use App\Genie\Enums\LogType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Loggable;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[Loggable]
class Log
{
    use IdTrait;
    use TitleTrait;
    use DescriptionTrait;
    use PrivacyAwareTrait;
    use TimestampTrait;

    #[ORM\Column(enumType: LogType::class)]
    private ?LogType $logType = null;

    public function getLogType(): ?LogType
    {
        return $this->logType;
    }

    public function setLogType(?LogType $logType): static
    {
        $this->logType = $logType;
        return $this;
    }
}