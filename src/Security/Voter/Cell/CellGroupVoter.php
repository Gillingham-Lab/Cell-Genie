<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @phpstan-extends Voter<'new', CellGroup>|Voter<'edit'|'remove', CellGroup>
 */
class CellGroupVoter extends Voter
{
    const NEW = "new";
    const EDIT = "edit";
    const REMOVE = "remove";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::NEW, self::EDIT, self::REMOVE])) {
            return false;
        }

        if ($subject === "CellGroup" and $attribute === self::NEW) {
            return true;
        }

        if ($subject instanceof CellGroup and $attribute !== self::NEW) {
            return true;
        }

        return false;
    }

    /**
     * @param self::NEW|self::EDIT|self::REMOVE $attribute
     * @param ($attribute is self::NEW ? 'CellGroup' : CellGroup) $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::NEW) {
            return in_array("ROLE_USER", $user->getRoles());
        } else {
            return match ($attribute) {
                self::EDIT => in_array("ROLE_ADMIN", $user->getRoles()) or in_array("ROLE_USER", $user->getRoles()),
                self::REMOVE => in_array("ROLE_ADMIN", $user->getRoles()) or (in_array("ROLE_USER", $user->getRoles()) && $subject->getCells()->count() === 0),
            };
        }
    }
}