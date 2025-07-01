<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<self::ATTR_*, CellAliquot>
 */
class CellAliquotVoter extends Voter
{
    const string ATTR_VIEW = "view";
    const string ATTR_EDIT = "edit";
    const string ATTR_REMOVE = "remove";
    const string ATTR_CONSUME = "consume";
    const string ATTR_OWNS = "owns";
    const string ATTR_ADD_CULTURE = "add_culture";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ATTR_VIEW, self::ATTR_EDIT, self::ATTR_CONSUME, self::ATTR_REMOVE, self::ATTR_OWNS, self::ATTR_ADD_CULTURE])) {
            return false;
        }

        if (!$subject instanceof CellAliquot) {
            return false;
        }

        return true;
    }

    /**
     * @param self::ATTR_VIEW|self::ATTR_EDIT|self::ATTR_CONSUME|self::ATTR_REMOVE|self::ATTR_OWNS|self::ATTR_ADD_CULTURE $attribute
     * @param CellAliquot $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        $aliquot = $subject;

        if (!$user instanceof User) {
            if ($attribute === self::ATTR_VIEW) {
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
            self::ATTR_VIEW => $this->canView($aliquot, $user),
            self::ATTR_EDIT, self::ATTR_CONSUME, self::ATTR_ADD_CULTURE => $this->canEdit($aliquot, $user),
            self::ATTR_REMOVE => $this->canRemove($aliquot, $user),
            self::ATTR_OWNS => $aliquot->getOwner() === $user,
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