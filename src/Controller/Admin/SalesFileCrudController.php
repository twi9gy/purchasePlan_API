<?php

namespace App\Controller\Admin;

use App\Entity\SalesFile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SalesFileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SalesFile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('filename', 'Название файла'),
            AssociationField::new('purchase_user', 'Пользователь'),
            DateTimeField::new('createdAt', 'Дата создания'),
            AssociationField::new('category', 'Категория'),
            AssociationField::new('demandForecastFiles', 'Отчеты о прогнозировании спроса'),
            TextField::new('separator', 'Разделитель столбцов'),
            BooleanField::new('createdByCategory', 'Создан по категории')
        ];
    }
}
