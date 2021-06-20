<?php

namespace App\Controller\Admin;

use App\Entity\DemandForecastFile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class DemandForecastFileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DemandForecastFile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
	    TextField::new('filename', 'Название файла'),
            DateTimeField::new('createdAt', 'Дата создания'),
            AssociationField::new('salesFile', 'Файл продаж'),
            AssociationField::new('category', 'Категория'),
            PercentField::new('accuracy', 'Точность прогноза'),
            NumberField::new('rmse'),
            TextField::new('analysisField', 'Поле анализа'),
            TextField::new('AnalysisMethodFormatString', 'Метод анализа'),
            NumberField::new('forecastPeriod', 'Период прогнозирования'),
            TextField::new('interval', 'Интервал прогнозирования'),
            AssociationField::new('purchasePlans', 'Планы закупок'),
        ];
    }
}
