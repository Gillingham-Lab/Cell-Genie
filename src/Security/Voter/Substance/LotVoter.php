<?php
declare(strict_types=1);

namespace App\Security\Voter\Substance;

use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<self::ATTR_*, Lot>
 */
class LotVoter extends Voter
{
    const string ATTR_VIEW = "view";
    const string ATTR_EDIT = "edit";
    const string ATTR_REMOVE = "remove";
    const string ATTR_OWNS = "owns";

    const ATTRIBUTES = [
        self::ATTR_VIEW,
        self::ATTR_EDIT,
        self::ATTR_REMOVE,
        self::ATTR_OWNS,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        if (!$subject instanceof Lot) {
            return false;
        }

        return true;
    }

    /**
     * @param self::ATTR_* $attribute
     * @param Lot $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User and $attribute !== self::ATTR_VIEW) {
            return false;
        }

        if ($subject instanceof Lot) {
            return match ($attribute) {
                self::ATTR_VIEW => true,
                self::ATTR_EDIT => $this->canEdit($subject, $user),
                self::ATTR_OWNS => $subject->getOwner() === $user,
                self::ATTR_REMOVE => $user->getIsAdmin(),
                default => false,
            };
        }  else {
            return false;
        }
    }

    private function canEdit(Lot $lot, User $user): bool
    {
        if ($lot->getGroup() === null) {
            return true;
        }

        if ($lot->getOwner() === $user) {
            return true;
        }

        return match($lot->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => $lot->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }
}