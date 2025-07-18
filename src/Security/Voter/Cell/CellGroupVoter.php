<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @phpstan-extends Voter<self::ATTR_NEW, "CellGroup">
 * @phpstan-extends Voter<self::ATTR_EDIT|self::ATTR_REMOVE, CellGroup>
 */
class CellGroupVoter extends Voter
{
    public const string ATTR_NEW = "new";
    public const string ATTR_EDIT = "edit";
    public const string ATTR_REMOVE = "remove";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ATTR_NEW, self::ATTR_EDIT, self::ATTR_REMOVE])) {
            return false;
        }

        if ($subject === "CellGroup" and $attribute === self::ATTR_NEW) {
            return true;
        }

        if ($subject instanceof CellGroup and $attribute !== self::ATTR_NEW) {
            return true;
        }

        return false;
    }

    /**
     * @param self::ATTR_NEW|self::ATTR_EDIT|self::ATTR_REMOVE $attribute
     * @param ($attribute is self::ATTR_NEW ? 'CellGroup' : CellGroup) $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::ATTR_NEW) {
            return in_array("ROLE_USER", $user->getRoles());
        } else {
            return match ($attribute) {
                self::ATTR_EDIT => in_array("ROLE_ADMIN", $user->getRoles()) or in_array("ROLE_USER", $user->getRoles()),
                self::ATTR_REMOVE => in_array("ROLE_ADMIN", $user->getRoles()) or (in_array("ROLE_USER", $user->getRoles()) && $subject->getCells()->count() === 0),
            };
        }
    }
}
