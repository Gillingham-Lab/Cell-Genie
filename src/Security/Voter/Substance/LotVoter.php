<?php
declare(strict_types=1);

namespace App\Security\Voter\Substance;

use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Lot;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class LotVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const REMOVE = "remove";
    const OWNS = "owns";

    const ATTRIBUTES = [
        self::VIEW,
        self::EDIT,
        self::REMOVE,
        self::OWNS,
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

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User and $attribute !== self::VIEW) {
            return false;
        }

        if ($subject instanceof Lot) {
            return match ($attribute) {
                self::VIEW => true,
                self::EDIT => $this->canEdit($subject, $user),
                self::OWNS => $subject->getOwner() === $user,
                self::REMOVE => $user->getIsAdmin(),
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