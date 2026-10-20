<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240904120820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add app_shipping_category_translation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_shipping_category_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, claims LONGTEXT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_C808493B2C2AC5D3 (translatable_id), UNIQUE INDEX app_shipping_category_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_shipping_category_translation ADD CONSTRAINT FK_C808493B2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_shipping_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_shipping_category_translation');
    }
}
