<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Genie\Enums\PrivacyLevel;
use App\Security\UserRole;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractPrivacyAwareVoter extends Voter
{
    protected function canEdit(User $user, PrivacyAwareInterface $entity): bool
    {
        if ($entity->getGroup() === null) {
            return true;
        }

        return match($entity->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => $entity->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }

    protected function canView(User $user, PrivacyAwareInterface $entity): bool
    {
        if ($entity->getGroup() === null) {
            return true;
        }

        return match($entity->getPrivacyLevel()) {
            PrivacyLevel::Public => true,
            PrivacyLevel::Group => $entity->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }

    protected function canRemove(User $user, PrivacyAwareInterface $entity): bool
    {
        return match($entity->getPrivacyLevel()) {
            PrivacyLevel::Public => true,
            PrivacyLevel::Group => $entity->getGroup() === $user->getGroup() && in_array(UserRole::GroupAdmin->value, $user->getRoles()),
            PrivacyLevel::Private => false,
        };
    }
}