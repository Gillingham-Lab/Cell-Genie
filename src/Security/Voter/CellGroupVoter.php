<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

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

        if (!($subject === "CellGroup" or $subject instanceof CellGroup)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::NEW and $subject === "CellGroup") {
            return in_array("ROLE_ADMIN", $user->getRoles());
        } elseif ($subject instanceof CellGroup) {
            return match ($attribute) {
                self::EDIT => in_array("ROLE_ADMIN", $user->getRoles()),
                self::REMOVE => in_array("ROLE_ADMIN", $user->getRoles()) && $subject->getCells()->count() === 0,
            };
        }

        return false;
    }
}