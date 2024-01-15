<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Security\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    public function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $roles = $token->getUser()?->getRoles();
        if (is_array($roles) and in_array(UserRole::Admin->value, $roles)) {
            return true;
        }

        return false;
    }
}