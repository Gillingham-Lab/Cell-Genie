<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\OwnerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OwnerVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($subject instanceof OwnerAwareInterface) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
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