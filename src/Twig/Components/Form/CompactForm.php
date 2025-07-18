<?php
declare(strict_types=1);

namespace App\Twig\Components\Form;

use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class CompactForm
{
    public FormView $form;
    /** @var array<string, mixed> */
    public array $formAttributes = [];
}
