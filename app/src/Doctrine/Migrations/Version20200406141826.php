<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200406141826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE erp_exceptional_schedule (id INT AUTO_INCREMENT NOT NULL, shipping_method_id INT DEFAULT NULL, warehouse_id INT DEFAULT NULL, service_rules_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, INDEX IDX_CEAF4D1C5F7D6850 (shipping_method_id), INDEX IDX_CEAF4D1C5080ECDE (warehouse_id), INDEX IDX_CEAF4D1C21FC979 (service_rules_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_shipping_category_method (shipping_category_id INT NOT NULL, shipping_method_id INT NOT NULL, INDEX IDX_695A6F0A9E2D1A41 (shipping_category_id), INDEX IDX_695A6F0A5F7D6850 (shipping_method_id), PRIMARY KEY(shipping_category_id, shipping_method_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE erp_service_rules (id INT AUTO_INCREMENT NOT NULL, shipping_method_id INT DEFAULT NULL, warehouse_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, shipping_category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, priority INT NOT NULL, shipping_plan_file VARCHAR(255) DEFAULT NULL, delivery_slot TIME DEFAULT NULL, INDEX IDX_4E3D4BDB5F7D6850 (shipping_method_id), INDEX IDX_4E3D4BDB5080ECDE (warehouse_id), INDEX IDX_4E3D4BDB9F2C3FAB (zone_id), INDEX IDX_4E3D4BDB9E2D1A41 (shipping_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE erp_exceptional_schedule ADD CONSTRAINT FK_CEAF4D1C5F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES sylius_shipping_method (id)');
        $this->addSql('ALTER TABLE erp_exceptional_schedule ADD CONSTRAINT FK_CEAF4D1C5080ECDE FOREIGN KEY (warehouse_id) REFERENCES erp_warehouse (id)');
        $this->addSql('ALTER TABLE erp_exceptional_schedule ADD CONSTRAINT FK_CEAF4D1C21FC979 FOREIGN KEY (service_rules_id) REFERENCES erp_service_rules (id)');
        $this->addSql('ALTER TABLE sylius_shipping_category_method ADD CONSTRAINT FK_695A6F0A9E2D1A41 FOREIGN KEY (shipping_category_id) REFERENCES sylius_shipping_category (id)');
        $this->addSql('ALTER TABLE sylius_shipping_category_method ADD CONSTRAINT FK_695A6F0A5F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES sylius_shipping_method (id)');
        $this->addSql('ALTER TABLE erp_service_rules ADD CONSTRAINT FK_4E3D4BDB5F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES sylius_shipping_method (id)');
        $this->addSql('ALTER TABLE erp_service_rules ADD CONSTRAINT FK_4E3D4BDB5080ECDE FOREIGN KEY (warehouse_id) REFERENCES erp_warehouse (id)');
        $this->addSql('ALTER TABLE erp_service_rules ADD CONSTRAINT FK_4E3D4BDB9F2C3FAB FOREIGN KEY (zone_id) REFERENCES sylius_zone (id)');
        $this->addSql('ALTER TABLE erp_service_rules ADD CONSTRAINT FK_4E3D4BDB9E2D1A41 FOREIGN KEY (shipping_category_id) REFERENCES sylius_shipping_category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE erp_exceptional_schedule DROP FOREIGN KEY FK_CEAF4D1C21FC979');
        $this->addSql('DROP TABLE erp_exceptional_schedule');
        $this->addSql('DROP TABLE sylius_shipping_category_method');
        $this->addSql('DROP TABLE erp_service_rules');
    }
}
