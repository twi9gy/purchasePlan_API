<?php

namespace App\Controller\Admin;

use App\Entity\PurchasePlan;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PurchasePlanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PurchasePlan::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('filename', 'Название файла'),
            AssociationField::new('purchase_user', 'Пользователь'),
            NumberField::new('freqDelivery', 'Частота проверки'),
            IntegerField::new('orderPoint', 'Точка заказа'),
            IntegerField::new('reserve', 'Резерв'),
            IntegerField::new('sizeOrder', 'Размер заказа'),
            NumberField::new('totalCost', 'Издержки'),
            IntegerField::new('serviceLevel', 'Уровень обслуживания'),
            NumberField::new('storageCost', 'Затраты хранения'),
            NumberField::new('shippingCost', 'Затраты доставки'),
            NumberField::new('productPrice', 'Стоимость продукции'),
            NumberField::new('timeShipping', 'Время доставки'),
            NumberField::new('delayedDeliveries', 'Время задержки достаки'),
            AssociationField::new('demandForecastFile', 'Отчет о прогнозировании спроса'),
            DateTimeField::new('createdAt', 'Дата создания')
        ];
    }
}
