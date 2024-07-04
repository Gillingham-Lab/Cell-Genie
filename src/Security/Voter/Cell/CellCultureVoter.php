<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use App\Entity\DoctrineEntity\User\User;
use App\Security\Voter\AbstractPrivacyAwareVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CellCultureVoter extends AbstractPrivacyAwareVoter
{
    const VIEW = "view";
    const EDIT = "edit";
    const NEW = "new";
    const REMOVE = "remove";
    const OWNS = "owns";
    const TRASH = "trash";
    const ADD_EVENT = "add_event";

    const ATTRIBUTES = [
        self::VIEW,
        self::EDIT,
        self::NEW,
        self::REMOVE,
        self::OWNS,
        self::TRASH,
        self::ADD_EVENT
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject instanceof CellCulture and in_array($attribute, [self::VIEW, self::EDIT, self::REMOVE, self::TRASH, self::OWNS, self::ADD_EVENT])) {
            return true;
        }

        if ($subject instanceof CellCultureEvent and in_array($attribute, [self::VIEW, self::EDIT, self::REMOVE, self::TRASH, self::OWNS])) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user) {
            return false;
        }

        assert($user instanceof User);

        if ($subject instanceof CellCulture) {
            return match($attribute) {
                self::VIEW => ($user->getGroup() === $subject->getGroup() or $user === $subject->getOwner() or $subject->getGroup() === null or $subject->getOwner() === null),
                self::EDIT, self::TRASH, self::ADD_EVENT => $this->canEdit($user, $subject),
                self::OWNS => $subject->getOwner() === $user,
                self::REMOVE => $user->getIsAdmin(),
                default => false,
            };
        } elseif ($subject instanceof CellCultureEvent) {
            return match($attribute) {
                self::VIEW => ($user->getGroup() === $subject->getGroup() or $user === $subject->getOwner() or $subject->getGroup() === null or $subject->getOwner() === null),
                self::EDIT, self::REMOVE => $this->canEdit($user, $subject),
                self::OWNS => $subject->getOwner() === $user,
                default => false,
            };
        }

        return false;
    }
}