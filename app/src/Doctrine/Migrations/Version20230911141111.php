<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230911141111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add price for bundle with reference TEDPMX created before 2022-12-14 14:00:00';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 7000, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM120190\', \'TEDPM120200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 7500, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM140190\', \'TEDPM140200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 8500, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM160200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 9000, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM180200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 10000, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM200200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 5500, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM70190\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 6000, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM80200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
        INSERT INTO
            app_bundle_association_price(bundle_association_id, price, createdAt)
        SELECT bap.bundle_association_id, 6500, \'2022-12-14 14:00:00\' FROM app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.variant_id = pv.id
            WHERE pv.code IN (\'TEDPM90190\', \'TEDPM90200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
    }

    public function down(Schema $schema): void
    {
        // no revert possible
    }
}
