<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Security\UserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, null>
 */
class AdminVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    ) {}

    public function supports(string $attribute, mixed $subject): bool
    {
        $roles = $this->security->getUser()?->getRoles();

        if (is_array($roles) and in_array(UserRole::Admin->value, $roles)) {
            return true;
        } else {
            return false;
        }
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $roles = $token->getUser()?->getRoles();
        if (is_array($roles) and in_array(UserRole::Admin->value, $roles)) {
            return true;
        }

        return false;
    }
}
