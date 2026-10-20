<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240827082934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sets the ShippingMethodCategory to null on ShippingMethod when deleting a ShippingMethodCategory';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE116D66C5A6');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE116D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_shipping_method DROP FOREIGN KEY FK_5FB0EE116D66C5A6');
        $this->addSql('ALTER TABLE sylius_shipping_method ADD CONSTRAINT FK_5FB0EE116D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id)');
    }
}
