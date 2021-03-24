<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Box;
use App\Entity\Cell;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Rack;
use App\Entity\Tissue;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
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
            MenuItem::linktoDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section("Cells"),
            MenuItem::linkToCrud("Cells", 'fa', Cell::class),
            MenuItem::linkToCrud("Organisms", "fa", Organism::class),
            MenuItem::linkToCrud("Tissue Types", "fa", Tissue::class),
            MenuItem::linkToCrud("Morphologies", "fa", Morphology::class),

            MenuItem::section("Storage"),
            MenuItem::linkToCrud("Racks", 'fa', Rack::class),
            MenuItem::linkToCrud("Boxes", "fa", Box::class),

            MenuItem::section("Users"),
            MenuItem::linkToCrud("Users", 'fa', User::class),

            #MenuItem::linkToExitImpersonation('Stop impersonation', 'fa fa-exit'),
            MenuItem::linkToLogout('Logout', 'fa fa-exit'),
        ];
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
