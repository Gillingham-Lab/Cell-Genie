<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<self::NEW, 'ConsumableCategory'>
 * @extends Voter<self::NEW|self::ATTR_*, ConsumableCategory|ConsumableLot|Consumable>
 */
class ConsumableVoter extends Voter
{
    const string ATTR_VIEW = "view";
    const string ATTR_EDIT = "edit";
    const string NEW = "new";
    const string ATTR_ADD_TO = "add_to";
    const string ATTR_TRASH = "trash";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ATTR_VIEW, self::ATTR_EDIT, self::NEW, self::ATTR_ADD_TO, self::ATTR_TRASH])) {
            return false;
        }

        if ($subject instanceof Consumable or $subject instanceof ConsumableCategory or $subject instanceof ConsumableLot) {
            return true;
        }

        if ($attribute === self::NEW and in_array($subject, ["ConsumableCategory"])) {
            return true;
        }

        return false;
    }

    /**
     * @param self::ATTR_*|self::NEW $attribute
     * @param 'ConsumableCategory'|Consumable|ConsumableCategory|ConsumableLot $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // If the attribute is not VIEW, the user is always instanceof User
        if (!$user instanceof User) {
            if ($attribute !== self::ATTR_VIEW) {
                return false;
            } else {
                return true;
            }
        }

        // Admins have always access, for now at least.
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            return true;
        }

        return match($attribute) {
            self::ATTR_VIEW => $this->canView($user, $subject),
            self::NEW => $this->canCreate($user, $subject),
            self::ATTR_ADD_TO => $this->canAddTo($user, $subject),
            self::ATTR_EDIT => $this->canEdit($user, $subject),
            self::ATTR_TRASH => $this->canTrash($user, $subject),
            default => false,
        };
    }

    private function canView(?User $user, mixed $subject): bool
    {
        if ($subject instanceof ConsumableCategory or $subject instanceof Consumable) {
            return match ($subject->getPrivacyLevel()) {
                PrivacyLevel::Public => true,
                PrivacyLevel::Group => $this->isGroupMember($user, $subject),
                PrivacyLevel::Private => $this->isOwner($user, $subject),
            };
        } elseif ($subject instanceof ConsumableLot) {
            return match ($subject->getConsumable()?->getPrivacyLevel()) {
                PrivacyLevel::Public => true,
                PrivacyLevel::Group => $this->isGroupMember($user, $subject->getConsumable()),
                PrivacyLevel::Private => $this->isOwner($user, $subject->getConsumable()),
                default => false,
            };
        } else {
            return false;
        }
    }

    private function canCreate(User $user, mixed $subject): bool
    {
        if ($subject === "ConsumableCategory") {
            return true;
        } elseif ($subject instanceof Consumable) {
            if ($subject->getCategory() === null) {
                return true;
            }

            return match ($subject->getCategory()->getPrivacyLevel()) {
                PrivacyLevel::Public, PrivacyLevel::Group => $this->isGroupMember($user, $subject),
                PrivacyLevel::Private => $this->isOwner($user, $subject),
            };
        } elseif ($subject instanceof ConsumableLot) {
            if ($subject->getConsumable() === null) {
                return false;
            }

            return match ($subject->getConsumable()->getPrivacyLevel()) {
                PrivacyLevel::Public, PrivacyLevel::Group => $this->isGroupMember($user, $subject->getConsumable()),
                PrivacyLevel::Private => $this->isOwner($user, $subject->getConsumable()),
            };
        }

        return false;
    }

    private function canAddTo(User $user, mixed $subject): bool
    {
        if ($subject instanceof ConsumableCategory or $subject instanceof Consumable) {
            return match ($subject->getPrivacyLevel()) {
                PrivacyLevel::Public, PrivacyLevel::Group => $this->isGroupMember($user, $subject),
                PrivacyLevel::Private => $this->isOwner($user, $subject),
            };
        } else {
            return false;
        }
    }

    private function canEdit(?User $user, mixed $subject): bool
    {
        if ($subject instanceof ConsumableCategory or $subject instanceof Consumable) {
            return match ($subject->getPrivacyLevel()) {
                PrivacyLevel::Group, PrivacyLevel::Public => $this->isGroupMember($user, $subject),
                PrivacyLevel::Private => $this->isOwner($user, $subject),
            };
        } elseif ($subject instanceof ConsumableLot) {
            return match ($subject->getConsumable()?->getPrivacyLevel()) {
                PrivacyLevel::Group, PrivacyLevel::Public => $this->isGroupMember($user, $subject->getConsumable()),
                PrivacyLevel::Private => $this->isOwner($user, $subject->getConsumable()),
                default => false,
            };
        } else {
            return false;
        }
    }

    private function canTrash(?User $user, mixed $subject): bool
    {
        if ($subject instanceof ConsumableLot) {
            return match ($subject->getConsumable()->getPrivacyLevel()) {
                PrivacyLevel::Group, PrivacyLevel::Public => $this->isGroupMember($user, $subject->getConsumable()),
                PrivacyLevel::Private => $this->isOwner($user, $subject->getConsumable()),
            };
        } else {
            return false;
        }
    }

    private function isGroupMember(User $user, PrivacyAwareInterface $subject): bool
    {
        return $subject->getGroup() === null or $subject->getGroup() === $user->getGroup() or $subject->getOwner() === $user;
    }

    private function isOwner(User $user, PrivacyAwareInterface $subject): bool
    {
        return $subject->getOwner() === $user;
    }
}