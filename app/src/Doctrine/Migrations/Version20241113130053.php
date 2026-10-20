<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241113130053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the product CMS sizing guide entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_product_cms_sizing_guide (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_sizing_guide_item (id INT AUTO_INCREMENT NOT NULL, product_cms_sizing_guide_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_94CC81CC29614F (product_cms_sizing_guide_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_sizing_guide_item_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, image_path VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_832000C22C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_sizing_guide_item_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_sizing_guide_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) NOT NULL, sub_title VARCHAR(255) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_F866F18F2C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_sizing_guide_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_cms_sizing_guide_item ADD CONSTRAINT FK_94CC81CC29614F FOREIGN KEY (product_cms_sizing_guide_id) REFERENCES app_product_cms_sizing_guide (id)');
        $this->addSql('ALTER TABLE app_product_cms_sizing_guide_item_translations ADD CONSTRAINT FK_832000C22C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_sizing_guide_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_product_cms_sizing_guide_translations ADD CONSTRAINT FK_F866F18F2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_sizing_guide (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B74B6CAA5D4');
        $this->addSql('DROP INDEX UNIQ_677B9B74B6CAA5D4 ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product CHANGE product_cms_focus_id product_cms_sizing_guide_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B7429614F FOREIGN KEY (product_cms_sizing_guide_id) REFERENCES app_product_cms_sizing_guide (id)');
        $this->addSql('CREATE INDEX IDX_677B9B7429614F ON sylius_product (product_cms_sizing_guide_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BE4E6BAA77153098 ON app_product_cms_sizing_guide (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_BE4E6BAA77153098 ON app_product_cms_sizing_guide');
        $this->addSql('ALTER TABLE app_product_cms_sizing_guide_item DROP FOREIGN KEY FK_94CC81CC29614F');
        $this->addSql('ALTER TABLE app_product_cms_sizing_guide_translations DROP FOREIGN KEY FK_F866F18F2C2AC5D3');
        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B7429614F');
        $this->addSql('ALTER TABLE app_product_cms_sizing_guide_item_translations DROP FOREIGN KEY FK_832000C22C2AC5D3');
        $this->addSql('DROP TABLE app_product_cms_sizing_guide');
        $this->addSql('DROP TABLE app_product_cms_sizing_guide_item');
        $this->addSql('DROP TABLE app_product_cms_sizing_guide_item_translations');
        $this->addSql('DROP TABLE app_product_cms_sizing_guide_translations');
        $this->addSql('DROP INDEX IDX_677B9B7429614F ON sylius_product');
        $this->addSql('DROP INDEX IDX_677B9B748B8E8428 ON sylius_product');
        $this->addSql('DROP INDEX IDX_677B9B7443625D9F ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product CHANGE product_cms_sizing_guide_id product_cms_focus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B74B6CAA5D4 FOREIGN KEY (product_cms_focus_id) REFERENCES app_product_cms_focus (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B74B6CAA5D4 ON sylius_product (product_cms_focus_id)');
    }
}
