<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VendorVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === "new" and $subject === "vendor") {
            return true;
        } else {
            return false;
        }
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the attribute is not VIEW, the user is always instanceof User
        if (!$user instanceof User and $attribute !== "view") {
            return false;
        }

        return true;
    }
}