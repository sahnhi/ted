<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240911092353 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_product_cms_faq (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, UNIQUE INDEX UNIQ_22F9828E4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_faq_question (id INT AUTO_INCREMENT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, productCMSFaq_id INT DEFAULT NULL, INDEX IDX_9C3C8E6373134CDA (productCMSFaq_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_faq_question_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, question VARCHAR(255) NOT NULL, answer LONGTEXT NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_5FC403FC2C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_faq_question_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_faq_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, title VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_F3BAEAF22C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_faq_translations_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_cms_faq ADD CONSTRAINT FK_22F9828E4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_product_cms_faq_question ADD CONSTRAINT FK_9C3C8E6373134CDA FOREIGN KEY (productCMSFaq_id) REFERENCES app_product_cms_faq (id)');
        $this->addSql('ALTER TABLE app_product_cms_faq_question_translations ADD CONSTRAINT FK_5FC403FC2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_faq_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_product_cms_faq_translations ADD CONSTRAINT FK_F3BAEAF22C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_faq (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_faq_question DROP FOREIGN KEY FK_9C3C8E6373134CDA');
        $this->addSql('ALTER TABLE app_product_cms_faq_translations DROP FOREIGN KEY FK_F3BAEAF22C2AC5D3');
        $this->addSql('ALTER TABLE app_product_cms_faq_question_translations DROP FOREIGN KEY FK_5FC403FC2C2AC5D3');
        $this->addSql('DROP TABLE app_product_cms_faq');
        $this->addSql('DROP TABLE app_product_cms_faq_question');
        $this->addSql('DROP TABLE app_product_cms_faq_question_translations');
        $this->addSql('DROP TABLE app_product_cms_faq_translations');
    }

    public function isTransactional(): bool
    {
        return true;
    }
}
