<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Crud\ItemCrudController;
use App\Controller\Admin\Crud\ShopRequestCrudController;
use App\Controller\Admin\Crud\TournamentCrudController;
use App\Controller\Admin\Crud\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Playmate');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fas fa-users');
        yield MenuItem::linkTo(ItemCrudController::class, 'Articles', 'fas fa-shopping-cart');
        yield MenuItem::linkTo(TournamentCrudController::class, 'Tournois', 'fas fa-trophy');
        yield MenuItem::linkTo(ShopRequestCrudController::class, 'Boutiques', 'fas fa-store');

        yield MenuItem::section();
        yield MenuItem::linkToRoute('Retour au site', 'fas fa-arrow-left', 'app_home');
    }
}
