<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Resource;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Table\Column;
use App\Entity\Table\HtmlColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToggleColumn;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Table\UrlColumn;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Form\ResourceType;
use App\Repository\ResourceRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ResourceController extends AbstractController
{
    #[Route("/resources/all", name: "app_resources")]
    public function showResources(
        Security $security,
        ResourceRepository $resourceRepository,
    ): Response {
        $resources = $resourceRepository->findBy([], orderBy: ["category" => "ASC", "longName" => "ASC"]);

        return $this->render("generic/simple_table.html.twig", [
            "title" => "Resources",
            "icon" => "fas fa-fw fa-link",
            "toolbox" => new Toolbox([
                new AddTool(
                    path: $this->generateUrl("app_resources_new"),
                    enabled: $security->isGranted("new", "resource"),
                )
            ]),
            "table" => (new Table(
                data: $resources,
                columns: [
                    new ToolboxColumn("Toolbox", fn(Resource $subject) => new Toolbox([
                        new EditTool(
                            path: $this->generateUrl("app_resources_edit", ["resource" => $subject->getId()]),
                            enabled: $security->isGranted("edit", $subject),
                        ),
                    ])),
                    new Column("Category", fn(Resource $resource) => $resource->getCategory()),
                    new Column("Name", fn(Resource $resource) => $resource->getLongName()),
                    new UrlColumn("Url", fn(Resource $resource) => $resource->getUrl()),
                    new HtmlColumn("Comment", fn(Resource $resource) => $resource->getComment()),
                ],
                sortColumn: 1,
            )),
        ]);
    }

    #[Route("/resources/edit/{resource}", name: "app_resources_edit")]
    #[IsGranted("edit", "resource")]
    public function editResource(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        Resource $resource = null,
    ): Response {
        return $this->addOrEditResource($request, $security, $entityManager, $fileUploader, $resource, [
            "title" => "Edit Resource",
        ]);
    }

    #[Route("/resources/addNew", name: "app_resources_new")]
    public function addResource(
        Request $request,
        Security $security,
        #[CurrentUser]
        User $user,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isGranted("new", "resource")) {
            throw $this->createAccessDeniedException("You are not allowed to edit this entity.");
        }

        $resource = new Resource();
        $resource->setOwner($user);
        $resource->setGroup($user->getGroup());

        return $this->addOrEditResource($request, $security, $entityManager, $fileUploader, $resource, [
            "title" => "Add Resource",
        ]);
    }

    protected function addOrEditResource(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        Resource $resource,
        array $options,
    ): Response {
        $formOptions = [
            "save_button" => true,
            "category_autocomplete_url" => $this->generateUrl("api_resource_category_list"),
        ];

        $form = $this->createForm(ResourceType::class, $resource, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $fileUploader->upload($form);
            $fileUploader->updateFileSequence($resource);
            $entityManager->persist($resource);

            try {
                $entityManager->flush();
                $this->addFlash("success", "Resource was successfully persisted.");

                return $this->redirectToRoute("app_resources");
            } catch (Exception $e) {
                $this->addFlash("error", "An error occurred while persisting this entity to the database: {$e->getMessage()}");
            }
        }

        return $this->render("parts/forms/add_or_edit.html.twig", [
            "form" => $form,
            "returnTo" => $this->generateUrl("app_resources"),
            ... $options,
        ]);
    }

    #[Route("/api/resources/categories/list", name: "api_resource_category_list")]
    public function onCategoryAutocomplete(
        ResourceRepository $resourceRepository,
        #[MapQueryParameter]
        string $query = null,
    ): Response {
        $reply = [
            "results" => [

            ]
        ];

        $results = $resourceRepository->findCategories($query);

        foreach ($results as $result) {
            $reply["results"][] = ["value" => $result["category"], "text" => $result["category"]];
        }

        return new JsonResponse($reply);
    }
}