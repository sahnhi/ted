<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240807140721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_product_cms_tags (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_F7A4B26D4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_tags_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, primary_tag VARCHAR(255) DEFAULT NULL, secondary_tag VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_9EA59CD2C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_tags_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_cms_tags ADD CONSTRAINT FK_F7A4B26D4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_product_cms_tags_translation ADD CONSTRAINT FK_9EA59CD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product ADD product_cms_tags_id INT DEFAULT NULL, CHANGE gift_card gift_card TINYINT(1) NOT NULL, CHANGE review_requested review_requested TINYINT(1) NOT NULL, CHANGE can_generate_bundle can_generate_bundle TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B747FB8DEBA FOREIGN KEY (product_cms_tags_id) REFERENCES app_product_cms_tags (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_677B9B747FB8DEBA ON sylius_product (product_cms_tags_id)');
        $this->addSql('ALTER TABLE sylius_product RENAME INDEX uniq_677b9b74570a939a TO UNIQ_677B9B7448974D54');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_product_cms_tags_translation DROP FOREIGN KEY FK_9EA59CD2C2AC5D3');
        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B747FB8DEBA');
        $this->addSql('DROP TABLE app_product_cms_tags');
        $this->addSql('DROP TABLE app_product_cms_tags_translation');
        $this->addSql('DROP INDEX UNIQ_677B9B747FB8DEBA ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product DROP product_cms_tags_id');
        $this->addSql('ALTER TABLE sylius_product RENAME INDEX uniq_677b9b7448974d54 TO UNIQ_677B9B74570A939A');
    }
}
