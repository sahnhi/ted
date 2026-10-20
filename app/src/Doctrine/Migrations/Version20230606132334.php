<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Services\Shipment\EnumCarrierCode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230606132334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipping_method ADD shipup_carrier_code VARCHAR(255) DEFAULT NULL');

        foreach (EnumCarrierCode::CARRIER_CODES as $code => $shipupCarrierCode) {
            $this->addSql(sprintf("UPDATE sylius_shipping_method SET shipup_carrier_code = '%s' WHERE code = '%s'", $shipupCarrierCode, $code));
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_shipping_method DROP shipup_carrier_code');
    }
}
