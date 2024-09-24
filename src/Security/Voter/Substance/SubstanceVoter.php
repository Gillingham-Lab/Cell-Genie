<?php
declare(strict_types=1);

namespace App\Security\Voter\Substance;

use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\User\User;
use App\Security\Voter\AbstractPrivacyAwareVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SubstanceVoter extends AbstractPrivacyAwareVoter
{
    const VIEW = "view";
    const EDIT = "edit";
    const NEW = "new";
    const REMOVE = "remove";
    const OWNS = "owns";
    const ADD_LOT = "add_lot";

    const ATTRIBUTES = [
        self::VIEW,
        self::EDIT,
        self::NEW,
        self::REMOVE,
        self::OWNS,
        self::ADD_LOT,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        if (!$subject instanceof Substance and $subject !== "Substance") {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            if ($attribute !== self::VIEW) {
                return false;
            } else {
                return true;
            }
        }

        if ($subject instanceof Substance) {
            return match ($attribute) {
                self::VIEW => true,
                self::EDIT, self::ADD_LOT => $this->canEdit($user, $subject),
                self::OWNS => $subject->getOwner() === $user,
                self::REMOVE => $user->getIsAdmin(),
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