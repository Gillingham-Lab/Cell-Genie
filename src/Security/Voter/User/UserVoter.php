<?php
declare(strict_types=1);

namespace App\Security\Voter\User;

use App\Entity\DoctrineEntity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const VIEW = "view";
    const NEW = "new";
    const EDIT = "edit";
    const CHANGE_GROUP = "change_group";
    const CHANGE_PASSWORD = "change_password";
    const CHANGE_IDENTITY = "change_identity";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::NEW, self::EDIT, self::CHANGE_GROUP, self::CHANGE_PASSWORD, self::CHANGE_IDENTITY])) {
            return false;
        }

        if ($subject !== "User" and !$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        // For privacy reason, only give access to users if you are logged in
        if (!$currentUser instanceof User) {
            return false;
        }

        if ($attribute === self::VIEW) {
            return true;
        }

        if ($subject instanceof User) {
            return match($attribute) {
                self::NEW, self::CHANGE_IDENTITY => in_array("ROLE_ADMIN", $currentUser->getRoles()) or in_array("ROLE_GROUP_ADMIN", $currentUser->getRoles()),
                self::EDIT, self::CHANGE_PASSWORD => $currentUser === $subject or in_array("ROLE_ADMIN", $currentUser->getRoles()) or in_array("ROLE_GROUP_ADMIN", $currentUser->getRoles()),
                self::CHANGE_GROUP => in_array("ROLE_ADMIN", $currentUser->getRoles()),
                default => false,
            };
        } elseif ($subject === "User" and $attribute === "new") {
            return in_array("ROLE_ADMIN", $currentUser->getRoles()) or in_array("ROLE_GROUP_ADMIN", $currentUser->getRoles());
        }

        return false;
    }
}