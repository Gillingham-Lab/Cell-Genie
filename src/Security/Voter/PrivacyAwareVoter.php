<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PrivacyAwareVoter extends AbstractPrivacyAwareVoter
{
    const VIEW = "view";
    const EDIT = "edit";
    const REMOVE = "remove";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::REMOVE])) {
            return false;
        }

        if ($subject instanceof PrivacyAwareInterface) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        /** @var User $user */
        $user = $token->getUser();

        return false;

        return match($attribute) {
            self::VIEW => $this->canView($user, $subject),
            self::EDIT => $this->canEdit($user, $subject),
            self::REMOVE => $this->canRemove($user, $subject),
        };
    }
}