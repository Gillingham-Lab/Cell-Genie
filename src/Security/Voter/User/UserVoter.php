<?php
declare(strict_types=1);

namespace App\Security\Voter\User;

use App\Entity\DoctrineEntity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<self::NEW, 'User'>
 * @extends Voter<self::ATTR_*, User>
 */
class UserVoter extends Voter
{
    const string ATTR_VIEW = "view";
    const string NEW = "new";
    const string ATTR_EDIT = "edit";
    const string ATTR_CHANGE_GROUP = "change_group";
    const string ATTR_CHANGE_PASSWORD = "change_password";
    const string ATTR_CHANGE_IDENTITY = "change_identity";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ATTR_VIEW, self::NEW, self::ATTR_EDIT, self::ATTR_CHANGE_GROUP, self::ATTR_CHANGE_PASSWORD, self::ATTR_CHANGE_IDENTITY])) {
            return false;
        }

        if ($subject === "User" and $attribute === self::NEW) {
            return true;
        }

        if ($subject instanceof User and $attribute !== self::NEW) {
            return true;
        }

        return false;
    }

    /**
     * @param self::NEW|self::ATTR_* $attribute
     * @param ($attribute is self::NEW ? 'User' : User) $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        // For privacy reason, only give access to users if you are logged in
        if (!$currentUser instanceof User) {
            return false;
        }

        if ($attribute === self::ATTR_VIEW) {
            return true;
        }

        if ($subject instanceof User) {
            return match($attribute) {
                self::NEW, self::ATTR_CHANGE_IDENTITY => in_array("ROLE_ADMIN", $currentUser->getRoles()) or in_array("ROLE_GROUP_ADMIN", $currentUser->getRoles()),
                self::ATTR_EDIT, self::ATTR_CHANGE_PASSWORD => $currentUser === $subject or in_array("ROLE_ADMIN", $currentUser->getRoles()) or in_array("ROLE_GROUP_ADMIN", $currentUser->getRoles()),
                self::ATTR_CHANGE_GROUP => in_array("ROLE_ADMIN", $currentUser->getRoles()),
                default => false,
            };
        } elseif ($subject === "User" and $attribute === "new") {
            return in_array("ROLE_ADMIN", $currentUser->getRoles()) or in_array("ROLE_GROUP_ADMIN", $currentUser->getRoles());
        }

        return false;
    }
}