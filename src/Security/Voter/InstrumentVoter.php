<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\InstrumentUser;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\InstrumentRole;
use App\Genie\Enums\PrivacyLevel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InstrumentVoter extends Voter
{
    const VIEW = "view";
    const EDIT = "edit";
    const NEW = "new";
    const BOOK = "book";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::NEW, self::BOOK])) {
            return false;
        }

        if (!$subject instanceof Instrument) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the attribute is not VIEW, the user is always instanceof User
        if (!$user instanceof User and $attribute !== self::VIEW) {
            return false;
        }

        // Admins have always access, for now at least.
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            return true;
        }

        /** @var Instrument $cell */
        $instrument = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($instrument, $user),
            self::EDIT => $this->canEdit($instrument, $user),
            self::NEW => $this->canCreate($instrument, $user),
            self::BOOK => $this->canBook($instrument, $user),
        };
    }

    private function canView(Instrument $instrument, ?User $user): bool
    {
        if ($instrument->getOwner() === $user) {
            return true;
        }

        // Machines should never be pricate or group, but they can
        // No one except group-members will have access to non-public instruments.
        return match($instrument->getPrivacyLevel()) {
            PrivacyLevel::Public => true,
            PrivacyLevel::Group => $user and ($instrument->getGroup() === null or $instrument->getGroup() === $user->getGroup()),
            PrivacyLevel::Private => false,
        };
    }

    private function canEdit(Instrument $instrument, User $user): bool
    {
        if ($instrument->getOwner() === $user) {
            return true;
        }

        // Only group members responsible for the machine can change it
        // Filter for specific rows
        $instrumentUsers = $instrument->getUsers()->filter(fn (InstrumentUser $e) => $e->getUser() === $user and $e->getRole() === InstrumentRole::Admin or $e->getRole() === InstrumentRole::Responsible);

        return match($instrument->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => ($instrument->getGroup() === null or $instrument->getGroup() === $user->getGroup()) and count($instrumentUsers) > 0,
            PrivacyLevel::Private => false,
        };
    }

    private function canCreate(Instrument $instrument, User $user): bool
    {
        // For now, all people can register machines
        // Specific roles will be made later.
        return true;
    }

    private function canBook(Instrument $instrument, User $user): bool
    {
        if (!$instrument->isBookable()) {
            // If the instrument is not bookable, you cannot book it ...
            return false;
        }

        // For booking an instrument that doesn't require training, you must be a group member
        if ($instrument->getRequiresTraining() === false) {
            if ($instrument->getGroup() === null or $instrument->getGroup() === $user->getGroup()) {
                return true;
            } else {
                // Unless you have role unlike "untrained"
                $instrumentUsers = $instrument->getUsers()->filter(fn (InstrumentUser $e) => $e->getUser() === $user and $e->getRole() !== InstrumentRole::Untrained);
                return $instrumentUsers->count() > 0;
            }
        }

        // For booking an instrument that requires training, you must have been trained
        if ($instrument->getRequiresTraining() === true) {
            // Unless you have role unlike "untrained
            $instrumentUsers = $instrument->getUsers()->filter(fn (InstrumentUser $e) => $e->getUser() === $user and $e->getRole() !== InstrumentRole::Untrained);
            return $instrumentUsers->count() > 0;
        }

        return false;
    }
}