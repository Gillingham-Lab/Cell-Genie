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
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConsumableVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const NEW = "new";
    const ADD_TO = "add_to";
    const TRASH = "trash";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::NEW, self::ADD_TO, self::TRASH])) {
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

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the attribute is not VIEW, the user is always instanceof User
        if (!$user instanceof User and $attribute !== self::VIEW) {
            return false;
        }

        // Admins have always access, for now at least.
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            return true;
        }

        return match($attribute) {
            self::VIEW => $this->canView($user, $subject),
            self::NEW => $this->canCreate($user, $subject),
            self::ADD_TO => $this->canAddTo($user, $subject),
            self::EDIT => $this->canEdit($user, $subject),
            self::TRASH => $this->canTrash($user, $subject),
            default => false,
        };
    }

    private function canView(?User $user, $subject): bool
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

    private function canCreate(User $user, $subject): bool
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

    private function canAddTo(User $user, $subject): bool
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

    private function canEdit(?User $user, $subject): bool
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

    private function canTrash(?User $user, $subject): bool
    {
        if ($subject instanceof ConsumableLot) {
            return match ($subject->getConsumable()->getPrivacyLevel()) {
                PrivacyLevel::Group, PrivacyLevel::Public => $this->isGroupMember($user, $subject->getConsumable()),
                PrivacyLevel::Private => $this->isOwner($user, $subject->getConsumable()),
                default => false,
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