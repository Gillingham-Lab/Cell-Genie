<?php
declare(strict_types=1);

namespace App\FunctionalTests\Tests\Controller;

use App\Controller\ExperimentController;
use App\Repository\Experiment\ExperimentalDesignRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExperimentControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $client = static::createClient();

        $user = $client->getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist1@example.com"]);

        $client->loginUser($user);

        $this->client = $client;
    }

    public function testIndexRoute(): void
    {
        $crawler = $this->client->request("GET", "/experiment");
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewDesign(): void
    {
        // Test that a non-existing design returns 404
        $crawler = $this->client->request("GET", "/experiment/design/view/0");
        $response = $this->client->getResponse();

        $this->assertSame(404, $response->getStatusCode());

        // Test that a existing design returns 200.
        $design = $this->client->getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($design);

        $security = $this->client->getContainer()->get("security.helper");
        $this->assertTrue($security->isGranted("view", $design));

        $crawler = $this->client->request("GET", "/experiment/design/view/" . $design->getId());
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewDesignData(): void
    {
        $crawler = $this->client->request("GET", "/experiment/design/viewData/0");
        $response = $this->client->getResponse();
        $this->assertSame(404, $response->getStatusCode());

        // Test that an existing design returns 200.
        $design = $this->client->getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($design);

        $security = $this->client->getContainer()->get("security.helper");
        $this->assertTrue($security->isGranted("view", $design));

        $crawler = $this->client->request("GET", "/experiment/design/viewData/" . $design->getId());
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }
}
