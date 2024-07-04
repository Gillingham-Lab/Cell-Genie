<?php
declare(strict_types=1);

namespace App\Twig\Components\Trait;

use App\Service\Doctrine\Type\Ulid;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

trait GeneratedIdTrait
{
    #[ExposeInTemplate("id")]
    public function getId()
    {
        return (new Ulid())->toRfc4122();
    }
}