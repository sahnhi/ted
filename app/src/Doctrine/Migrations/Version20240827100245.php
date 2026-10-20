<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240827100245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add app_shipping_method_category_service_restricted_product table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_shipping_method_category_service_restricted_product (shipping_method_category_service_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_FF106BFA81E077 (shipping_method_category_service_id), UNIQUE INDEX UNIQ_FF106BF4584665A (product_id), PRIMARY KEY(shipping_method_category_service_id, product_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_shipping_method_category_service_restricted_product ADD CONSTRAINT FK_FF106BFA81E077 FOREIGN KEY (shipping_method_category_service_id) REFERENCES app_shipping_method_category_service (id)');
        $this->addSql('ALTER TABLE app_shipping_method_category_service_restricted_product ADD CONSTRAINT FK_FF106BF4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_shipping_method_category_service_restricted_product');
    }
}
