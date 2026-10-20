<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240307163909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update original prices to new product bundles';
    }

    public function up(Schema $schema): void
    {
        $productCodes = ['bundle-lit-adult', 'bundle-literie-adult', 'bundle-lit-kid', 'bundle-literie-kid'];

        $inQuery = "'" . join("', '", $productCodes) . "'";

        $this->addSql("
            UPDATE sylius_channel_pricing cp
            INNER JOIN sylius_product_variant pv
                ON pv.id = cp.product_variant_id
            INNER JOIN sylius_product p
                ON p.id = pv.product_id
            SET cp.original_price = cp.price, cp.price = cp.price * 0.9
            WHERE p.code IN ($inQuery)
            AND cp.price > 0
            AND cp.price is not null
        ");

        $this->addSql("
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON pv.id = ba.bundle_id
            INNER JOIN sylius_product p
                ON p.id = pv.product_id
            SET bap.price = bap.price * 0.9
            WHERE p.code IN ($inQuery)
            AND bap.price > 0
            AND bap.price is not null
        ");
    }

    public function down(Schema $schema): void
    {
        $productCodes = ['bundle-lit-adult', 'bundle-literie-adult', 'bundle-lit-kid', 'bundle-literie-kid'];

        $inQuery = "'" . join("', '", $productCodes) . "'";

        $this->addSql("
            UPDATE sylius_channel_pricing cp
            INNER JOIN sylius_product_variant pv
                ON pv.id = cp.product_variant_id
            INNER JOIN sylius_product p
                ON p.id = pv.product_id
            SET cp.original_price = NULL, cp.price = cp.price / 0.9
            WHERE p.code IN ($inQuery)
        ");

        $this->addSql("
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON pv.id = ba.bundle_id
            INNER JOIN sylius_product p
                ON p.id = pv.product_id
            SET bap.price = bap.price / 0.9
            WHERE p.code IN ($inQuery)
            AND bap.price > 0
            AND bap.price is not null
        ");
    }
}
