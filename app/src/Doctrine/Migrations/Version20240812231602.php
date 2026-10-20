<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240812231602 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_product_cms_focus (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_focus_details (id INT AUTO_INCREMENT NOT NULL, product_cms_focus_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_4E4A749FB6CAA5D4 (product_cms_focus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_focus_details_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, term VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_3C2922252C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_focus_details_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_focus_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_6FD539042C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_focus_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_cms_focus_details ADD CONSTRAINT FK_4E4A749FB6CAA5D4 FOREIGN KEY (product_cms_focus_id) REFERENCES app_product_cms_focus (id)');
        $this->addSql('ALTER TABLE app_product_cms_focus_details_translations ADD CONSTRAINT FK_3C2922252C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_focus_details (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_product_cms_focus_translations ADD CONSTRAINT FK_6FD539042C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_focus (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product ADD product_cms_focus_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B74B6CAA5D4 FOREIGN KEY (product_cms_focus_id) REFERENCES app_product_cms_focus (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B74B6CAA5D4 ON sylius_product (product_cms_focus_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_focus_details DROP FOREIGN KEY FK_4E4A749FB6CAA5D4');
        $this->addSql('ALTER TABLE app_product_cms_focus_translations DROP FOREIGN KEY FK_6FD539042C2AC5D3');
        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B74B6CAA5D4');
        $this->addSql('ALTER TABLE app_product_cms_focus_details_translations DROP FOREIGN KEY FK_3C2922252C2AC5D3');
        $this->addSql('DROP TABLE app_product_cms_focus');
        $this->addSql('DROP TABLE app_product_cms_focus_details');
        $this->addSql('DROP TABLE app_product_cms_focus_details_translations');
        $this->addSql('DROP TABLE app_product_cms_focus_translations');
        $this->addSql('ALTER TABLE sylius_product DROP product_cms_focus_id');
    }
}
