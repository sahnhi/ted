<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240723070650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Shipping Method Category Tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_shipping_method_category (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, enabled TINYINT(1) DEFAULT \'0\' NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_E04E3C6677153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_shipping_method_category_service (id INT AUTO_INCREMENT NOT NULL, product_variant_id INT DEFAULT NULL, shipping_method_category_id INT NOT NULL, code VARCHAR(255) NOT NULL, enabled TINYINT(1) DEFAULT \'0\' NOT NULL, included TINYINT(1) DEFAULT \'0\' NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_F114B176A80EF684 (product_variant_id), INDEX IDX_F114B1766D66C5A6 (shipping_method_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_shipping_method_category_service_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_106E848D2C2AC5D3 (translatable_id), UNIQUE INDEX app_shipping_method_category_service_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_shipping_method_category_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_B58E515A2C2AC5D3 (translatable_id), UNIQUE INDEX app_shipping_method_category_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_shipping_method_category_translation ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_shipping_method_category_service ADD CONSTRAINT FK_F114B176A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE app_shipping_method_category_service ADD CONSTRAINT FK_F114B1766D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id)');
        $this->addSql('ALTER TABLE app_shipping_method_category_service_translation ADD CONSTRAINT FK_106E848D2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_shipping_method_category_service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_shipping_method_category_translation ADD CONSTRAINT FK_B58E515A2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_shipping_method_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD shipping_method_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE116D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id)');
        $this->addSql('CREATE INDEX IDX_5FB0EE116D66C5A6 ON sylius_shipping_method (shipping_method_category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_shipping_method_category_service DROP FOREIGN KEY FK_F114B1766D66C5A6');
        $this->addSql('ALTER TABLE app_shipping_method_category_translation DROP FOREIGN KEY FK_B58E515A2C2AC5D3');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE116D66C5A6');
        $this->addSql('ALTER TABLE app_shipping_method_category_service_translation DROP FOREIGN KEY FK_106E848D2C2AC5D3');
        $this->addSql('DROP TABLE app_shipping_method_category');
        $this->addSql('DROP TABLE app_shipping_method_category_service');
        $this->addSql('DROP TABLE app_shipping_method_category_service_translation');
        $this->addSql('DROP TABLE app_shipping_method_category_translation');
        $this->addSql('DROP INDEX IDX_5FB0EE116D66C5A6 ON sylius_shipping_method');
        $this->addSql('ALTER TABLE sylius_shipping_method DROP COLUMN shipping_method_category_id');
    }
}
