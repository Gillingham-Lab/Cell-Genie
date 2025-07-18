<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Param\ParamBag;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

readonly class EnumerationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function getNextNumber(User $user, string $enumerationType): ?string
    {
        return match ($enumerationType) {
            "antibody" => $this->getNextNumberedSubstanceNumber($user, Antibody::class, "numberingAntibody"),
            "cell" => $this->getNextCellNumber($user),
            "cell_culture" => $this->getNextNumberedSubstanceNumber($user, CellCulture::class, "numberingCellCulture"),
            "plasmid" => $this->getNextNumberedSubstanceNumber($user, Plasmid::class, "numberingPlasmid"),
            "chemical" => $this->getNextSubstanceNumber($user, Substance::class, "numberingChemical"),
            "oligo" => $this->getNextSubstanceNumber($user, Substance::class, "numberingOligo"),
            "protein" => $this->getNextSubstanceNumber($user, Substance::class, "numberingProtein"),
            default => null,
        };
    }

    /**
     * @param class-string $class
     */
    public function getNextNumberedSubstanceNumber(User $user, string $class, string $prefixType): ?string
    {
        $prefix = $this->buildPrefix($user, $prefixType);

        $results = $this->entityManager->getRepository($class)
            ->createQueryBuilder("s")
            ->select("s.number")
            ->where("s.number LIKE :prefix")
            ->setParameter("prefix", "$prefix%")
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        return $this->getNextNumberInLine($results, $prefix);
    }

    /**
     * @param class-string $class
     */
    public function getNextSubstanceNumber(User $user, string $class, string $prefixType): ?string
    {
        $prefix = $this->buildPrefix($user, $prefixType);

        $results = $this->entityManager->getRepository($class)
            ->createQueryBuilder("s")
            ->select("s.shortName")
            ->where("s.shortName LIKE :prefix")
            ->setParameter("prefix", "$prefix%")
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        return $this->getNextNumberInLine($results, $prefix);
    }

    public function getNextCellNumber(User $user): ?string
    {
        $prefix = $this->buildPrefix($user, "numberingCell");

        $results = $this->entityManager->getRepository(Cell::class)->createQueryBuilder("c")
            ->select("c.cellNumber")
            ->where("c.cellNumber LIKE :prefix")
            ->setParameter("prefix", "$prefix%")
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        return $this->getNextNumberInLine($results, $prefix);
    }

    public function buildPrefix(User $user, string $prefixType): ?string
    {
        $group = $user->getGroup();
        $userSigill = $user->getSettings()->getParam("sigill")?->asString();
        $userEntitySettings = $user->getSettings()->getParam($prefixType)?->getValue();

        if (!$group) {
            if (!$userSigill) {
                throw new LogicException("Cannot create a prefix without a group or a sigill");
            }

            return $userSigill;
        }

        if ($userEntitySettings instanceof ParamBag) {
            $userSigill = $userSigill . $userEntitySettings->getParam("prefix", "")->asString();
        }

        $groupSigill = $group->getSettings()->getParam("numberingGroupPrefix")?->asString();

        $groupEntitySettings = $group->getSettings()->getParam($prefixType)?->getValue();
        if ($groupEntitySettings instanceof ParamBag) {
            $useUserSigill = $groupEntitySettings->getParam("userSigill", false)->asBool();

            if ($useUserSigill && !$userSigill) {
                throw new LogicException("Group policy required sigill, but sigill is empty. You might want to set your sigill in your user settings.");
            }

            $userSigill = $useUserSigill ? $userSigill : "";
            $entitySigill = $groupEntitySettings->getParam("prefix", "")->asString();

            return "$groupSigill$entitySigill$userSigill";
        } else {
            if (!$userSigill) {
                throw new LogicException("Cannot create a prefix without group policies or a sigill");
            }

            return $userSigill;
        }
    }

    /**
     * @param list<string> $numbers
     */
    public function getNextNumberInLine(array $numbers, string $prefix): string
    {
        // Filter non-desired elements out
        $numbers = array_filter($numbers, fn($number) => is_numeric(substr($number, strlen($prefix))));

        if (count($numbers) === 0) {
            $nextNumber = 1;

            // Set default number length
            $lastNumberLength = 3;
        } else {
            // Natural sort
            natsort($numbers);

            // Get last element
            $lastElement = end($numbers);

            // Strip away prefix
            $lastNumber = substr($lastElement, strlen($prefix));
            $lastNumberLength = strlen($lastNumber);

            // Convert to integer, increase and format back to string
            $nextNumber = (int) $lastNumber + 1;
        }

        $nextNumber = sprintf("%0{$lastNumberLength}d", $nextNumber);
        return "$prefix$nextNumber";
    }
}
