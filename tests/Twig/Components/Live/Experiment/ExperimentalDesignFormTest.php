<?php
declare(strict_types=1);

namespace App\Tests\Twig\Components\Live\Experiment;

use App\Twig\Components\Live\Experiment\ExperimentalDesignForm;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

class ExperimentalDesignFormTest extends KernelTestCase
{
    use InteractsWithLiveComponents;

    protected function setUp(): void
    {
        $kernel = self::bootKernel([
            "environment" => "test",
            "debug" => false,
        ]);

        $session = new Session(new MockFileSessionStorage());
        $request = new Request();
        $request->setSession($session);
        $stack = static::getContainer()->get(RequestStack::class);
        $stack->push($request);

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testCanRenderAndInteract()
    {
        $testComponent = $this->createLiveComponent(
            name: ExperimentalDesignForm::class,
            data: [],
        );

        $this->assertStringContainsString("Save and Return", $testComponent->render()->toString());
    }
}
