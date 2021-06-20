<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526092553 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE demand_forecast_file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE purchase_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE purchase_plan_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sales_file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, purchase_user_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64C19C1BCB419EC ON category (purchase_user_id)');
        $this->addSql('CREATE TABLE demand_forecast_file (id INT NOT NULL, category_id INT DEFAULT NULL, purchase_user_id INT NOT NULL, sales_file_id INT NOT NULL, filename VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, accuracy DOUBLE PRECISION DEFAULT NULL, analysis_field VARCHAR(255) DEFAULT NULL, forecast_period INT NOT NULL, analysis_method SMALLINT NOT NULL, rmse DOUBLE PRECISION NOT NULL, interval VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D53D3B112469DE2 ON demand_forecast_file (category_id)');
        $this->addSql('CREATE INDEX IDX_3D53D3B1BCB419EC ON demand_forecast_file (purchase_user_id)');
        $this->addSql('CREATE INDEX IDX_3D53D3B19D32050C ON demand_forecast_file (sales_file_id)');
        $this->addSql('CREATE TABLE purchase_plan (id INT NOT NULL, purchase_user_id INT NOT NULL, demand_forecast_file_id INT NOT NULL, filename VARCHAR(255) NOT NULL, freq_delivery INT NOT NULL, order_point INT NOT NULL, reserve INT NOT NULL, size_order INT NOT NULL, total_cost DOUBLE PRECISION NOT NULL, service_level INT NOT NULL, storage_cost DOUBLE PRECISION NOT NULL, product_price DOUBLE PRECISION NOT NULL, shipping_cost DOUBLE PRECISION NOT NULL, time_shipping INT NOT NULL, delayed_deliveries INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ADE9931EBCB419EC ON purchase_plan (purchase_user_id)');
        $this->addSql('CREATE INDEX IDX_ADE9931EFAAAC7B8 ON purchase_plan (demand_forecast_file_id)');
        $this->addSql('CREATE TABLE purchase_plan_user (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D09E870E7927C74 ON purchase_plan_user (email)');
        $this->addSql('CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE sales_file (id INT NOT NULL, category_id INT NOT NULL, purchase_user_id INT NOT NULL, filename VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, separator VARCHAR(10) DEFAULT NULL, created_by_category BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7108DBC512469DE2 ON sales_file (category_id)');
        $this->addSql('CREATE INDEX IDX_7108DBC5BCB419EC ON sales_file (purchase_user_id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1BCB419EC FOREIGN KEY (purchase_user_id) REFERENCES purchase_plan_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE demand_forecast_file ADD CONSTRAINT FK_3D53D3B112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE demand_forecast_file ADD CONSTRAINT FK_3D53D3B1BCB419EC FOREIGN KEY (purchase_user_id) REFERENCES purchase_plan_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE demand_forecast_file ADD CONSTRAINT FK_3D53D3B19D32050C FOREIGN KEY (sales_file_id) REFERENCES sales_file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE purchase_plan ADD CONSTRAINT FK_ADE9931EBCB419EC FOREIGN KEY (purchase_user_id) REFERENCES purchase_plan_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE purchase_plan ADD CONSTRAINT FK_ADE9931EFAAAC7B8 FOREIGN KEY (demand_forecast_file_id) REFERENCES demand_forecast_file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sales_file ADD CONSTRAINT FK_7108DBC512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sales_file ADD CONSTRAINT FK_7108DBC5BCB419EC FOREIGN KEY (purchase_user_id) REFERENCES purchase_plan_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE demand_forecast_file DROP CONSTRAINT FK_3D53D3B112469DE2');
        $this->addSql('ALTER TABLE sales_file DROP CONSTRAINT FK_7108DBC512469DE2');
        $this->addSql('ALTER TABLE purchase_plan DROP CONSTRAINT FK_ADE9931EFAAAC7B8');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1BCB419EC');
        $this->addSql('ALTER TABLE demand_forecast_file DROP CONSTRAINT FK_3D53D3B1BCB419EC');
        $this->addSql('ALTER TABLE purchase_plan DROP CONSTRAINT FK_ADE9931EBCB419EC');
        $this->addSql('ALTER TABLE sales_file DROP CONSTRAINT FK_7108DBC5BCB419EC');
        $this->addSql('ALTER TABLE demand_forecast_file DROP CONSTRAINT FK_3D53D3B19D32050C');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE demand_forecast_file_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE purchase_plan_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE purchase_plan_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sales_file_id_seq CASCADE');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE demand_forecast_file');
        $this->addSql('DROP TABLE purchase_plan');
        $this->addSql('DROP TABLE purchase_plan_user');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE sales_file');
    }
}
