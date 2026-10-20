<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240808140625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates the product_cms_claims table and its translation table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_product_cms_claims (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_7F2398954584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_product_cms_claims_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, claim VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_F60CA65B2C2AC5D3 (translatable_id), UNIQUE INDEX app_product_cms_claims_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_product_cms_claims ADD CONSTRAINT FK_7F2398954584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE app_product_cms_claims_translation ADD CONSTRAINT FK_F60CA65B2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_product_cms_claims (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_claims_translation DROP FOREIGN KEY FK_F60CA65B2C2AC5D3');
        $this->addSql('DROP TABLE app_product_cms_claims');
        $this->addSql('DROP TABLE app_product_cms_claims_translation');
    }
}
