<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Entity\DoctrineEntity;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\User\User;
use App\Tests\TestTraits\EntityManagerSetUp;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InstrumentTest extends KernelTestCase
{
    use EntityManagerSetUp;

    public function testGetResponsibleUsers(): void
    {
        $instrument = $this->entityManager->getRepository(Instrument::class)->findOneBy(["instrumentNumber" => "LC-001"]);

        $responsiblePeople = $instrument->getResponsibleUsers();

        $this->assertCount(1, $responsiblePeople);
        $this->assertContainsOnlyInstancesOf(User::class, $responsiblePeople);
    }
}