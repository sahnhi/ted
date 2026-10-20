<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811205619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_product_cms (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_1545EBA44584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, sub_title VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_BF497CF2C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_cms ADD CONSTRAINT FK_1545EBA44584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_product_cms_translation ADD CONSTRAINT FK_BF497CF2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_product_cms_translation DROP FOREIGN KEY FK_BF497CF2C2AC5D3');
        $this->addSql('DROP TABLE app_product_cms');
        $this->addSql('DROP TABLE app_product_cms_translation');
    }
}
