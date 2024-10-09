<?php
declare(strict_types=1);

namespace App\FunctionalTests\Tests\Controller;

use App\Controller\ExperimentController;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\ExperimentalFieldVariableRoleEnum;
use App\Genie\Enums\FormRowTypeEnum;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Experiment\ExperimentalDesignRepository;
use App\Repository\Experiment\ExperimentalRunRepository;
use App\Repository\Substance\ChemicalRepository;
use App\Repository\User\UserRepository;
use App\Tests\TestTraits\NestedFormAssertions;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExperimentControllerTest extends WebTestCase
{
    use NestedFormAssertions;

    public function testIndexRouteAsGroupScientist(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist1@example.com"]);
        $client->loginUser($user);

        $crawler = $client->request("GET", "/experiment");
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewDesignAsGroupScientist(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist1@example.com"]);
        $client->loginUser($user);

        // Test that a non-existing design returns 404
        $crawler = $client->request("GET", "/experiment/design/view/0");
        $response = $client->getResponse();

        $this->assertSame(404, $response->getStatusCode());

        // Test that a existing design returns 200.
        $design = $client->getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($design);

        $security = $client->getContainer()->get("security.helper");
        $this->assertTrue($security->isGranted("view", $design));

        $crawler = $client->request("GET", "/experiment/design/view/" . $design->getId());
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testViewDesignDataAsGroupScientist(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist1@example.com"]);
        $client->loginUser($user);

        $crawler = $client->request("GET", "/experiment/design/viewData/0");
        $response = $client->getResponse();
        $this->assertSame(404, $response->getStatusCode());

        // Test that an existing design returns 200.
        $design = $client->getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($design);

        $security = $client->getContainer()->get("security.helper");
        $this->assertTrue($security->isGranted("view", $design));

        $crawler = $client->request("GET", "/experiment/design/viewData/" . $design->getId());
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testDownloadDesignDataAsGroupScientist(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist1@example.com"]);
        $client->loginUser($user);

        $crawler = $client->request("GET", "/api/public/experiment/design/viewData/0");
        $response = $client->getResponse();
        $this->assertSame(404, $response->getStatusCode());

        // Test that an existing design returns 200.
        $design = $client->getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($design);

        $crawler = $client->request("GET", "/api/public/experiment/design/viewData/" . $design->getId());
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("text/plain; charset=UTF-8", $response->headers->get('Content-Type'));

        // To test more, get the other compounds, too
        $pen1 = $client->getContainer()->get(ChemicalRepository::class)->findOneBy(["shortName" => "Pen1"]);
        $pen2 = $client->getContainer()->get(ChemicalRepository::class)->findOneBy(["shortName" => "Pen2"]);

        $this->assertNotNull($pen1);
        $this->assertNotNull($pen2);

        // Check the results
        $content_lines = array_map(fn ($row) => explode("\t", $row), explode("\n", trim($response->getContent())));

        $this->assertStringStartsWith("#TotalNumberOfRows\t2\n", $response->getContent());
        $this->assertCount(4, $content_lines);
        $this->assertCount(5, $content_lines[1]);

        $this->assertSame($pen1->getShortName(), $content_lines[2][2]);
        $this->assertSame($pen1->getSmiles(), $content_lines[2][3]);
        $this->assertSame($pen2->getShortName(), $content_lines[3][2]);
        $this->assertSame($pen2->getSmiles(), $content_lines[3][3]);
    }

    public function testDownloadConditionDataAsGroupScientist(): void
    {
        $client = static::createClient();
        $user = $client->getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist1@example.com"]);
        $client->loginUser($user);

        $crawler = $client->request("GET", "/api/experiment/run/viewData/0");
        $response = $client->getResponse();
        $this->assertSame(404, $response->getStatusCode());

        // Test that an existing design returns 200.
        $design = $client->getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($design);
        $this->assertGreaterThan(0, $design->getRuns()->count());

        // To test more, get the other compounds, too
        $pen1 = $client->getContainer()->get(ChemicalRepository::class)->findOneBy(["shortName" => "Pen1"]);
        $pen2 = $client->getContainer()->get(ChemicalRepository::class)->findOneBy(["shortName" => "Pen2"]);
        $this->assertNotNull($pen1);
        $this->assertNotNull($pen2);

        $run = $design->getRuns()[0];

        $crawler = $client->request("GET", "/api/experiment/run/viewData/" . $run->getId());
        $response = $client->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("text/plain; charset=UTF-8", $response->headers->get('Content-Type'));

        // Check the results
        $content_lines = array_map(fn ($row) => explode("\t", $row), explode("\n", trim($response->getContent())));

        $this->assertCount(3, $content_lines);
        $this->assertCount(3, $content_lines[1]);

        $this->assertSame('150', $content_lines[1][2]);
        $this->assertSame('140', $content_lines[2][2]);
    }

    public function testNewExperimentRouteWorksForGroupAdminUser(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "flemming@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $crawler = $client->request("GET", "/experiment/design/new");
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form[name^='experimental_design']")->form();

        # Assert default values
        $currentValues = $form->getValues();
        $this->assertSame($user->getId()->toRfc4122(), $currentValues['experimental_design[_general][ownership][owner]']);
        $this->assertSame($user->getGroup()->getId()->toRfc4122(), $currentValues['experimental_design[_general][ownership][group]']);

        $form->setValues([
            "experimental_design[_general][number]" => "EXP002-test",
            "experimental_design[_general][shortName]" => "FP assay",
            "experimental_design[_general][longName]" => "FP assay",
        ]);
        $client->submit($form);

        // The return should be "ok".
        $this->assertResponseStatusCodeSame(200);

        // And submitting the form should *not* result in something created, because the form is a component.
        $experimentalDesign = self::getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP002-test"]);
        $this->assertNull($experimentalDesign);
    }

    public function testEditExperimentRouteWorksForGroupAdmin(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "flemming@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalDesign = self::getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($experimentalDesign);

        $crawler = $client->request("GET", "/experiment/design/edit/{$experimentalDesign->getId()->toRfc4122()}");
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form[name^='experimental_design']")->form();

        # Assert default values
        $currentValues = $form->getValues();

        $this->assertSame("EXP001", $currentValues['experimental_design[_general][number]']);
        $this->assertSame("Experiment Design 1", $currentValues['experimental_design[_general][shortName]']);
        $this->assertSame("Experiment Design 1", $currentValues['experimental_design[_general][longName]']);
        $this->assertSame($user->getId()->toRfc4122(), $currentValues['experimental_design[_general][ownership][owner]']);
        $this->assertSame($user->getGroup()->getId()->toRfc4122(), $currentValues['experimental_design[_general][ownership][group]']);

        $this->assertSame(ExperimentalFieldRole::Condition->value, $currentValues['experimental_design[_fields][fields][0][role]']);
        $this->assertSame(ExperimentalFieldVariableRoleEnum::Group->value, $currentValues['experimental_design[_fields][fields][0][variableRole]']);
        $this->assertSame('0', $currentValues['experimental_design[_fields][fields][0][weight]']);
        $this->assertSame('1', $currentValues['experimental_design[_fields][fields][0][exposed]']);
        $this->assertSame(FormRowTypeEnum::EntityType->value, $currentValues['experimental_design[_fields][fields][0][formRow][type]']);
        $this->assertSame("compound", $currentValues['experimental_design[_fields][fields][0][formRow][label]']);
        $this->assertSame("", $currentValues['experimental_design[_fields][fields][0][formRow][help]']);
        $this->assertSame(Chemical::class, $currentValues['experimental_design[_fields][fields][0][formRow][configuration][entityType]']);

        $this->assertSame(ExperimentalFieldRole::Condition->value, $currentValues['experimental_design[_fields][fields][1][role]']);
        $this->assertSame('time', $currentValues['experimental_design[_fields][fields][1][formRow][label]']);

        $this->assertSame(ExperimentalFieldRole::Datum->value, $currentValues['experimental_design[_fields][fields][2][role]']);
        $this->assertSame('MIC', $currentValues['experimental_design[_fields][fields][2][formRow][label]']);

        $client->submit($form);

        // The return should be "ok".
        $this->assertResponseStatusCodeSame(200);
    }

    public function testEditExperimentRouteFailsIfFromDifferentGroup(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist2@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalDesign = self::getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($experimentalDesign);

        $crawler = $client->request("GET", "/experiment/design/edit/{$experimentalDesign->getId()->toRfc4122()}");
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddRunToExperimentFailsIfFromDifferentGroup(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist2@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalDesign = self::getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($experimentalDesign);

        $crawler = $client->request("GET", "/experiment/design/newRun/{$experimentalDesign->getId()->toRfc4122()}");
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddRunToExperimentRouteWorksIfGroupAdmin(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "flemming@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalDesign = self::getContainer()->get(ExperimentalDesignRepository::class)->findOneBy(["number" => "EXP001"]);
        $this->assertNotNull($experimentalDesign);

        $crawler = $client->request("GET", "/experiment/design/newRun/{$experimentalDesign->getId()->toRfc4122()}");

        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->filter("form[name^='experimental_run']")->form();

        # Assert default values
        $currentValues = $form->getValues();
        $this->assertSame($user->getId()->toRfc4122(), $currentValues['experimental_run[_general][ownership][owner]']);
        $this->assertSame($user->getGroup()->getId()->toRfc4122(), $currentValues['experimental_run[_general][ownership][group]']);
    }

    public function testEditRunOfExperimentFailsIfFromDifferentGroup(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist2@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalRun = self::getContainer()->get(ExperimentalRunRepository::class)->findOneBy(["name" => "AF001 - Penicillin inhibition"]);
        $this->assertNotNull($experimentalRun);
        $this->assertNotNull($experimentalRun->getDesign());

        $crawler = $client->request("GET", "/experiment/design/editRun/{$experimentalRun->getId()->toRfc4122()}");

        $this->assertResponseStatusCodeSame(403);
    }

    public function testEditRunOfExperimentFailsIfGroupAdmin(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "flemming@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalRun = self::getContainer()->get(ExperimentalRunRepository::class)->findOneBy(["name" => "AF001 - Penicillin inhibition"]);
        $this->assertNotNull($experimentalRun);
        $this->assertNotNull($experimentalRun->getDesign());

        $crawler = $client->request("GET", "/experiment/design/editRun/{$experimentalRun->getId()->toRfc4122()}");

        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->filter("form[name^='experimental_run']")->form();

        // Assert default values
        $currentValues = $form->getValues();
        $this->assertSame("AF001 - Penicillin inhibition", $currentValues['experimental_run[_general][name]']);
        $this->assertSame($user->getId()->toRfc4122(), $currentValues['experimental_run[_general][ownership][owner]']);
        $this->assertSame($user->getGroup()->getId()->toRfc4122(), $currentValues['experimental_run[_general][ownership][group]']);
    }

    public function testAddDataToRunOfExperimentFailsIfFromDifferentGroup(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist2@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalRun = self::getContainer()->get(ExperimentalRunRepository::class)->findOneBy(["name" => "AF001 - Penicillin inhibition"]);
        $this->assertNotNull($experimentalRun);
        $this->assertNotNull($experimentalRun->getDesign());

        $crawler = $client->request("GET", "/experiment/design/addDataToRun/{$experimentalRun->getId()->toRfc4122()}");

        $this->assertResponseStatusCodeSame(403);
    }

    public function testaddDataToRunOfExperimentFailsIfGroupAdmin(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "flemming@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalRun = self::getContainer()->get(ExperimentalRunRepository::class)->findOneBy(["name" => "AF001 - Penicillin inhibition"]);
        $this->assertNotNull($experimentalRun);
        $this->assertNotNull($experimentalRun->getDesign());

        $crawler = $client->request("GET", "/experiment/design/addDataToRun/{$experimentalRun->getId()->toRfc4122()}");

        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->filter("form[name^='experimental_run']")->form();

        $pen1 = self::getContainer()->get(ChemicalRepository::class)->findOneBy(["shortName" => "Pen1"]);
        $pen2 = self::getContainer()->get(ChemicalRepository::class)->findOneBy(["shortName" => "Pen2"]);

        $this->assertNotNull($pen1);
        $this->assertNotNull($pen2);

        // Assert default values

        $expectedValues = [
            "_conditions" => [
                "conditions" => [
                    "0" => [
                        "name" => "Condition 1",
                        "data" => [
                            "_compound" => $pen1->getUlid()->toRfc4122(),
                            "_time" => "24",
                        ]
                    ],
                    "1" => [
                        "name" => "Condition 2",
                        "data" => [
                            "_compound" => $pen2->getUlid()->toRfc4122(),
                            "_time" => "24",
                        ]
                    ],
                ]
            ],
            "_dataSets" => [
                "dataSets" => [
                    "0" => [
                        "data" => [
                            "_MIC" => "150",
                        ]
                    ],
                    "1" => [
                        "data" => [
                            "_MIC" => "140",
                        ]
                    ],
                ]
            ]
        ];

        $this->assertNestedFormValues($form, "experimental_run_data", $expectedValues);
    }

    public function testIfViewRunFailsIfFromDifferentGroup(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "scientist2@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalRun = self::getContainer()->get(ExperimentalRunRepository::class)->findOneBy(["name" => "AF001 - Penicillin inhibition"]);
        $this->assertNotNull($experimentalRun);
        $this->assertNotNull($experimentalRun->getDesign());

        $crawler = $client->request("GET", "/experiment/run/{$experimentalRun->getId()->toRfc4122()}");

        $this->assertResponseStatusCodeSame(403);
    }

    public function testViewRunIfGroupAdmin(): void
    {

        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(["email" => "flemming@example.com"]);
        $this->assertNotNull($user);
        $client->loginUser($user);

        $experimentalRun = self::getContainer()->get(ExperimentalRunRepository::class)->findOneBy(["name" => "AF001 - Penicillin inhibition"]);
        $this->assertNotNull($experimentalRun);
        $this->assertNotNull($experimentalRun->getDesign());

        $crawler = $client->request("GET", "/experiment/run/{$experimentalRun->getId()->toRfc4122()}");

        $this->assertResponseStatusCodeSame(200);

        $this->assertStringContainsString($experimentalRun->getId()->toRfc4122(), $crawler->filter("#collapse-Overview-content")->html());

        $conditionContent = $crawler->filter("#collapse-Conditions-content")->html();
        foreach ($experimentalRun->getConditions() as $condition) {
            $this->assertStringContainsString($condition->getName(), $conditionContent);

            $compound = $condition->getDatum("_compound")->getReferenceUuid();
            $compound = self::getContainer()->get(ChemicalRepository::class)->find($compound);

            $this->assertStringContainsString($compound->getSmiles(), $conditionContent);
        }

        $dataContent = $crawler->filter("#collapse-Data-content")->html();
        foreach ($experimentalRun->getConditions() as $condition) {
            $this->assertStringContainsString($condition->getName(), $dataContent);
        }

        foreach ($experimentalRun->getDataSets() as $dataSet) {
            $micValue = (string)$dataSet->getDatum("_MIC")->getValue();
            $this->assertStringContainsString($micValue, $dataContent);
        }
    }
}
