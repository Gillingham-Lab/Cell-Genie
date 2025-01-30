<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Controller;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SubstanceControllerTest extends WebTestCase
{
    public function testViewSubstanceGrantsAccessAndRedirectsToAntibody(): void
    {
        $client = static::createClient();

        $antibody = $this->createMock(Antibody::class);
        $antibody->method('getUlid')->willReturn('mock-ulid');
        $antibodyClass = get_class($antibody);

        $client->getContainer()->get('security.authorization_checker')->expects($this->once())
            ->method('isGranted')
            ->with('view', $antibodyClass)
            ->willReturn(true);

        $client->getContainer()->get('doctrine')->getManager()->expects($this->once())
            ->method('find')
            ->willReturn($antibody);

        $client->request('GET', '/substance/view/mock-ulid');

        $this->assertResponseRedirects('/antibodies/view/id/mock-ulid');
    }

    public function testViewSubstanceGrantsAccessAndRedirectsToChemical(): void
    {
        $client = static::createClient();

        $chemical = $this->createMock(Chemical::class);
        $chemical->method('getUlid')->willReturn('mock-ulid');
        $chemicalClass = get_class($chemical);

        $client->getContainer()->get('security.authorization_checker')->expects($this->once())
            ->method('isGranted')
            ->with('view', $chemicalClass)
            ->willReturn(true);

        $client->getContainer()->get('doctrine')->getManager()->expects($this->once())
            ->method('find')
            ->willReturn($chemical);

        $client->request('GET', '/substance/view/mock-ulid');

        $this->assertResponseRedirects('/compounds/view/mock-ulid');
    }

    public function testViewSubstanceGrantsAccessAndRedirectsToOligo(): void
    {
        $client = static::createClient();

        $oligo = $this->createMock(Oligo::class);
        $oligo->method('getUlid')->willReturn('mock-ulid');
        $oligoClass = get_class($oligo);

        $client->getContainer()->get('security.authorization_checker')->expects($this->once())
            ->method('isGranted')
            ->with('view', $oligoClass)
            ->willReturn(true);

        $client->getContainer()->get('doctrine')->getManager()->expects($this->once())
            ->method('find')
            ->willReturn($oligo);

        $client->request('GET', '/substance/view/mock-ulid');

        $this->assertResponseRedirects('/oligos/view/mock-ulid');
    }

    public function testViewSubstanceGrantsAccessAndRedirectsToProtein(): void
    {
        $client = static::createClient();

        $protein = $this->createMock(Protein::class);
        $protein->method('getUlid')->willReturn('mock-ulid');
        $proteinClass = get_class($protein);

        $client->getContainer()->get('security.authorization_checker')->expects($this->once())
            ->method('isGranted')
            ->with('view', $proteinClass)
            ->willReturn(true);

        $client->getContainer()->get('doctrine')->getManager()->expects($this->once())
            ->method('find')
            ->willReturn($protein);

        $client->request('GET', '/substance/view/mock-ulid');

        $this->assertResponseRedirects('/protein/view/mock-ulid');
    }

    public function testViewSubstanceGrantsAccessAndRedirectsToPlasmid(): void
    {
        $client = static::createClient();

        $plasmid = $this->createMock(Plasmid::class);
        $plasmid->method('getUlid')->willReturn('mock-ulid');
        $plasmidClass = get_class($plasmid);

        $client->getContainer()->get('security.authorization_checker')->expects($this->once())
            ->method('isGranted')
            ->with('view', $plasmidClass)
            ->willReturn(true);

        $client->getContainer()->get('doctrine')->getManager()->expects($this->once())
            ->method('find')
            ->willReturn($plasmid);

        $client->request('GET', '/substance/view/mock-ulid');

        $this->assertResponseRedirects('/plasmid/view/mock-ulid');
    }

    public function testViewSubstanceThrowsAccessDenied(): void
    {
        $client = static::createClient();

        $substance = $this->createMock(Substance::class);
        $client->getContainer()->get('doctrine')->getManager()->expects($this->once())
            ->method('find')
            ->willReturn($substance);

        $client->getContainer()->get('security.authorization_checker')->expects($this->once())
            ->method('isGranted')
            ->with('view', $substance)
            ->willReturn(false);

        $client->catchExceptions(false);

        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);

        $client->request('GET', '/substance/view/mock-ulid');
    }

    public function testViewSubstanceThrowsNotFound(): void
    {
        $client = static::createClient();

        $client->getContainer()->get('doctrine')->getManager()->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $client->request('GET', '/substance/view/mock-ulid');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}