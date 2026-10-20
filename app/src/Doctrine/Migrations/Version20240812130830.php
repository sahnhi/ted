<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240812130830 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_product_cms_technical_details (id INT AUTO_INCREMENT NOT NULL, product_cms_technical_infos_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_756B13B8A85B671E (product_cms_technical_infos_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_technical_details_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, header VARCHAR(255) DEFAULT NULL, text VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_79EFC3D72C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_technical_details_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_technical_infos (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_5D6435184584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_technical_infos_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_528564CF2C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_technical_infos_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_cms_technical_details ADD CONSTRAINT FK_756B13B8A85B671E FOREIGN KEY (product_cms_technical_infos_id) REFERENCES app_product_cms_technical_infos (id)');
        $this->addSql('ALTER TABLE app_product_cms_technical_details_translations ADD CONSTRAINT FK_79EFC3D72C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_technical_details (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_product_cms_technical_infos ADD CONSTRAINT FK_5D6435184584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_product_cms_technical_infos_translations ADD CONSTRAINT FK_528564CF2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_technical_infos (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_technical_details_translations DROP FOREIGN KEY FK_79EFC3D72C2AC5D3');
        $this->addSql('ALTER TABLE app_product_cms_technical_details DROP FOREIGN KEY FK_756B13B8A85B671E');
        $this->addSql('ALTER TABLE app_product_cms_technical_infos_translations DROP FOREIGN KEY FK_528564CF2C2AC5D3');
        $this->addSql('DROP TABLE app_product_cms_technical_details');
        $this->addSql('DROP TABLE app_product_cms_technical_details_translations');
        $this->addSql('DROP TABLE app_product_cms_technical_infos');
        $this->addSql('DROP TABLE app_product_cms_technical_infos_translations');
    }
}
