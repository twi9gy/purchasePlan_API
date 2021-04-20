<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\DemandForecastFile;
use App\Entity\PurchasePlan;
use App\Entity\SalesFile;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();
        $url = $routeBuilder->setController(UserCrudController::class)->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('PurchasePlan API');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Административная панель', 'fa fa-home');
        yield MenuItem::linkToCrud('Пользователи', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Категории', 'fas fa-th', Category::class);
        yield MenuItem::linkToCrud('Файлы с продажами', 'fa fa-file-excel-o', SalesFile::class);
        yield MenuItem::linkToCrud('Отчеты о прогнозировании спроса', 'fa fa-rub', DemandForecastFile::class);
        yield MenuItem::linkToCrud('Планы закупок', 'fa fa-area-chart', PurchasePlan::class);
    }
}
