<?php
declare(strict_types=1);

namespace App\Tests\FunctionalTests\Controller;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Repository\Cell\CellAliquotRepository;
use App\Repository\Cell\CellGroupRepository;
use App\Repository\Cell\CellRepository;
use App\Repository\User\UserRepository;
use App\Security\Voter\Cell\CellGroupVoter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class CellControllerTest extends WebTestCase
{
    public function testCellBrowseRouteForAllCells(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $crawler = $client->request("GET", "/cells");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "Browse Cells");
        $this->assertSelectorTextContains("#collapseCellGroupsHeader h2", "Cell groups");
        $this->assertSelectorTextContains("#collapseCellsHeader h2", "Cells");

        $this->assertSelectorCount(4, "#collapseCellGroupsContent .card-body > .list-group > .list-group-item");
        $this->assertSelectorTextContains("#collapseCellsContent .card-body", "No cell group has been selected");

        $content = $crawler->html();
        $this->assertStringContainsString("HEK293", $content);
        $this->assertStringContainsString("HEK293T", $content);
        $this->assertStringContainsString("HCT 116", $content);
        $this->assertStringContainsString("HeLa", $content);
    }

    /**
     * @return array<array{string}>
     */
    public function cellGroupNames(): array
    {
        return [
            ["HCT 116"],
            ["HEK293"],
            ["HEK293T"],
            ["HeLa"],
            ["Empty"],
        ];
    }

    /**
     * @dataProvider cellGroupNames
     */
    public function testCellBrowseRouteForSpecificCellGroup(string $cellGroupName): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);
        $cellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByName($cellGroupName);

        $crawler = $client->request("GET", "/cells/group/view/{$cellGroup->getId()}");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "Browse Cells");
        $this->assertSelectorTextContains("#collapseCellGroupsHeader h2", "Cell groups");
        $this->assertSelectorTextContains("#collapseCellsHeader h2", "Cells");

        $this->assertSelectorCount(4, "#collapseCellGroupsContent .card-body > .list-group > .list-group-item");

        $this->assertSelectorTextContains("#collapseCellsContent .card-body h3", $cellGroupName);
    }

    public function testCellSearchRouteWorks(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $crawler = $client->request("GET", "/cells/all");
        $this->assertResponseIsSuccessful();
    }

    public function testCellGroupRemovalRouteIfGroupAdmin(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com");
        $client->loginUser($user);

        $cellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByName("Empty");

        $crawler = $client->request("GET", "/cells/group/remove/{$cellGroup->getId()}");
        $this->assertResponseRedirects("/cells");
    }

    public function testCellGroupRemovalRouteIfNotGroupAdmin(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $cellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByName("Empty");

        $crawler = $client->request("GET", "/cells/group/remove/{$cellGroup->getId()}");
        $this->assertResponseStatusCodeSame(302);
    }

    public function testCellGroupRemovalRouteIfNotGroupAdminAndGroupIsNotEmpty(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $cellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByNumber("CVCL_0291");

        $crawler = $client->request("GET", "/cells/group/remove/{$cellGroup->getId()}");
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAccessOfRouteForAddingCellGroupsIsSuccessful(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com");
        $client->loginUser($user);

        $crawler = $client->request("GET", "/cells/group/add");
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form")->form();
        $form->setValues([
            "cell_group[__generalGroup][number]" => "CV001",
            "cell_group[__generalGroup][name]" => "New Cell Line",
        ]);
        $client->submit($form);

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);

        // Follow the redirect
        $client->followRedirect();

        // Run assertions for cell group (see above)
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "Browse Cells");
        $this->assertSelectorTextContains("#collapseCellGroupsHeader h2", "Cell groups");
        $this->assertSelectorTextContains("#collapseCellsHeader h2", "Cells");

        // We should fine now 1 more
        $this->assertSelectorCount(5, "#collapseCellGroupsContent .card-body > .list-group > .list-group-item");

        $this->assertSelectorTextContains("#collapseCellsContent .card-body h3", "New Cell Line");
    }

    public function testAccessOfRouteForAddingCellGroupsAndParentCellGroupInformationGetsProperlyAdded(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com");
        $client->loginUser($user);

        /** @var CellGroup $parentCellGroup */
        $parentCellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByName("HEK293T");

        $crawler = $client->request("GET", "/cells/group/add");
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form")->form();
        $form->setValues([
            "cell_group[__generalGroup][number]" => "CV001",
            "cell_group[__generalGroup][name]" => "New Cell Line",
            "cell_group[__generalGroup][parent]" => $parentCellGroup->getId(),
        ]);
        $client->submit($form);

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);

        // We stop here, and instead retrieve the parent and the new cell line from the database
        // Refetching the parent seems to be necessary
        /** @var CellGroup $parentCellGroup */
        $parentCellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByName("HEK293T");
        /** @var CellGroup $newCellGroup */
        $newCellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByName("New Cell Line");

        $this->assertSame($parentCellGroup->getMorphology(), $newCellGroup->getMorphology());
        $this->assertSame($parentCellGroup->getOrganism(), $newCellGroup->getOrganism());
        $this->assertSame($parentCellGroup->getTissue(), $newCellGroup->getTissue());
        $this->assertSame($parentCellGroup->getAge(), $newCellGroup->getAge());
        $this->assertSame($parentCellGroup->getEthnicity(), $newCellGroup->getEthnicity());
        $this->assertSame($parentCellGroup->getDisease(), $newCellGroup->getDisease());
        $this->assertSame($parentCellGroup, $newCellGroup->getParent());
    }

    public function testAccessOfRouteForEditingCellGroupsAndParentCellGroupInformationGetsProperlyAdded(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("flemming@example.com");
        $client->loginUser($user);

        /** @var CellGroupRepository $cellGroupRepository */
        $cellGroupRepository = self::getContainer()->get(CellGroupRepository::class);

        /** @var CellGroup $cellGroup */
        $cellGroup = $cellGroupRepository->findOneBy(["name" => "HeLa"]);

        $this->assertNotNull($cellGroup);

        $crawler = $client->request("GET", "/cells/group/edit/{$cellGroup->getId()}");
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form")->form();
        $form->setValues([
            "cell_group[__generalGroup][number]" => "NEWNUMBER_3",
        ]);
        $client->submit($form);

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);

        // Refetch
        /** @var CellGroup $cellGroup */
        $cellGroup = $cellGroupRepository->findOneBy(["name" => "HeLa"]);

        $this->assertSame("NEWNUMBER_3", $cellGroup->getNumber());
    }

    public function testViewCellByNumberRouteWithoutAliquot(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        /** @var Cell $cell */
        $cell = self::getContainer()->get(CellRepository::class)->findOneByName("HEK293");

        $crawler = $client->request("GET", "/cells/view/no/{$cell->getCellNumber()}");
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains("h1", "CL002 - HEK293");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-header h2", "Cell metadata");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-content section h3", "Aliquots");
    }

    public function testAddCellRoute(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $parentCellGroup = self::getContainer()->get(CellGroupRepository::class)->findOneByName("HEK293");
        $this->assertNotNull($parentCellGroup);

        $crawler = $client->request("GET", "/cells/add");
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter("form")->form();
        $form->setValues([
            "cell[general][cellNumber]" => "CV042",
            "cell[general][name]" => "New HEK Cells",
            "cell[general][cellGroup]" => $parentCellGroup->getId(),
        ]);
        $client->submit($form);

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects("/cells/view/no/CV042");

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "CV042 - New HEK Cells");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-header h2", "Cell metadata");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-content section h3", "Aliquots");
    }

    public function testEditCellRoute(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $cell = self::getContainer()->get(CellRepository::class)->findOneByName("HEK293");
        $crawler = $client->request("GET", "/cells/edit/{$cell->getCellNumber()}");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "Edit");
        $this->assertSelectorTextContains("h2", "CL002 | HEK293");

        $form = $crawler->filter("form")->form();
        $form->setValues([
            "cell[general][cellNumber]" => "CV666",
        ]);
        $client->submit($form);

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects("/cells/view/no/CV666");

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "CV666 - HEK293");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-header h2", "Cell metadata");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-content section h3", "Aliquots");
    }

    public function testAddNewCellAliquot(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $cell = self::getContainer()->get(CellRepository::class)->findOneByName("HCT 116");
        $crawler = $client->request("GET", "/cells/addAliquot/{$cell->getCellNumber()}");

        $form = $crawler->filter("form")->form();
        $form->setValues([
            "cell_aliquot[_general][aliquotName]" => "AL001",
            "cell_aliquot[_general][aliquoted_by]" => $user->getId()->toRfc4122(),
        ]);
        $client->submit($form);

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringStartsWith("/cells/view/no/CL001/", $client->getResponse()->headers->get("Location"));

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains("h1", "CL001 - HCT 116");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-header h2", "Cell metadata");
        $this->assertSelectorTextContains("#collapse-Cellmetadata-content section h3", "Aliquots");

        // Inside of aliquots is not testable as it is a live component with lazy loading.
    }

    public function testEditCellAliquot(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        /** @var Cell $cell */
        $cell = self::getContainer()->get(CellRepository::class)->findOneByName("HEK293");
        $cellAliquot = $cell->getCellAliquots()->first();

        $crawler = $client->request("GET", "/cells/editAliquot/{$cell->getCellNumber()}/{$cellAliquot->getId()}");
        $form = $crawler->filter("form")->form();
        $form->setValues([
            "cell_aliquot[_general][aliquotName]" => "AL013",
        ]);
        $client->submit($form);

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects("/cells/view/no/{$cell->getCellNumber()}/{$cellAliquot->getId()}");

        # Refetch aliquot
        $refetchedCellAliquot = self::getContainer()->get(CellAliquotRepository::class)->findOneByAliquotName("AL013");
        $this->assertSame($cellAliquot->getId()->toRfc4122(), $refetchedCellAliquot->getId()->toRfc4122());
    }

    public function testConsumeAliquot(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        /** @var CellAliquot $aliquot */
        $aliquot = self::getContainer()->get(CellAliquotRepository::class)->findOneByAliquotName("HEK1");
        $this->assertTrue($aliquot->getCell()->isAliquotConsumptionCreatesCulture());
        $crawler = $client->request("GET", "/cells/consume/{$aliquot->getId()}");

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);
        $this->assertStringStartsWith("/cells/cultures/edit", $client->getResponse()->headers->get("Location"));
        // Refetch aliquot to check counter
        $aliquotRefetched = self::getContainer()->get(CellAliquotRepository::class)->findOneByAliquotName("HEK1");
        $this->assertSame(17, $aliquotRefetched->getVials());
    }

    public function trashAliquot(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        /** @var CellAliquot $aliquot */
        $aliquot = self::getContainer()->get(CellAliquotRepository::class)->findOneByAliquotName("HEK1");
        $cell = $aliquot->getCell();
        $this->assertInstanceOf(CellAliquot::class, $aliquot);

        $crawler = $client->request("GET", "/cells/aliquot/trash/{$aliquot->getId()}");
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects("/cells/view/no/{$cell->getCellNumber()}");

        # Refetch aliquot to check if it disappeared from the database
        $aliquot = self::getContainer()->get(CellAliquotRepository::class)->findOneByAliquotName("HEK1");
        $this->assertNull($aliquot);
    }

    public function testCellCulturesAsAnonymousUserForwardsToLogin(): void
    {
        $client = self::createClient();

        $crawler = $client->request("GET", "/cells/cultures");
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects("/login");
    }

    public function testCellCultures(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $crawler = $client->request("GET", "/cells/cultures");
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAccessingCellCulturesAfterConsumingAliquot(): void
    {
        $client = self::createClient();
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail("scientist1@example.com");
        $client->loginUser($user);

        $aliquot = self::getContainer()->get(CellAliquotRepository::class)->findOneByAliquotName("HEK1");
        $crawler = $client->request("GET", "/cells/consume/{$aliquot->getId()}");

        // Make sure we have a redirect
        $this->assertResponseStatusCodeSame(302);
        $redirectTo = $client->getResponse()->headers->get("Location");
        $this->assertStringStartsWith("/cells/cultures/edit", $redirectTo);
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $this->assertStringContainsString("HEK293", $client->getCrawler()->filter("h1")->html());

        $form = $client->getCrawler()->filter("form")->form();
        $form->setValues([
            "cell_culture[number]" => "CCL001",
        ]);
        $client->submit($form);
        $this->assertResponseStatusCodeSame(302);

        $crawler = $client->followRedirect();
        $this->assertResponseStatusCodeSame(200);

        $crawler = $client->request("GET", "/cells/cultures");

        $this->assertStringContainsString(htmlentities('"name":"CCL001 (CL002 | HEK293)"'), $client->getResponse()->getContent());

        $this->assertResponseStatusCodeSame(200);
    }
}
