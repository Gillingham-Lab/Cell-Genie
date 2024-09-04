<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Cell\CellCultureOtherEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use App\Entity\DoctrineEntity\Vendor;
use App\Entity\Epitope;
use App\Entity\Experiment;
use App\Entity\ExperimentType;
use App\Entity\Message;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Recipe;
use App\Entity\Tissue;
use App\Entity\Vocabulary;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
            ->setTitle('Gin')
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
            MenuItem::linkToCrud("Aliquotes", 'fas fa-vials', CellAliquot::class),
            MenuItem::linkToCrud("Cell Cultures", "fas", CellCulture::class),
            MenuItem::subMenu("Cell Culture Events", "far fa-calendar-alt")
                ->setSubItems([
                    MenuItem::linkToCrud("Tests", "fas fa-hospital-symbol", CellCultureTestEvent::class),
                    MenuItem::linkToCrud("Splitting", "fas fa-fill-drip", CellCultureSplittingEvent::class),
                    MenuItem::linkToCrud("Others", "fas", CellCultureOtherEvent::class),
                ]),

            MenuItem::linkToCrud("Organisms", "fa", Organism::class),
            MenuItem::linkToCrud("Tissue Types", "fas fa-kidneys", Tissue::class),
            MenuItem::linkToCrud("Morphologies", "fa", Morphology::class),

            MenuItem::section("Substances"),
            MenuItem::linkToCrud("Antibodies", "icon icon-antibody fw-icon", Antibody::class),
            MenuItem::linkToCrud("Chemicals", "icon icon-chemical", Chemical::class),
            MenuItem::linkToCrud("Oligos", "icon icon-oligomer", Oligo::class),
            MenuItem::linkToCrud("Proteins", "icon icon-protein", Protein::class),
            MenuItem::linkToCrud("Plasmids", "icon icon-plasmid", Plasmid::class),
            MenuItem::linkToCrud("Epitopes", "icon icon-epitope", Epitope::class),

            MenuItem::section("Experimental"),
            MenuItem::linkToCrud("Recipes", "fas fa-list-alt", Recipe::class),

            MenuItem::section("Inventory"),
            MenuItem::linkToCrud("Racks", 'fas fa-boxes', Rack::class),
            MenuItem::linkToCrud("Boxes", "fas fa-box", Box::class),
            MenuItem::linkToCrud("Vendors", "fas fa-store-alt", Vendor::class),

            MenuItem::section("Administration"),
            MenuItem::linkToCrud("Messages", 'fas fa-envelope', Message::class),
            MenuItem::linkToCrud("Vocabulary", 'fas fa-list', Vocabulary::class),
            MenuItem::linkToCrud("Users", 'fas fa-user', User::class)->setPermission("ROLE_ADMIN"),
            MenuItem::linkToCrud("Groups", 'fas fa-user', UserGroup::class)->setPermission("ROLE_ADMIN"),

            #MenuItem::linkToExitImpersonation('Stop impersonation', 'fa fa-exit'),
        ];
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
