<?php
declare(strict_types=1);

namespace App\Security\Voter\Cell;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use App\Entity\DoctrineEntity\User\User;
use App\Security\Voter\AbstractPrivacyAwareVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * @extends AbstractPrivacyAwareVoter<self::ATTR_VIEW|self::ATTR_EDIT|self::ATTR_REMOVE|self::ATTR_TRASH|self::ATTR_OWNS|self::ATTR_ADD_EVENT, CellCulture>
 * @extends AbstractPrivacyAwareVoter<self::ATTR_VIEW|self::ATTR_EDIT|self::ATTR_REMOVE|self::ATTR_TRASH|self::ATTR_OWNS, CellCultureEvent>
 */
class CellCultureVoter extends AbstractPrivacyAwareVoter
{
    public const string ATTR_VIEW = "view";
    public const string ATTR_EDIT = "edit";
    public const string ATTR_NEW = "new";
    public const string ATTR_REMOVE = "remove";
    public const string ATTR_OWNS = "owns";
    public const string ATTR_TRASH = "trash";
    public const string ATTR_ADD_EVENT = "add_event";

    public const array ATTRIBUTES = [
        self::ATTR_VIEW,
        self::ATTR_EDIT,
        self::ATTR_NEW,
        self::ATTR_REMOVE,
        self::ATTR_OWNS,
        self::ATTR_TRASH,
        self::ATTR_ADD_EVENT,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject instanceof CellCulture and in_array($attribute, [self::ATTR_VIEW, self::ATTR_EDIT, self::ATTR_REMOVE, self::ATTR_TRASH, self::ATTR_OWNS, self::ATTR_ADD_EVENT])) {
            return true;
        }

        if ($subject instanceof CellCultureEvent and in_array($attribute, [self::ATTR_VIEW, self::ATTR_EDIT, self::ATTR_REMOVE, self::ATTR_TRASH, self::ATTR_OWNS])) {
            return true;
        }

        return false;
    }

    /**
     * @param self::ATTR_VIEW|self::ATTR_EDIT|self::ATTR_REMOVE|self::ATTR_TRASH|self::ATTR_OWNS|self::ATTR_ADD_EVENT $attribute
     * @param ($attribute is self::ATTR_ADD_EVENT ? CellCulture : CellCulture|CellCultureEvent) $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user) {
            return false;
        }

        assert($user instanceof User);

        if ($subject instanceof CellCulture) {
            return match ($attribute) {
                self::ATTR_VIEW => ($user->getGroup() === $subject->getGroup() or $user === $subject->getOwner() or $subject->getGroup() === null or $subject->getOwner() === null),
                self::ATTR_EDIT, self::ATTR_TRASH, self::ATTR_ADD_EVENT => $this->canEdit($user, $subject),
                self::ATTR_OWNS => $subject->getOwner() === $user,
                self::ATTR_REMOVE => $user->getIsAdmin(),
                default => false,
            };
        } elseif ($subject instanceof CellCultureEvent) {
            return match ($attribute) {
                self::ATTR_VIEW => ($user->getGroup() === $subject->getGroup() or $user === $subject->getOwner() or $subject->getGroup() === null or $subject->getOwner() === null),
                self::ATTR_EDIT, self::ATTR_REMOVE => $this->canEdit($user, $subject),
                self::ATTR_OWNS => $subject->getOwner() === $user,
                default => false,
            };
        }

        return false;
    }
}
