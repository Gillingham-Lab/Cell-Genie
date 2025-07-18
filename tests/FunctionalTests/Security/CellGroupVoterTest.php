<?php

declare(strict_types=1);

namespace App\Tests\FunctionalTests\Security;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Repository\Cell\CellGroupRepository;
use App\Security\Voter\Cell\CellGroupVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CellGroupVoterTest extends KernelTestCase
{
    use VoterTrait;

    /**
     * @return array<array{string, int, int, int, int}>
     */
    public function getTestCases(): array
    {
        return [
            ["admin@example.com", Voter::ACCESS_GRANTED, Voter::ACCESS_GRANTED, Voter::ACCESS_GRANTED, Voter::ACCESS_GRANTED],
            ["flemming@example.com", Voter::ACCESS_GRANTED, Voter::ACCESS_GRANTED, Voter::ACCESS_DENIED, Voter::ACCESS_GRANTED],
            ["hodgkin@example.com", Voter::ACCESS_GRANTED, Voter::ACCESS_GRANTED, Voter::ACCESS_DENIED, Voter::ACCESS_GRANTED],
            ["scientist1@example.com", Voter::ACCESS_GRANTED, Voter::ACCESS_GRANTED, Voter::ACCESS_DENIED, Voter::ACCESS_GRANTED],
        ];
    }

    /**
     * @dataProvider getTestCases
     */
    public function testRights(string $email, int $new, int $edit, int $removeFull, int $removeEmpty): void
    {
        $token = $this->getTokenForUser($email);

        /** @var CellGroupVoter $voter */
        $voter = static::getContainer()->get(CellGroupVoter::class);

        $this->assertSame($new, $voter->vote($token, "CellGroup", [CellGroupVoter::ATTR_NEW]), message: "For user {$email}:");

        $cellGroup = static::getContainer()->get(CellGroupRepository::class)->findOneByNumber("CVCL_0291");

        $this->assertSame($edit, $voter->vote($token, $cellGroup, [CellGroupVoter::ATTR_EDIT]), message: "For user {$email}:");

        // Users cannot remove Cell groups that still have cells
        $this->assertSame($removeFull, $voter->vote($token, $cellGroup, [CellGroupVoter::ATTR_REMOVE]), message: "For user {$email}:");

        // But they can if the cell group is empty
        $cellGroup = $this->createMock(CellGroup::class);
        $cellGroup->method("getCells")->willReturnCallback(function () {
            $countMock = $this->createMock(ArrayCollection::class);
            $countMock->method("count")->willReturn(0);
            return $countMock;
        });

        $this->assertSame($removeEmpty, $voter->vote($token, $cellGroup, [CellGroupVoter::ATTR_REMOVE]), message: "For user {$email}:");
    }

    public function testVoterVotesDeniedIfTokenUserIsNotUser(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method("getUser")->willReturnCallback(fn() => $this->createMock(UserInterface::class));

        /** @var CellGroupVoter $voter */
        $voter = static::getContainer()->get(CellGroupVoter::class);

        $this->assertSame(Voter::ACCESS_DENIED, $voter->vote($token, "CellGroup", [CellGroupVoter::ATTR_NEW]));
    }
}
