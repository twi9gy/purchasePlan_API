<?php

namespace App\Controller\Admin;

use App\Entity\PurchasePlan;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PurchasePlanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PurchasePlan::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
