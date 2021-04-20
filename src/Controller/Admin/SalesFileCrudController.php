<?php

namespace App\Controller\Admin;

use App\Entity\SalesFile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SalesFileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SalesFile::class;
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
