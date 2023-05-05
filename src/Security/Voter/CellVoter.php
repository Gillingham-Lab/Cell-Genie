<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CellVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const NEW = "new";
    const ADD_ALIQUOT = "add_aliquot";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::NEW, self::ADD_ALIQUOT])) {
            return false;
        }

        if (!$subject instanceof Cell) {
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

        /** @var Cell $cell */
        $cell = $subject;

        return match ($attribute) {
            self::VIEW => true, // Cells can always be viewed
            self::EDIT => $this->canEdit($cell, $user),
            self::NEW => $this->canCreate($cell, $user),
            self::ADD_ALIQUOT => $this->canAddAliquot($cell, $user),
        };
    }

    private function canEdit(Cell $cell, User $user): bool
    {
        // Currently, there are no restrictions on editing
        return true;
    }

    private function canCreate(Cell $cell, User $user): bool
    {
        // Currently, there are no restrictions on creating
        return true;
    }

    private function canAddAliquot(Cell $cell, User $user): bool
    {
        // Currently, there are no restrictions on adding aliquots
        return true;
    }
}