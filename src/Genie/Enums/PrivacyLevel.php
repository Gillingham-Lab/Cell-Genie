<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum PrivacyLevel: int
{
    case Private = 0;
    case Group = 1;
    case Public = 2;

    public function label()
    {
        return match($this) {
            self::Public => "Public readable, group writeable",
            self::Group => "Group readable and writable",
            self::Private => "Owner readable and writeable",
        };
    }
}
