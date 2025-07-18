<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Form;

use Symfony\Component\Form\FormView;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
class EnumeratedWidget
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $enumerationType;

    #[ExposeInTemplate("form")]
    #[LiveProp(useSerializerForHydration: true)]
    public ?FormView $formView;

    #[LiveAction]
    public function generate(): void {}
}
