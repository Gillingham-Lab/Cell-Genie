<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\Vendor;
use App\Entity\Table\Column;
use App\Entity\Table\HtmlColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToggleColumn;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Table\UrlColumn;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Form\VendorType;
use App\Repository\VendorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class VendorController extends AbstractController
{
    #[Route("/vendors/all", name: "app_vendors")]
    public function showVendors(
        Security $security,
        VendorRepository $vendorRepository,
    ): Response {
        $vendors = $vendorRepository->findAll();

        return $this->render("generic/simple_table.html.twig", [
            "title" => "Vendors",
            "icon" => "fas fa-fw fa-store-alt",
            "toolbox" => new Toolbox([
                new AddTool(
                    path: $this->generateUrl("app_vendors_new"),
                    enabled: $security->isGranted("new", "vendor"),
                ),
            ]),
            "table" => (new Table(
                data: $vendors,
                columns: [
                    new ToolboxColumn("Toolbox", fn(Vendor $vendor) => new Toolbox([
                        new EditTool(
                            path: $this->generateUrl("app_vendors_edit", ["vendor" => $vendor->getId()]),
                            enabled: $security->isGranted("edit", $vendor),
                        ),
                    ])),
                    new Column("Name", fn(Vendor $vendor) => $vendor->getName()),
                    new UrlColumn("Homepage", fn(Vendor $vendor) => $vendor->getHomepage()),
                    new HtmlColumn("Comment", fn(Vendor $vendor) => $vendor->getComment()),
                    new ToggleColumn("Free Shipping", fn(Vendor $vendor) => $vendor->getHasFreeShipping()),
                    new ToggleColumn("Discount", fn(Vendor $vendor) => $vendor->getHasDiscount()),
                    new ToggleColumn("Preferred", fn(Vendor $vendor) => $vendor->getIsPreferred()),
                ],
                sortColumn: 1,
            )),
        ]);
    }

    #[Route("/vendors/edit/{vendor}", name: "app_vendors_edit")]
    #[IsGranted("edit", "vendor")]
    public function editVendor(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        ?Vendor $vendor = null,
    ): Response {
        return $this->addOrEditVendor($request, $security, $entityManager, $vendor, [
            "title" => "Edit Vendor",
        ]);
    }

    #[Route("/vendors/addNew", name: "app_vendors_new")]
    public function addVendor(
        Request $request,
        Security $security,
        #[CurrentUser]
        User $user,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isGranted("new", "vendor")) {
            throw $this->createAccessDeniedException("You are not allowed to edit this entity.");
        }

        $vendor = new Vendor();
        $vendor->setOwner($user);
        $vendor->setGroup($user->getGroup());

        return $this->addOrEditVendor($request, $security, $entityManager, $vendor, [
            "title" => "Add Vendor",
        ]);
    }

    /**
     * @param Request $request
     * @param Security $security
     * @param EntityManagerInterface $entityManager
     * @param Vendor $vendor
     * @param array<string, mixed> $options
     * @return Response
     */
    protected function addOrEditVendor(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        Vendor $vendor,
        array $options,
    ): Response {
        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm(VendorType::class, $vendor, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $entityManager->persist($vendor);

            try {
                $entityManager->flush();
                $this->addFlash("success", "Vendor was successfully persisted.");
                return $this->redirectToRoute("app_vendors");
            } catch (Exception $e) {
                $this->addFlash("error", "An error occurred while persisting this entity to the database: {$e->getMessage()}");
            }
        }

        return $this->render("parts/forms/add_or_edit.html.twig", [
            "form" => $form,
            "returnTo" => $this->generateUrl("app_vendors"),
            ... $options,
        ]);
    }
}
