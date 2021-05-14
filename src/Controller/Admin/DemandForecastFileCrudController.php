<?php

namespace App\Controller\Admin;

use App\Entity\DemandForecastFile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
            IdField::new('id'),
            TextField::new('filename'),
            DateTimeField::new('createdAt'),
            AssociationField::new('salesFile'),
            AssociationField::new('category'),
            PercentField::new('accuracy'),
            TextField::new('analysisField'),
            TextField::new('AnalysisMethodFormatString'),
            NumberField::new('forecastPeriod'),
            AssociationField::new('purchasePlans'),
            NumberField::new('rmse'),
            TextField::new('interval')
        ];
    }
}
