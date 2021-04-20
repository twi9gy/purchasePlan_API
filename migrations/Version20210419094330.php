<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210419094330 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase_plan ADD service_level INT NOT NULL');
        $this->addSql('ALTER TABLE purchase_plan ADD storage_cost DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE purchase_plan ADD product_price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE purchase_plan ADD shipping_cost DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE purchase_plan ADD time_shipping INT NOT NULL');
        $this->addSql('ALTER TABLE purchase_plan ADD delayed_deliveries INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE purchase_plan DROP service_level');
        $this->addSql('ALTER TABLE purchase_plan DROP storage_cost');
        $this->addSql('ALTER TABLE purchase_plan DROP product_price');
        $this->addSql('ALTER TABLE purchase_plan DROP shipping_cost');
        $this->addSql('ALTER TABLE purchase_plan DROP time_shipping');
        $this->addSql('ALTER TABLE purchase_plan DROP delayed_deliveries');
    }
}
