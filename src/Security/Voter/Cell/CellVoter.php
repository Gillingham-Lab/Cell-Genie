<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CellVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const NEW = "new";
    const REMOVE = "remove";
    const ADD_ALIQUOT = "add_aliquot";
    const OWNS = "owns";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::NEW, self::ADD_ALIQUOT, self::REMOVE, self::OWNS])) {
            return false;
        }

        if (!$subject instanceof Cell and $subject !== "Cell") {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User and $attribute !== self::VIEW) {
            return false;
        }

        if ($subject instanceof Cell) {
            return match ($attribute) {
                self::VIEW => true, // Cells can always be viewed
                self::NEW => $this->canCreate($user),
                self::EDIT, self::ADD_ALIQUOT => $this->canEdit($subject, $user),
                self::REMOVE => $this->canRemove($subject, $user),
                self::OWNS => $subject->getOwner() === $user,
            };
        } else {
            return match($attribute) {
                self::NEW => $this->canCreate($user),
            };
        }
    }

    public function canRemove(Cell $cell, User $user): bool
    {
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            return true;
        } else {
            return false;
        }
    }

    private function canEdit(Cell $cell, User $user): bool
    {
        if ($cell->getGroup() === null) {
            return true;
        }

        if ($cell->getOwner() === $user) {
            return true;
        }

        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            return true;
        }

        return match($cell->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => $cell->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }

    private function canCreate(User $user): bool
    {
        if ($user->getIsActive()) {
            return true;
        } else {
            return false;
        }
    }
}