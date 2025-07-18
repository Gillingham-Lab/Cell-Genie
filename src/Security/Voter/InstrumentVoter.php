<?php
declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\InstrumentUser;
use App\Entity\DoctrineEntity\Log;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\InstrumentRole;
use App\Genie\Enums\PrivacyLevel;
use App\Security\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<self::NEW|self::ATTR_*, Instrument|array{0: Instrument, 1: Log}>
 */
class InstrumentVoter extends Voter
{
    public const string ATTR_VIEW = "view";
    public const string ATTR_EDIT = "edit";
    public const string NEW = "new";
    public const string ATTR_BOOK = "book";
    public const string ATTR_TRAIN = "train";
    public const string ATTR_LOG_NEW = "log_new";
    public const string ATTR_LOG_EDIT = "log_edit";
    public const string ATTR_LOG_REMOVE = "log_remove";

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::ATTR_VIEW, self::ATTR_EDIT, self::NEW, self::ATTR_TRAIN, self::ATTR_BOOK, self::ATTR_LOG_NEW, self::ATTR_LOG_EDIT, self::ATTR_LOG_REMOVE])) {
            return false;
        }

        if ($subject instanceof Instrument) {
            return true;
        }

        if ($attribute === self::NEW and $subject === "Instrument") {
            return true;
        }

        if (is_array($subject) and count($subject) === 2 and $subject[0] instanceof Instrument and $subject[1] instanceof Log) {
            return true;
        }

        return false;
    }

    /**
     * @param self::NEW|self::ATTR_* $attribute
     * @param Instrument|array{0: Instrument, 1: Log} $subject
     * @param TokenInterface $token
     * @param ?Vote $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // If the attribute is not VIEW, the user is always instanceof User
        if (!$user instanceof User) {
            if ($attribute !== self::ATTR_VIEW) {
                return false;
            } else {
                return true;
            }
        }

        // Admins have always access, for now at least.
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            return true;
        }

        if (is_array($subject)) {
            [$instrument, $log] = $subject;
        } else {
            $instrument = $subject;
            $log = null;
        }

        return match ($attribute) {
            self::ATTR_VIEW => $this->canView($instrument, $user),
            self::ATTR_EDIT, self::ATTR_TRAIN => $this->canEdit($instrument, $user),
            self::NEW => $this->canCreate($instrument, $user),
            self::ATTR_BOOK => $this->canBook($instrument, $user),
            self::ATTR_LOG_NEW => $this->canChange($instrument, $user),
            self::ATTR_LOG_REMOVE, self::ATTR_LOG_EDIT => $this->canEditLogs($instrument, $log, $user),
            default => false,
        };
    }

    private function canView(Instrument $instrument, ?User $user): bool
    {
        if ($instrument->getOwner() === $user) {
            return true;
        }

        // Machines should never be private or group, but they can
        // No one except group-members will have access to non-public instruments.
        return match ($instrument->getPrivacyLevel()) {
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
        $instrumentUsers = $instrument->getUsers()->filter(fn(InstrumentUser $e) => $e->getUser() === $user and $e->getRole() === InstrumentRole::Admin or $e->getRole() === InstrumentRole::Responsible);

        return match ($instrument->getPrivacyLevel()) {
            PrivacyLevel::Public, PrivacyLevel::Group => ($instrument->getGroup() === null or $instrument->getGroup() === $user->getGroup()) and count($instrumentUsers) > 0,
            PrivacyLevel::Private => false,
        };
    }

    private function canCreate(string|Instrument $instrument, User $user): bool
    {
        if (is_string($instrument) and $instrument === "Instrument") {
            if (in_array(UserRole::InstrumentManagement->value, $user->getRoles()) or in_array(UserRole::GroupAdmin->value, $user->getRoles())) {
                return true;
            }
        }

        return false;
    }

    private function canBook(Instrument $instrument, User $user): bool
    {
        if (!$instrument->isBookable()) {
            // If the instrument is not bookable, you cannot book it ...
            return false;
        }

        return $this->canChange($instrument, $user);
    }

    private function canChange(Instrument $instrument, User $user): bool
    {
        if ($instrument->getRequiresTraining() === false) {
            // For booking an instrument that doesn't require training, you must be a group member

            if ($instrument->getGroup() === null or $instrument->getGroup() === $user->getGroup()) {
                return true;
            } else {
                // Unless you have role unlike "untrained"
                $instrumentUsers = $instrument->getUsers()->filter(fn(InstrumentUser $e) => $e->getUser() === $user and $e->getRole() !== InstrumentRole::Untrained);
                return $instrumentUsers->count() > 0;
            }
        } else {
            // For booking an instrument that requires training, you must have been trained
            // Unless you have role unlike "untrained"
            $instrumentUsers = $instrument->getUsers()->filter(fn(InstrumentUser $e) => $e->getUser() === $user and $e->getRole() !== InstrumentRole::Untrained);
            return $instrumentUsers->count() > 0;
        }
    }

    private function canEditLogs(Instrument $instrument, ?Log $log, User $user): bool
    {
        if (!$log) {
            return false;
        }

        if (!$this->canChange($instrument, $user)) {
            return false;
        }

        if ($log->getOwner() === $user) {
            return true;
        }

        // If not owner of the log, user must be admin or responsible for the instrument
        $instrumentUsers = $instrument->getUsers()->filter(fn(InstrumentUser $e) => $e->getUser() === $user and $e->getRole() !== InstrumentRole::Admin and $e->getRole() !== InstrumentRole::Responsible);
        if ($instrumentUsers->count() > 0) {
            return true;
        }

        return false;
    }
}
