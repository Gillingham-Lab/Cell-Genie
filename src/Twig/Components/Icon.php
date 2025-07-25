<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use ValueError;

#[AsTwigComponent]
class Icon
{
    public string $icon;
    public ?string $stack = null;

    public function getIconClass(): string
    {
        if ($this->stack) {
            return "gin-icon gin-icon-stack gin-icon-stack-fw";
        }

        return "gin-icon " . match ($this->icon) {
            "success" => "far fa-check-circle",
            "danger" => "fas fa-exclamation-circle",
            "unknown" => "far fa-question-circle",
            "info" => "fas fa-info-circle",

            "antibody" => "icon icon-antibody",
            "antibody.primary" => "icon icon-antibody-primary-twocolor",
            "antibody.secondary" => "icon icon-antibody-secondary-twocolor",
            "epitope" => "icon icon-epitope-twocolor",

            "chemical", "compound" => "icon icon-chemical",
            "protein" => "icon icon-protein",
            "oligo" => "icon icon-oligo",
            "plasmid" => "icon icon-plasmid",
            "cell" => "icon icon-mammalian-cell",
            "cellCulture", "experiment" => "fas fa-fw fa-flask",

            "design" => "fa-fw fas fa-pen-fancy",

            "lot" => "icon icon-Lot",

            "view" => "far fa-fw fa-eye",
            "hidden" => "far fa-fw fa-eye-slash",
            "add", "increase" => "fas fa-fw fa-plus",
            "minus", "decrease" => "fas fa-fw fa-minus",
            "trash", "remove" => "fas fa-fw fa-trash-alt",
            "edit" => "fa fa-fw fa-pen",
            "import" => "fas fa-fw fa-file-import",
            "search" => "fas fa-fw fa-search",
            "clipboard" => "far fa-fw fa-clipboard",
            "clone" => "fa fa-clone fa-fw",
            "arrive" => "fas fa-plane-arrival",

            "rack", "location", "storage" => "fas fa-fw fa-boxes",
            "box" => "fas fa-fw fa-box",

            "instrument" => "fas fa-fw fa-hdd",
            "consumable" => "fas fa-fw fa-cookie-bite",
            "vendor" => "fas fa-fw  fa-store-alt",
            "price", "currency" => "fas fa-fw fa-coins",
            "recipe" => "fas fa-fw fa-list-alt",
            "resource" => "fas fa-fw fa-link",
            "user" => "fas fa-fw fa-user",
            "users", "user.group" => "fas fa-fw fa-users",
            "admin", "settings" => "fas fa-fw fa-tools",
            "engineering" => "fas fa-fw fa-cogs",
            "privacy" => "fas fa-user-lock fa-fw",

            "logout" => "fas fa-sign-out-alt fa-fw",
            "external" => "fa fa-external-link-alt fa-fw",
            "hint", "idea" => "far fa-fw fa-lightbulb",
            "tag", "annotation" => "fa-fw fas fa-tag",
            "tags", "annotations" => "fa-fw fas fa-tags",
            "logbook", "book" => "fa-fw fas fa-book",
            "calendar", "booking" => "fa-fw fas fa-calendar",

            "data" => "fa-fw fas fa-list",

            "up" => "fas fa-fw fa-arrow-up",
            "down" => "fas fa-fw fa-arrow-down",
            "left" => "fas fa-fw fa-arrow-left",
            "right" => "fas fa-fw fa-arrow-right",

            "file", "file.any" => "fas fa-fw fa-file",
            "file.powerpoint" => "fas fa-fw fa-file-powerpoint",
            "file.excel" => "fas fa-fw fa-file-excel",
            "file.word" => "fas fa-fw fa-file-word",
            "file.pdf" => "fas fa-fw fa-file-pdf",
            "download" => "fas fa-fw fa-download",
            "upload" => "fas fa-fw fa-upload",

            "attachment" => "fas fa-fw fa-paperclip",

            "other" => "fas fa-fw fa-ellipsis-v",
            "none" => "fas fa-fw",

            "generate", "automate" => "fas fa-fw fa-cog",

            default => throw new ValueError("Icon {$this->icon} does not exist."),
        };
    }

    public function getIconContent(): string
    {
        return match ($this->icon) {
            "antibody.primary", "antibody.secondary", "epitope" => '<span class="path1"></span><span class="path2"></span>',
            default => "",
        };
    }
}
