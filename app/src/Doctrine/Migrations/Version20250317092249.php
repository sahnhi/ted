<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317092249 extends AbstractMigration
{
    private const PRODUCTS_TAXONS = [
        'return-b2b-air1-mattress' => ['gros-colis'],
        'return-b2b-air2-mattress' => ['gros-colis'],
        'return-b2b-air3-mattress' => ['gros-colis'],
        'return-b2b-bed-feet' => ['petit-colis'],
        'return-b2b-blanket-g1' => ['petit-colis'],
        'return-B2B-mattress' => ['gros-colis'],
        'return-b2b-pillow-g1' => ['petit-colis'],
        'return-B2B-protection' => ['petit-colis'],
        'return-b2b-steel-bed-frame-cover' => ['petit-colis'],
        'return-b2b-steel-bed-frame-structure' => ['gros-colis'],
        'return-bed-foot' => ['petit-colis'],
        'return-bed-frame' => ['gros-colis'],
        'return-bed-frame-phenix' => ['gros-colis'],
        'return-bed-head' => ['gros-colis'],
        'return-bed-head-phenix' => ['gros-colis'],
        'return-bed-linen-double-fitted-sheet-percalebiokids-baby' => ['petit-colis'],
        'return-bed-linen-fitted-sheet' => ['petit-colis'],
        'return-bed-linen-fitted-sheet-gauze' => ['petit-colis'],
        'return-bed-linen-fitted-sheet-linen' => ['petit-colis'],
        'return-bed-linen-fitted-sheet-percale' => ['petit-colis'],
        'return-bed-linen-fitted-sheet-percalebiokids' => ['petit-colis'],
        'return-bed-linen-fitted-sheet-percalebiokids-baby' => ['petit-colis'],
        'return-bed-linen-flat-sheet' => ['petit-colis'],
        'return-bed-linen-pillow-case' => ['petit-colis'],
        'return-bed-linen-pillow-case-linen' => ['petit-colis'],
        'return-bed-linen-pillow-case-percale' => ['petit-colis'],
        'return-bed-linen-pillow-case-percalebiokids' => ['petit-colis'],
        'return-bed-linen-quilt-cover' => ['petit-colis'],
        'return-bed-linen-quilt-cover-gauze' => ['petit-colis'],
        'return-bed-linen-quilt-cover-linen' => ['petit-colis'],
        'return-bed-linen-quilt-cover-percale' => ['petit-colis'],
        'return-bed-linen-quilt-cover-percalebiokids' => ['petit-colis'],
        'return-bed-linen-set' => ['petit-colis'],
        'return-blanket' => ['petit-colis'],
        'return-blanket-phenix' => ['petit-colis'],
        'return-blanket-store' => ['petit-colis'],
        'return-blanket-universel' => ['petit-colis'],
        'return-blanket-universel-bundle' => ['petit-colis'],
        'return-bolster' => ['petit-colis'],
        'return-bolster-store' => ['petit-colis'],
        'return-bundle-bed-linen-gauze-set' => ['petit-colis'],
        'return-bundle-bed-linen-percale-set' => ['petit-colis'],
        'return-bundle-combo-lit-bois' => ['gros-colis', 'colis-montage'],
        'return-bundle-literie-adult' => ['petit-colis'],
        'return-electric-bed-frame' => ['gros-colis', 'colis-montage'],
        'return-electric-bed-frame-cover' => ['petit-colis'],
        'return-electric-bed-frame-cover-unit' => ['petit-colis'],
        'return-electric-bed-frame-foot' => ['petit-colis'],
        'return-electric-bed-frame-structure' => ['gros-colis'],
        'return-go-bed-frame' => ['colis-grand-ours', 'colis-montage'],
        'return-go-bed-frame-plastic-feet' => ['petit-colis'],
        'return-go-bed-frame-wood-feet' => ['petit-colis'],
        'return-go-bed-head' => ['colis-grand-ours'],
        'return-go-bed-linen-fitted-sheet-linen' => ['petit-colis'],
        'return-go-bloc-frame' => ['colis-grand-ours', 'colis-montage'],
        'return-go-mattress' => ['colis-grand-ours'],
        'return-go-mattress-base' => ['gros-colis'],
        'return-go-mattress-protector' => ['petit-colis'],
        'return-go-mattress-topper' => ['colis-grand-ours'],
        'return-hybrid-mattress' => ['gros-colis'],
        'return-kid-bed-frame' => ['gros-colis'],
        'return-kid-blanket' => ['petit-colis'],
        'return-kid-blanket-store' => ['petit-colis'],
        'return-kid-mattress' => ['gros-colis'],
        'return-kid-mattress-phenix' => ['gros-colis'],
        'return-kid-mattress-protector' => ['petit-colis'],
        'return-kid-pillow' => ['petit-colis'],
        'return-kid-pillow-protector' => ['petit-colis'],
        'return-kid-pillow-store' => ['petit-colis'],
        'return-kid-steel-bed-frame' => ['gros-colis'],
        'return-kid-storage-bed-frame' => ['gros-colis'],
        'return-mattress' => ['gros-colis'],
        'return-mattress-cover' => ['petit-colis'],
        'return-mattress-phenix' => ['gros-colis'],
        'return-mattress-protector' => ['petit-colis'],
        'return-mattress-topper' => ['gros-colis'],
        'return-mattress-topper-universel' => ['gros-colis'],
        'return-pack_pelote' => ['gros-colis'],
        'return-pelote-mattress' => ['gros-colis'],
        'return-pelote-mattress-cover' => ['petit-colis'],
        'return-pillow' => ['petit-colis'],
        'return-pillow-phenix' => ['petit-colis'],
        'return-pillow-protector' => ['petit-colis'],
        'return-pillow-store' => ['petit-colis'],
        'return-pillow-universel' => ['petit-colis'],
        'return-pillow-universel-bundle' => ['petit-colis'],
        'return-small-bed-frame' => ['gros-colis'],
        'return-small-mattress' => ['gros-colis'],
        'return-small-sheet' => ['petit-colis'],
        'return-sofa-bed' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-armrest' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-back-bar' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-color-combo-phenix' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-combo' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-cushions' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-drawer' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-duo' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-frame' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-frame-base' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-frame-phenix' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-mattress' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-set' => ['petit-colis'],
        'return-sofa-bed-solo' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-solo-armrest' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-solo-combo' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-solo-cushions' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-solo-frame' => ['gros-colis', 'colis-montage'],
        'return-sofa-bed-solo-mattress' => ['gros-colis', 'colis-montage'],
        'return-sofa-module-angle' => ['gros-colis'],
        'return-sofa-module-pouf' => ['gros-colis'],
        'return-sofa-simple' => ['gros-colis', 'colis-montage'],
        'return-sofa-simple-angle' => ['gros-colis'],
        'return-sofa-simple-cushion-small-pair' => ['petit-colis'],
        'return-sofa-simple-feet' => ['petit-colis'],
        'return-sofa-simple-structure' => ['gros-colis', 'colis-montage'],
        'return-sofa-structure' => ['gros-colis', 'colis-montage'],
        'return-steel-bed-frame' => ['gros-colis'],
        'return-steel-bed-frame-cover' => ['petit-colis'],
        'return-steel-bed-frame-feet' => ['gros-colis', 'colis-montage'],
        'return-steel-bed-frame-structure' => ['gros-colis'],
        'return-storag-bed-frame-simple-pan' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame-batten-tips' => ['petit-colis'],
        'return-storage-bed-frame-component' => ['gros-colis'],
        'return-storage-bed-frame-cover' => ['petit-colis'],
        'return-storage-bed-frame-pan-hook' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame-structure' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame-structureA' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame-structureA-phenix' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame-structureB' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame-structureB-phenix' => ['gros-colis', 'colis-montage'],
        'return-storage-bed-frame-tool-kit' => ['petit-colis'],
        'return-TEDCSYNTH' => ['petit-colis'],
        'return-tediber-mattress-unpackaged' => ['gros-colis'],
        'return-TEDOSYNTH' => ['petit-colis'],
        'return-wood-bed-frame' => ['gros-colis', 'colis-montage'],
        'return-wood-bed-frame-kid-structureb' => ['gros-colis', 'colis-montage'],
        'return-wood-bed-frame-structurea' => ['gros-colis', 'colis-montage'],
        'return-wood-bed-frame-structureb' => ['gros-colis', 'colis-montage'],
        'return-wood-bed-frame-structurec' => ['gros-colis', 'colis-montage'],
        'return-wood-bed-head' => ['gros-colis', 'colis-montage'],
        'return-wood-bed-head-cushions' => ['gros-colis'],
        'return-wood-bed-kid' => ['gros-colis', 'colis-montage'],
    ];

    public function getDescription(): string
    {
        return 'Add taxon on return products';
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
    }
}
