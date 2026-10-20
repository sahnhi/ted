<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240903115423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alter table app_product_cms_technical_details_translations to change text column to LONGTEXT in order not be limited to 255 characters';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_technical_details_translations CHANGE text text LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_product_cms_technical_details_translations CHANGE text text VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
