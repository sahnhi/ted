<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240827083321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sets the ShippingMethodCategory to null on Shipment when deleting a ShippingMethodCategory';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_shipment DROP FOREIGN KEY FK_FD707B336D66C5A6');
        $this->addSql('ALTER TABLE sylius_shipment ADD CONSTRAINT FK_FD707B336D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_shipment DROP FOREIGN KEY FK_FD707B336D66C5A6');
        $this->addSql('ALTER TABLE sylius_shipment ADD CONSTRAINT FK_FD707B336D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id)');
    }
}
