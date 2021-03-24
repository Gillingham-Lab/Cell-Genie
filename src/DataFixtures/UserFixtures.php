<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordEncoderInterface $passwordEncoder,
    ) {

    }

    public function load(ObjectManager $manager)
    {
        $admin = (new User())
            ->setEmail("admin@example.com")
            ->setFullName("Admin Example")
        ;
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, "CHANGEME"));

        $manager->persist($admin);

        $manager->flush();
    }
}
