<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends AbstractPrivacyAwareVoter<self::ATTR_*, PrivacyAwareInterface>
 */
class PrivacyAwareVoter extends AbstractPrivacyAwareVoter
{
    const string ATTR_VIEW = "view";
    const string ATTR_EDIT = "edit";
    const string ATTR_REMOVE = "remove";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ATTR_VIEW, self::ATTR_EDIT, self::ATTR_REMOVE])) {
            return false;
        }

        if ($subject instanceof PrivacyAwareInterface) {
            return true;
        }

        return false;
    }

    /**
     * @param self::ATTR_VIEW|self::ATTR_EDIT|self::ATTR_REMOVE $attribute
     * @param PrivacyAwareInterface $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        /** @var User $user */
        $user = $token->getUser();

        return match($attribute) {
            self::ATTR_VIEW => $this->canView($user, $subject),
            self::ATTR_EDIT => $this->canEdit($user, $subject),
            self::ATTR_REMOVE => $this->canRemove($user, $subject),
        };
    }
}