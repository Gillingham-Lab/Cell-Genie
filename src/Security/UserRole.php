<?php
declare(strict_types=1);

namespace App\Security;

use Symfony\Bundle\SecurityBundle\Security;

enum UserRole: string
{
    case User = "ROLE_USER";
    case Admin = "ROLE_ADMIN";
    case GroupAdmin = "ROLE_GROUP_ADMIN";

    case InstrumentManagement = "INSTRUMENT_MANAGEMENT";

    /**
     * @param Security $security
     * @return array<string, value-of<self>>
     */
    public static function getChoices(Security $security): array
    {
        $cases = [];

        if ($security->isGranted("ROLE_GROUP_ADMIN") or $security->isGranted("ROLE_ADMIN")) {
            $cases["Group Admin"] = self::GroupAdmin->value;
        }

        return $cases;
    }
}
