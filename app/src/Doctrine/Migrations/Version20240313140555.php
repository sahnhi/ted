<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240313140555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add price to 0 for bis channel for product_variant return';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
        INSERT INTO
            sylius_channel_pricing(product_variant_id, price, channel_code)
        SELECT cp.product_variant_id, 0, \'bis\' FROM sylius_channel_pricing cp
            INNER JOIN tediber.sylius_product_variant pt
                    ON pt.id = cp.product_variant_id
                WHERE pt.code like "R%"
                AND cp.product_variant_id NOT IN (SELECT cp2.product_variant_id FROM tediber.sylius_channel_pricing cp2
                        WHERE cp2.channel_code = \'bis\'
                    )
                GROUP BY cp.product_variant_id
        ');
    }

    public function down(Schema $schema): void
    {
        // no revert
    }
}
