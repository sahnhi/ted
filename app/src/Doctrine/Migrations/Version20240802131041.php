<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240802131041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Shipping Method Category on Shipment';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_shipment ADD shipping_method_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shipment ADD CONSTRAINT FK_FD707B336D66C5A6 FOREIGN KEY (shipping_method_category_id) REFERENCES app_shipping_method_category (id)');
        $this->addSql('CREATE INDEX IDX_FD707B336D66C5A6 ON sylius_shipment (shipping_method_category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_shipment DROP FOREIGN KEY FK_FD707B336D66C5A6');
        $this->addSql('DROP INDEX IDX_FD707B336D66C5A6 ON sylius_shipment');
        $this->addSql('ALTER TABLE sylius_shipment DROP shipping_method_category_id');
    }
}
