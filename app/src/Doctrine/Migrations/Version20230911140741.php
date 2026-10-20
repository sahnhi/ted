<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230911140741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'set bundle creation date to bundle pricing history ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN sylius_product_variant pv
                ON ba.bundle_id = pv.id
            SET bap.createdAt = pv.created_at
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 6500
            WHERE pv.code IN (\'TEDPM120190\', \'TEDPM120200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 7000
            WHERE pv.code IN (\'TEDPM140190\', \'TEDPM140200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 8000
            WHERE pv.code IN (\'TEDPM160200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 8500
            WHERE pv.code IN (\'TEDPM180200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 9500
            WHERE pv.code IN (\'TEDPM200200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 5000
            WHERE pv.code IN (\'TEDPM70190\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 5500
            WHERE pv.code IN (\'TEDPM80200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
        $this->addSql('
            UPDATE app_bundle_association_price bap
            INNER JOIN app_bundle_association ba
                ON bap.bundle_association_id = ba.id
            INNER JOIN tediber.sylius_product_variant pv
                ON ba.variant_id = pv.id
            SET bap.price = 6000
            WHERE pv.code IN (\'TEDPM90190\', \'TEDPM90200\')
            AND bap.createdAt < \'2022-12-14 14:00:00\'
        ');
    }

    public function down(Schema $schema): void
    {
        // no revert possible
    }
}
