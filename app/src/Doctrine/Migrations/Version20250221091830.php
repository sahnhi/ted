<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250221091830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update channel pricings on return products';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            INSERT INTO sylius_channel_pricing (product_variant_id, price, original_price, channel_code)
            SELECT spv.id, 0, 0, c.code
                FROM sylius_product_variant spv
                INNER JOIN sylius_product sp ON sp.id = spv.product_id
                INNER JOIN sylius_product_channels spc ON spc.product_id = sp.id
                INNER JOIN sylius_channel c ON c.id != spc.channel_id
            WHERE sp.code LIKE 'return-%'
            ON DUPLICATE KEY UPDATE price = price;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
    }
}
