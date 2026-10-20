<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240926123702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Shipping Method Category Claim Tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_shipping_method_category_claim (id INT AUTO_INCREMENT NOT NULL, shipping_method_category_id INT NOT NULL, enabled TINYINT(1) DEFAULT \'0\' NOT NULL, pushed TINYINT(1) DEFAULT \'0\' NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, INDEX IDX_450474D56D66C5A6 (shipping_method_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_shipping_method_category_claim_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, content VARCHAR(255) DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_FDDE722E2C2AC5D3 (translatable_id), UNIQUE INDEX app_shipping_method_category_claim_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_shipping_method_category_claim ADD CONSTRAINT FK_450474D56D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id)');
        $this->addSql('ALTER TABLE app_shipping_method_category_claim_translation ADD CONSTRAINT FK_FDDE722E2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES app_shipping_method_category_claim (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_shipping_method_category_claim_translation DROP FOREIGN KEY FK_FDDE722E2C2AC5D3');
        $this->addSql('DROP TABLE app_shipping_method_category_claim');
        $this->addSql('DROP TABLE app_shipping_method_category_claim_translation');
    }
}
