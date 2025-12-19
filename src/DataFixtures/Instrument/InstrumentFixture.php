<?php
declare(strict_types=1);

namespace App\DataFixtures\Instrument;

use App\DataFixtures\GroupFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\InstrumentUser;
use App\Entity\DoctrineEntity\User\User;
use App\Genie\Enums\InstrumentRole;
use App\Genie\Enums\PrivacyLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InstrumentFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            $this->getReference(UserFixtures::HEAD_SCIENTIST_USER_REFERENCE, User::class),
            $this->getReference(UserFixtures::SCIENTIST_USER_REFERENCE, User::class),
            $this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class),
        ];

        $instrument = (new Instrument())
            ->setInstrumentNumber("LC-001")
            ->setShortName("HPLC 1")
            ->setLongName("Gadgilent 1337")
            ->setOwner($users[0])
            ->setGroup($users[0]->getGroup())
            ->setPrivacyLevel(PrivacyLevel::Group)
            ->setModelNumber("GALC1337-1260")
            ->setSerialNumber("GA202303211200")
        ;

        $instrumentUsers = [
            (new InstrumentUser())->setInstrument($instrument)->setUser($users[0])->setRole(InstrumentRole::Responsible),
            (new InstrumentUser())->setInstrument($instrument)->setUser($users[1])->setRole(InstrumentRole::Trained),
            (new InstrumentUser())->setInstrument($instrument)->setUser($users[2])->setRole(InstrumentRole::Admin),
        ];

        array_map(fn (InstrumentUser $user) => $instrument->addUser($user), $instrumentUsers);
        $manager->persist($instrument);
        $manager->flush();
    }
}