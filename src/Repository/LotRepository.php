<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Lot;
use App\Genie\Enums\Availability;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Substance\UserGroupRepository;
use App\Repository\Substance\UserRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;

/**
 * @method Lot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lot[]    findAll()
 * @method Lot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lot::class);
    }

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

    protected static function tryDate(?string $date): ?\DateTimeInterface
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
}
