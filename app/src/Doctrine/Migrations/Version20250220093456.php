<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250220093456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update shipup_carrier_code in sylius_shipping_method table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_shipping_method SET shipup_carrier_code = \'vir\' WHERE code LIKE \'VIR%\'');
        $this->addSql('UPDATE sylius_shipping_method SET shipup_carrier_code = \'france_express\' WHERE code = \'FRANCE-EXPRESS-Petit-Colis\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE sylius_shipping_method SET shipup_carrier_code = NULL WHERE code LIKE \'VIR%\'');
        $this->addSql('UPDATE sylius_shipping_method SET shipup_carrier_code = \'vir\' WHERE code = \'VIR\'');
        $this->addSql('UPDATE sylius_shipping_method SET shipup_carrier_code = NULL WHERE code = \'FRANCE-EXPRESS-Petit-Colis\'');
    }
}
