<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241118101421 extends AbstractMigration
{
    private const PRODUCTS_TAXONS = [
        'mattress' => ['gros-colis'],
        'hybrid-mattress' => ['gros-colis'],
        'go-mattress' => ['colis-grand-ours'],
        'mattress-topper' => ['gros-colis'],
        'mattress-topper-universel' => ['gros-colis'],
        'steel-bed-frame' => ['gros-colis'],
        'bed-frame' => ['gros-colis'],
        'wood-bed-frame' => ['gros-colis', 'colis-montage'],
        'storage-bed-frame' => ['gros-colis', 'colis-montage'],
        'electric-bed-frame' => ['gros-colis', 'colis-montage'],
        'go-bed-frame' => ['colis-grand-ours'],
        'bed-head' => ['gros-colis'],
        'wood-bed-head' => ['gros-colis'],
        'go-bed' => ['colis-grand-ours'],
        'bundle-combo-lit-tapissier' => ['gros-colis'],
        'bundle-combo-lit-bois' => ['gros-colis', 'colis-montage'],
        'bundle-combo-lit-coffre' => ['gros-colis', 'colis-montage'],
        'bundle-combo-lit-electrique' => ['gros-colis', 'colis-montage'],
        'steel-mattress-pack' => ['gros-colis'],
        'bundle-lit-double-synt' => ['gros-colis'],
        'bundle-lit-simple-synt' => ['gros-colis'],
        'bundle-lit-enfant' => ['gros-colis'],
        'bundle-literie-adult' => ['petit-colis'],
        'bundle-literie-kid' => ['petit-colis'],
        'go-pack' => ['colis-grand-ours'],
        'pillow' => ['petit-colis'],
        'pillow-universel' => ['petit-colis'],
        'bolster' => ['petit-colis'],
        'blanket' => ['petit-colis'],
        'blanket-universel' => ['petit-colis'],
        'mattress-protector' => ['petit-colis'],
        'pillow-protector' => ['petit-colis'],
        'sofa-simple' => ['gros-colis', 'colis-montage'],
        'sofa-simple-angle' => ['gros-colis', 'colis-montage'],
        'sofa-bed-duo' => ['gros-colis', 'colis-montage'],
        'sofa-module-pouf' => ['gros-colis'],
        'sofa-module-angle' => ['gros-colis'],
        'sofa-simple-cushion-small-pair' => ['petit-colis'],
        'bundle-bed-linen-set' => ['petit-colis'],
        'bundle-bed-linen-percale-set' => ['petit-colis'],
        'bundle-bed-linen-lin-set' => ['petit-colis'],
        'bundle-bed-linen-gauze-set' => ['petit-colis'],
        'bed-linen-fitted-sheet' => ['petit-colis'],
        'bed-linen-fitted-sheet-percale' => ['petit-colis'],
        'bed-linen-fitted-sheet-linen' => ['petit-colis'],
        'bed-linen-fitted-sheet-gauze' => ['petit-colis'],
        'bed-linen-quilt-cover' => ['petit-colis'],
        'bed-linen-quilt-cover-percale' => ['petit-colis'],
        'bed-linen-quilt-cover-linen' => ['petit-colis'],
        'bed-linen-quilt-cover-gauze' => ['petit-colis'],
        'bed-linen-pillow-case' => ['petit-colis'],
        'bed-linen-pillow-case-percale' => ['petit-colis'],
        'bed-linen-pillow-case-linen' => ['petit-colis'],
        'bed-linen-pillow-case-gauze' => ['petit-colis'],
        'bed-linen-flat-sheet' => ['petit-colis'],
        'small-mattress' => ['petit-colis'],
        'kid-mattress' => ['gros-colis'],
        'small-bed-frame' => ['gros-colis'],
        'kid-steel-bed' => ['gros-colis'],
        'kid-storage-bed' => ['gros-colis', 'colis-montage'],
        'wood-bed-kid' => ['gros-colis', 'colis-montage'],
        'kid-blanket' => ['petit-colis'],
        'kid-pillow' => ['petit-colis'],
        'kid-pillow-protector' => ['petit-colis'],
        'small-sheet' => ['petit-colis'],
        'kid-mattress-protector' => ['petit-colis'],
        'bundle-bed-linen-percalebio-kids-set' => ['petit-colis'],
        'bundle-bed-linen-percalebio-baby-set' => ['petit-colis'],
        'bed-linen-fitted-sheet-percalebiokids' => ['petit-colis'],
        'bed-linen-double-fitted-sheet-percalebiokids-baby' => ['petit-colis'],
        'kid-steel-bed-frame' => ['petit-colis'],
        'kid-storage-bed-frame' => ['petit-colis'],
    ];

    public function getDescription(): string
    {
        return 'Add taxons on products for the shipping method rule';
    }

    public function up(Schema $schema): void
    {
        foreach (self::PRODUCTS_TAXONS as $productCode => $taxonCodes) {
            $position = 0;
            foreach ($taxonCodes as $taxonCode) {
                $this->addSql(<<<SQL
INSERT INTO sylius_product_taxon (product_id, taxon_id, position) VALUES ((SELECT id FROM sylius_product WHERE code = :productCode), (SELECT id FROM sylius_taxon WHERE code = :taxonCode), :position) ON DUPLICATE KEY UPDATE product_id = product_id;
SQL, ['productCode' => $productCode, 'taxonCode' => $taxonCode, 'position' => $position++]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::PRODUCTS_TAXONS as $productCode => $taxonCodes) {
            foreach ($taxonCodes as $taxonCode) {
                $this->addSql(<<<SQL
DELETE FROM sylius_product_taxon WHERE product_id = (SELECT id FROM sylius_product WHERE code = :productCode) AND taxon_id = (SELECT id FROM sylius_taxon WHERE code = :taxonCode)
SQL, ['productCode' => $productCode, 'taxonCode' => $taxonCode]);
            }
        }
    }
}
