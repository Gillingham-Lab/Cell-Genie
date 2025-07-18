<?php
declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<'view', null>
 */
class NullViewVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === "view" and $subject === null) {
            return true;
        } else {
            return false;
        }
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return true;
    }
}
