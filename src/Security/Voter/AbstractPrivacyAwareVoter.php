<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractPrivacyAwareVoter extends Voter
{
    protected function canEdit(User $user, PrivacyAwareInterface $entity): bool
    {
        if ($user->getIsAdmin()) {
            return true;
        }

        if ($entity->getGroup() === null) {
            return true;
        }

        if ($entity->getOwner() === $user) {
            return true;
        }

        return match($entity->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => $entity->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }
}