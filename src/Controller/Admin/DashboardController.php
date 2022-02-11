<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Antibody;
use App\Entity\AntibodyHost;
use App\Entity\Box;
use App\Entity\Cell;
use App\Entity\CellAliquote;
use App\Entity\Chemical;
use App\Entity\CultureFlask;
use App\Entity\Experiment;
use App\Entity\ExperimentType;
use App\Entity\Message;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Protein;
use App\Entity\Rack;
use App\Entity\Recipe;
use App\Entity\Tissue;
use App\Entity\User;
use App\Entity\Vendor;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route("/admin", name: "admin")]
    public function index(): Response
    {
        return $this->render('admin_dashboard/dashboard.html.twig');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile("icomoon/style.css");
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Cell Genie')
            ->renderContentMaximized()
        ;
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToRoute("Back to the App", "fas fa-home", "app_homepage"),
            MenuItem::linkToLogout('Logout', 'fas fa-sign-out-alt'),

            MenuItem::section("Cells"),
            MenuItem::linkToCrud("Cells", 'fas fa-disease', Cell::class),
            MenuItem::linkToCrud("Aliquotes", 'fas fa-vials', CellAliquote::class),
            MenuItem::linkToCrud("Organisms", "fa", Organism::class),
            MenuItem::linkToCrud("Tissue Types", "fas fa-kidneys", Tissue::class),
            MenuItem::linkToCrud("Morphologies", "fa", Morphology::class),

            MenuItem::section("Experimental"),
            MenuItem::linkToCrud("Experiment types", 'fas fa-flask', ExperimentType::class),
            MenuItem::linkToCrud("Experiments", 'fas', Experiment::class),
            MenuItem::linkToCrud("Protein targets", "icon icon-protein", Protein::class),
            MenuItem::linkToCrud("Antibodies", "icon icon-antibody", Antibody::class),
            MenuItem::linkToCrud("Antibody hosts", "fas fa-horse", AntibodyHost::class),
            MenuItem::linkToCrud("Chemicals", "fas fa-tablets", Chemical::class),
            MenuItem::linkToCrud("Recipes", "fas fa-list-alt", Recipe::class),

            MenuItem::section("Inventory"),
            MenuItem::linkToCrud("Racks", 'fas fa-boxes', Rack::class),
            MenuItem::linkToCrud("Boxes", "fas fa-box", Box::class),
            MenuItem::linkToCrud("Vendors", "fas fa-store-alt", Vendor::class),
            MenuItem::linkToCrud("Culture flasks", 'fas', CultureFlask::class),

            MenuItem::section("Users"),
            MenuItem::linkToCrud("Messages", 'fas fa-envelope', Message::class),
            MenuItem::linkToCrud("Users", 'fas fa-user', User::class)->setPermission("ROLE_ADMIN"),

            #MenuItem::linkToExitImpersonation('Stop impersonation', 'fa fa-exit'),
        ];
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
