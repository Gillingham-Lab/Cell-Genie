<?php
declare(strict_types=1);

namespace App\Security\Voter\Substance;

use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\User\User;
use App\Security\Voter\AbstractPrivacyAwareVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @extends AbstractPrivacyAwareVoter<self::NEW, 'Substance'>
 * @extends AbstractPrivacyAwareVoter<self::ATTR_*, Substance>
 */
class SubstanceVoter extends AbstractPrivacyAwareVoter
{
    const string ATTR_VIEW = "view";
    const string ATTR_EDIT = "edit";
    const string NEW = "new";
    const string ATTR_REMOVE = "remove";
    const string ATTR_OWNS = "owns";
    const string ATTR_ADD_LOT = "add_lot";

    const ATTRIBUTES = [
        self::ATTR_VIEW,
        self::ATTR_EDIT,
        self::NEW,
        self::ATTR_REMOVE,
        self::ATTR_OWNS,
        self::ATTR_ADD_LOT,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        if ($subject instanceof Substance and $attribute !== self::NEW) {
            return true;
        }

        if ($subject === "Substance" and $attribute === self::NEW) {
            return true;
        }

        return false;
    }

    /**
     * @param self::NEW|self::ATTR_* $attribute
     * @param ($attribute is self::NEW ? 'Substance' : Substance) $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            if ($attribute !== self::ATTR_VIEW) {
                return false;
            } else {
                return true;
            }
        }

        if ($subject instanceof Substance) {
            return match ($attribute) {
                self::ATTR_VIEW => true,
                self::ATTR_EDIT, self::ATTR_ADD_LOT => $this->canEdit($user, $subject),
                self::ATTR_OWNS => $subject->getOwner() === $user,
                self::ATTR_REMOVE => $user->getIsAdmin(),
                default => false,
            };
        } elseif ($subject === "Substance") {
            return match ($attribute) {
                self::NEW => $this->canCreate($user),
                default => false,
            };
        } else {
            return false;
        }
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