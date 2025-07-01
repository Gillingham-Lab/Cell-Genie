<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\OwnerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, OwnerAwareInterface>
 */
class OwnerVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject instanceof OwnerAwareInterface) {
            return true;
        }

        return false;
    }

    /**
     * @param string $attribute
     * @param OwnerAwareInterface $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if ($user === $subject->getOwner()) {
            return true;
        } else {
            return false;
        }
    }
}