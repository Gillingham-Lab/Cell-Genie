<?php
declare(strict_types=1);

namespace App\Twig\Components\Instrument;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Genie\Enums\InstrumentRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class InstrumentCard
{
    public Instrument $instrument;
    public InstrumentRole $userRole;

    public function __construct(
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private Security $security,
    ) {
    }

    /**
     * @param array<string, mixed> $props
     * @return array<string, mixed>
     */
    #[PreMount]
    public function preMount(array $props): array
    {
        if (isset($props["userRole"])) {
            $props["userRole"] = InstrumentRole::from($props["userRole"]);
        }

        return $props;
    }

    public function getCardColor(): string
    {
        return match($this->userRole) {
            InstrumentRole::Untrained => "text-secondary bg-secondary",
            InstrumentRole::Admin => "border-primary",
            InstrumentRole::Trained => "border-success",
            default => "",
        };
    }

    public function isEnabled(): bool
    {
        return match($this->userRole) {
            InstrumentRole::Untrained => false,
            default => true,
        };
    }

    public function getToolbox(): Toolbox
    {
        return new Toolbox([
            new ViewTool(
                path: $this->urlGenerator->generate("app_instruments_view", ["instrument" => $this->instrument->getId()]),
                enabled: $this->security->isGranted("view", $this->instrument),
                tooltip: "View instrument",
            ),
            new EditTool(
                path: $this->urlGenerator->generate("app_instruments_edit", ["instrument" => $this->instrument->getId()]),
                enabled: $this->security->isGranted("edit", $this->instrument),
                tooltip: "Edit instrument",
            ),
            new ClipwareTool(
                clipboardText: $this->instrument->getCitationText() ?? "",
                enabled: !!$this->instrument->getCitationText(),
                tooltip: "Copy citation",
            )
        ]);
    }
}