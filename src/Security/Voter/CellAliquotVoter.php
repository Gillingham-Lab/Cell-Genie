<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CellAliquotVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const CONSUME = "consume";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::CONSUME])) {
            return false;
        }

        if (!$subject instanceof CellAliquot) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User and $attribute !== self::VIEW) {
            return false;
        }

        // Admins can do anything (for now)
        if ($user and $user->getIsAdmin()) {
            return true;
        }

        /** @var CellAliquot $cell */
        $aliquot = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($aliquot, $user),
            self::EDIT, self::CONSUME => $this->canEdit($aliquot, $user),
        };
    }

    private function canView(CellAliquot $aliquot, ?User $user): bool
    {
        // Unclaimed aliquots can be viewed by everyone
        if ($aliquot->getGroup() === null) {
            return true;
        }

        // If owner, then viewing is always possible
        if ($aliquot->getOwner() === $user) {
            return true;
        }

        // If not, it depends on the privacy level and the group
        return match($aliquot->getPrivacyLevel()) {
            PrivacyLevel::Public => true,
            PrivacyLevel::Group => $user and $aliquot->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }

    private function canEdit(CellAliquot $aliquot, User $user): bool
    {
        // Unclaimed aliquots can be edited by everyone
        if ($aliquot->getGroup() === null) {
            return true;
        }

        // If owner, then editing is always possible
        if ($aliquot->getOwner() === $user) {
            return true;
        }

        // If not, it depends on the privacy level and the group
        return match($aliquot->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => $aliquot->getGroup() === $user->getGroup(),
            PrivacyLevel::Private => false,
        };
    }
}