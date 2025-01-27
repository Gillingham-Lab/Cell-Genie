<?php
declare(strict_types=1);

namespace App\Twig\Components\Form;

use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class TabbedFormComponent
{
    public FormView $form;
    /** @var array<string, mixed> */
    public array $formAttributes = [];
    public string $submitButtonLabel = "Submit";
    public string $saveButtonLabel = "Save";
    public bool $useAction = false;
}