<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CellAliquotVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const REMOVE = "remove";
    const CONSUME = "consume";
    const OWNS = "owns";
    const ADD_CULTURE = "add_culture";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::CONSUME, self::REMOVE, self::OWNS, self::ADD_CULTURE])) {
            return false;
        }

        if (!$subject instanceof CellAliquot) {
            return false;
        }

        return true;
    }

    /**
     * @param self::VIEW|self::EDIT|self::CONSUME|self::REMOVE|self::OWNS|self::ADD_CULTURE $attribute
     * @param CellAliquot $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $aliquot = $subject;

        if (!$user instanceof User) {
            if ($attribute === self::VIEW) {
                return $this->canView($subject, null);
            } else {
                return false;
            }
        }

        // Admins can do anything (for now)
        if ($user->getIsAdmin()) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($aliquot, $user),
            self::EDIT, self::CONSUME, self::ADD_CULTURE => $this->canEdit($aliquot, $user),
            self::REMOVE => $this->canRemove($aliquot, $user),
            self::OWNS => $aliquot->getOwner() === $user,
        };
    }

    private function canView(CellAliquot $aliquot, ?User $user): bool
    {
        // Unclaimed aliquots can be viewed by everyone
        if ($aliquot->getGroup() === null) {
            return true;
        }

        // If owner, then viewing is always possible
        if ($aliquot->getOwner() === $user) {
            return true;
        }

        // If not, it depends on the privacy level and the group
        return match($aliquot->getPrivacyLevel()) {
            PrivacyLevel::Public => true,
            PrivacyLevel::Group => $user and $aliquot->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }

    private function canEdit(CellAliquot $aliquot, User $user): bool
    {
        // Unclaimed aliquots can be edited by everyone
        if ($aliquot->getGroup() === null) {
            return true;
        }

        // If owner, then editing is always possible
        if ($aliquot->getOwner() === $user) {
            return true;
        }

        // If not, it depends on the privacy level and the group
        return match($aliquot->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => $aliquot->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }

    private function canRemove(CellAliquot $aliquot, User $user): bool
    {
        // Removal is only possible by admins
        if (in_array("ROLE_GROUP_ADMIN", $user->getRoles()) and $aliquot->getGroup() === $user->getGroup()) {
            return true;
        }
        else {
            return false;
        }
    }
}