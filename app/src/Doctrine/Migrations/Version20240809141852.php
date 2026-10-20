<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240809141852 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_rtb_infos (id INT AUTO_INCREMENT NOT NULL, priority INT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_rtb_infos_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_4E352EF52C2AC5D3 (translatable_id), UNIQUE INDEX app_rtb_infos_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_rtb_infos (product_id INT NOT NULL, rtb_infos_id INT NOT NULL, INDEX IDX_16BAE91B4584665A (product_id), INDEX IDX_16BAE91BB4CE313C (rtb_infos_id), PRIMARY KEY(product_id, rtb_infos_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_rtb_infos_translation ADD CONSTRAINT FK_4E352EF52C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_rtb_infos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_product_rtb_infos ADD CONSTRAINT FK_16BAE91B4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_product_rtb_infos ADD CONSTRAINT FK_16BAE91BB4CE313C FOREIGN KEY (rtb_infos_id) REFERENCES app_rtb_infos (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_rtb_infos_translation DROP FOREIGN KEY FK_4E352EF52C2AC5D3');
        $this->addSql('ALTER TABLE app_product_rtb_infos DROP FOREIGN KEY FK_16BAE91BB4CE313C');
        $this->addSql('DROP TABLE app_rtb_infos');
        $this->addSql('DROP TABLE app_rtb_infos_translation');
        $this->addSql('DROP TABLE app_product_rtb_infos');
    }
}
