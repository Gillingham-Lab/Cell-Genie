<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\DoctrineEntity\User\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'user.admin';
    public const SCIENTIST_USER_REFERENCE = "user.flemming";

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {

    }

    public function load(ObjectManager $manager)
    {
        $admin = $this->getAdmin();
        $manager->persist($admin);

        $scientist = $this->getScientist();
        $manager->persist($scientist);

        $manager->flush();

        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);
        $this->addReference(self::SCIENTIST_USER_REFERENCE, $scientist);
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
    private function getScientist(): User
    {
        $user = (new User())
            ->setEmail("flemming@example.com")
            ->setFullName("Alexander Flemming")
            ->setIsAdmin(False)
            ->setIsActive(true)
            ->setOffice("P3N1-C")
            ->setTitle("Sir")
        ;

        $user->setPassword($this->passwordHasher->hashPassword($user, "PENICILLIN"));
        return $user;
    }
}
