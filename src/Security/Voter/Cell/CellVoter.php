<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<self::NEW, 'Cell'>
 * @extends Voter<self::ATTR_*, Cell>
 */
class CellVoter extends Voter
{
    public const string ATTR_VIEW = "view";
    public const string ATTR_EDIT = "edit";
    public const string NEW = "new";
    public const string ATTR_REMOVE = "remove";
    public const string ATTR_ADD_ALIQUOT = "add_aliquot";
    public const string ATTR_OWNS = "owns";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ATTR_VIEW, self::ATTR_EDIT, self::NEW, self::ATTR_ADD_ALIQUOT, self::ATTR_REMOVE, self::ATTR_OWNS])) {
            return false;
        }

        if ($subject instanceof Cell and $attribute !== self::NEW) {
            return true;
        }

        if ($subject !== "Cell" and $attribute === self::NEW) {
            return false;
        }

        return true;
    }

    /**
     * @param self::ATTR_*|self::NEW $attribute
     * @param ($attribute is self::NEW ? 'Cell' : Cell) $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            if ($attribute !== self::ATTR_VIEW) {
                return false;
            } else {
                return true;
            }
        }

        if ($subject instanceof Cell) {
            return match ($attribute) {
                self::ATTR_VIEW => true, // Cells can always be viewed
                self::NEW => $this->canCreate($user),
                self::ATTR_EDIT, self::ATTR_ADD_ALIQUOT => $this->canEdit($subject, $user),
                self::ATTR_REMOVE => $this->canRemove($subject, $user),
                self::ATTR_OWNS => $subject->getOwner() === $user,
            };
        } elseif ($attribute === self::NEW) {
            return $this->canCreate($user);
        } else {
            return false;
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

        return match ($cell->getPrivacyLevel()) {
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
