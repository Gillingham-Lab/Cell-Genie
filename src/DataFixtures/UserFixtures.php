<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\DoctrineEntity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const ADMIN_USER_REFERENCE = 'user.admin';
    public const HEAD_SCIENTIST_USER_REFERENCE = "user.flemming";
    public const OTHER_HEAD_SCIENTIST_USER_REFERENCE = "user.hodgkin";
    public const SCIENTIST_USER_REFERENCE = "user.scientist1";
    public const OTHER_SCIENTIST_USER_REFERENCE = "user.scientist2";

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function getDependencies()
    {
        return [
            GroupFixtures::class
        ];
    }

    public function load(ObjectManager $manager)
    {
        $admin = $this->getAdmin();
        $manager->persist($admin);
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        $headScientist1 = $this->getHeadScientist();
        $manager->persist($headScientist1);
        $this->addReference(self::HEAD_SCIENTIST_USER_REFERENCE, $headScientist1);

        $headScientist2 = $this->getOtherHeadScientist();
        $manager->persist($headScientist2);
        $this->addReference(self::OTHER_HEAD_SCIENTIST_USER_REFERENCE, $headScientist2);

        $scientist1 = $this->getScientist(1);
        $scientist1->setGroup($this->getReference(GroupFixtures::RESEARCH_GROUP_REFERENCE));
        $manager->persist($scientist1);
        $this->addReference(self::SCIENTIST_USER_REFERENCE, $headScientist1);

        $scientist2 = $this->getScientist(2);
        $scientist1->setGroup($this->getReference(GroupFixtures::OTHER_GROUP_REFERENCE));
        $manager->persist($scientist2);
        $this->addReference(self::OTHER_SCIENTIST_USER_REFERENCE, $headScientist1);

        $manager->flush();
    }

    /**
     * Creates an admin account
     * @return User
     */
    private function getAdmin(): User
    {
        $admin = (new User())
            ->setEmail("admin@example.com")
            ->setFullName("Admin Example")
            ->setIsAdmin(true)
            ->setIsActive(true)
        ;
        $admin->setPassword($this->passwordHasher->hashPassword($admin, "CHANGEME"));
        return $admin;
    }

    /**
     * Creates a scientist
     * @return User
     */
    private function getHeadScientist(): User
    {
        $user = (new User())
            ->setEmail("flemming@example.com")
            ->setFullName("Alexander Flemming")
            ->setIsAdmin(False)
            ->setIsActive(true)
            ->setOffice("P3N-C")
            ->setTitle("Sir")
            ->setRoles(["ROLE_GROUP_ADMIN"])
            ->setGroup($this->getReference(GroupFixtures::RESEARCH_GROUP_REFERENCE))
        ;

        $user->setPassword($this->passwordHasher->hashPassword($user, "PENICILLIN"));
        return $user;
    }

    private function getOtherHeadScientist(): User
    {
        $user = (new User())
            ->setEmail("hodgkin@example.com")
            ->setFullName("Dorothy Hodgkin")
            ->setIsAdmin(False)
            ->setIsActive(true)
            ->setOffice("P3N-X")
            ->setTitle("Prof. Dr")
            ->setRoles(["ROLE_GROUP_ADMIN"])
            ->setGroup($this->getReference(GroupFixtures::OTHER_GROUP_REFERENCE))
        ;

        $user->setPassword($this->passwordHasher->hashPassword($user, "XRayStructure"));
        return $user;
    }

    private function getScientist(int $number): User
    {
        $user = (new User())
            ->setEmail("scientist{$number}@example.com")
            ->setFullName("Scientist {$number}")
            ->setIsAdmin(False)
            ->setIsActive(true)
            ->setOffice("P3N-00{$number}")
            ->setRoles([]);

        $user->setPassword($this->passwordHasher->hashPassword($user, "Scientist{$number}"));
        return $user;
    }
}
