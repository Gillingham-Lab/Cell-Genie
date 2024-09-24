<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Security;

use App\Repository\User\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait VoterTrait
{
    public function getTokenForUser(string $email): TokenInterface
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail($email);
        $this->assertNotNull($user, message: "User {$email} was not found.");

        $token = $this->createMock(TokenInterface::class);
        $token->method("getUser")->willReturn($user);

        return $token;
    }
}