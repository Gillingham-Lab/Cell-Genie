<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\SubstanceLot;
use App\Genie\Enums\Availability;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Storage\BoxRepository;
use App\Repository\User\UserGroupRepository;
use App\Repository\User\UserRepository;
use App\Service\Doctrine\Type\Ulid;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lot>
 */
class LotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lot::class);
    }

    /**
     * @param array{
     *     number: string,
     *     lotNumber: string,
     *     comment: string,
     *     availability: value-of<Availability>,
     *     boughtOn: string,
     *     privacyLevel: value-of<PrivacyLevel>,
     *     owner: string,
     *     group: string,
     *     box: string,
     *     boxCoordinate: ?string,
     *     amount: string,
     *     purity: string,
     *     numberOfAliquotes: numeric-string,
     *     maxNumberOfAliquots: numeric-string,
     *     aliquoteSize: ?string,
     * } $data
     */
    public static function createFromArray(
        UserRepository $userRepository,
        UserGroupRepository $groupRepository,
        BoxRepository $boxRepository,
        array $data
    ): Lot {
        $lot = new Lot();
        $lot->setNumber($data["number"]);
        $lot->setLotNumber($data["lotNumber"]);
        $lot->setComment($data["comment"]);
        $lot->setAvailability(Availability::from($data["availability"]));
        $lot->setBoughtOn(self::tryDate($data["boughtOn"]));
        $lot->setPrivacyLevel(PrivacyLevel::from(intval($data["privacyLevel"])));
        $lot->setOwner($userRepository->find(Ulid::fromString($data["owner"])));
        $lot->setBoughtBy($userRepository->find(Ulid::fromString($data["owner"])));
        $lot->setGroup($groupRepository->find(Ulid::fromString($data["group"])));

        $lot->setBox($boxRepository->find(Ulid::fromString($data["box"])));
        $lot->setBoxCoordinate($data["boxCoordinate"]);
        $lot->setAmount($data["amount"]);
        $lot->setPurity($data["purity"]);
        $lot->setNumberOfAliquotes(intval($data["numberOfAliquotes"]));
        $lot->setMaxNumberOfAliquots(intval($data["maxNumberOfAliquots"]));
        $lot->setAliquoteSize($data["aliquoteSize"]);

        return $lot;
    }

    protected static function tryDate(?string $date): ?DateTimeInterface
    {
        if ($date === null) {return null;}

        $parsedDate = \DateTime::createFromFormat("Y-m-d", $date);

        if ($parsedDate === false) {
            $parsedDate = \DateTime::createFromFormat("d.m.Y", $date);
        }

        if ($parsedDate === false) {
            $parsedDate = \DateTime::createFromFormat("d. m. Y", $date);
        }

        return $parsedDate !== false ? $parsedDate : null;
    }

    /**
     * @param class-string $class
     * @param array<int, string|Ulid> $lotIds
     * @return SubstanceLot[]
     */
    public function getLotsWithSubstance(string $class, array $lotIds): array
    {
        if (!is_subclass_of($class, Substance::class)) {
            throw new \TypeError("Only subclasses of " . Substance::class ." are accepted for getLotsWithSubstance, {$class} was given.");
        }

        $query = $this->getEntityManager()->createQuery(
            "SELECT s, l FROM ". $class ." s LEFT JOIN s.lots l WHERE l.id IN (:ids)"
        )->setParameter("ids", $lotIds);

        $results = $query->getResult();
        $substanceLots = [];

        //  Multiple lots for a single substances are folded into that substance's lot.
        foreach ($results as $result) {
            foreach ($result->getLots() as $lot) {
                // Thus, we need to go through *all* retrieved lots.
                $substanceLots[] = new SubstanceLot($result, $lot);
            }
        }

        return $substanceLots;
    }
}
