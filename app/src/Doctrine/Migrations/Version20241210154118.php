<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241210154118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_option_translation ADD site_name VARCHAR(255) DEFAULT NULL');

        $data = [
            'Drap housse percale bio bébé' => 'Choisissez la taille du drap housse',
            'bed-frame-color' => 'Choisissez la couleur du sommier',
            'bed-frame-foot' => 'Choisissez la taille des pieds',
            'bed-frame-size' => 'Choisissez la taille du sommier',
            'bed-head-color' => 'Choisissez le coloris de la tête de lit',
            'bed-head-size' => 'Choisissez la taille de la tête de lit',
            'bed-linen-double-fitted-sheet-percalebiokids-baby-color' => 'Choisissez la couleur du drap housse',
            'bed-linen-double-fitted-sheet-percalebiokids-baby-size' => 'Choisissez la taille du drap housse',
            'bed-linen-fitted-sheet-color' => 'Choisissez la couleur du drap housse',
            'bed-linen-fitted-sheet-gauze-color_' => 'Choisissez la couleur du drap housse',
            'bed-linen-fitted-sheet-gauze-size' => 'Choisissez la taille du drap housse',
            'bed-linen-fitted-sheet-linen-color' => 'Choisissez la couleur du drap housse',
            'bed-linen-fitted-sheet-linen-size' => 'Choisissez la taille du drap housse',
            'bed-linen-fitted-sheet-percale-color' => 'Choisissez la couleur du drap housse',
            'bed-linen-fitted-sheet-percale-size' => 'Choisissez la taille du drap housse',
            'bed-linen-fitted-sheet-percalebiokids-baby' => 'Choisissez la taille du drap housse',
            'bed-linen-fitted-sheet-percalebiokids-baby-color' => 'Choisissez la couleur du drap housse',
            'bed-linen-fitted-sheet-percalebiokids-color' => 'Choisissez la couleur du drap housse',
            'bed-linen-fitted-sheet-percalebiokids-size' => 'Choisissez la taille du drap housse',
            'bed-linen-fitted-sheet-size' => 'Choisissez la taille du drap housse',
            'bed-linen-flat-sheet-color' => 'Choisissez la couleur du drap plat',
            'bed-linen-flat-sheet-size' => 'Choisissez la taille du drap plat',
            'bed-linen-pillow-case-color' => 'Choisissez la couleur des taies d’oreiller',
            'bed-linen-pillow-case-gauze-color' => 'Choisissez la couleur des taies d’oreiller',
            'bed-linen-pillow-case-gauze-size' => 'Choisissez la taille des taies d’oreiller',
            'bed-linen-pillow-case-linen-color' => 'Choisissez la couleur des taies d’oreiller',
            'bed-linen-pillow-case-linen-size' => 'Choisissez la taille des taies d’oreiller',
            'bed-linen-pillow-case-percale-color' => 'Choisissez la couleur des taies d’oreiller',
            'bed-linen-pillow-case-percale-size' => 'Choisissez la taille des taies d’oreiller',
            'bed-linen-pillow-case-percalebiokids-color' => 'Choisissez la couleur de la taie d’oreiller',
            'bed-linen-pillow-case-percalebiokids-size' => 'Choisissez la taille de la taie d’oreiller',
            'bed-linen-pillow-case-size' => 'Choisissez la taille des taies d’oreiller',
            'bed-linen-quilt-cover-color' => 'Choisissez la couleur de la housse de couette',
            'bed-linen-quilt-cover-gauze-color' => 'Choisissez la couleur de la housse de couette',
            'bed-linen-quilt-cover-gauze-size' => 'Choisissez la taille de la housse de couette',
            'bed-linen-quilt-cover-linen-color' => 'Choisissez la couleur de la housse de couette',
            'bed-linen-quilt-cover-linen-size' => 'Choisissez la taille de la housse de couette',
            'bed-linen-quilt-cover-percale-color' => 'Choisissez la couleur de la housse de couette',
            'bed-linen-quilt-cover-percale-size' => 'Choisissez la taille de la housse de couette',
            'bed-linen-quilt-cover-percalebiokids-color' => 'Choisissez la couleur de la housse de couette',
            'bed-linen-quilt-cover-percalebiokids-size' => 'Choisissez la taille de la housse de couette',
            'bed-linen-quilt-cover-size' => 'Choisissez la taille de la housse de couette',
            'bed-linen-set-color' => 'Choisissez la couleur de la parure',
            'bed-linen-set-gauze-color' => 'Choisissez la couleur de la parure',
            'bed-linen-set-gauze-size' => 'Choisissez la taille de la parure',
            'bed-linen-set-size' => 'Choisissez la taille de la parure',
            'blanket-size' => 'Choisissez la taille de la couette',
            'blanket-universel-size' => 'Choisissez la taille de la couette',
            'bolster-size' => 'Choisissez la taille du traversin',
            'bundle-bed-frame-color' => 'Couleur du tissu',
            'bundle-bed-linen-fitted-sheet-size' => 'Choisissez la taille du drap housse',
            'bundle-bed-linen-gauze-fitted-sheet-size' => 'Choisissez la taille du drap housse',
            'bundle-bed-linen-gauze-pillow-case-size' => 'Choisissez la taille des taies d’oreiller',
            'bundle-bed-linen-gauze-quilt-cover-size' => 'Choisissez la taille de la housse de couette',
            'bundle-bed-linen-gauze-set-color' => 'Choisissez la couleur de la parure',
            'bundle-bed-linen-lin-fitted-sheet-size' => 'Choisissez la taille du drap housse',
            'bundle-bed-linen-lin-pillow-case-size' => 'Choisissez la taille des taies d’oreiller',
            'bundle-bed-linen-lin-quilt-cover-size' => 'Choisissez la taille de la housse de couette',
            'bundle-bed-linen-lin-set-color' => 'Choisissez la couleur de la parure',
            'bundle-bed-linen-percale-fitted-sheet-size' => 'Choisissez la taille du drap housse',
            'bundle-bed-linen-percale-pillow-case-size' => 'Choisissez la taille des taies d’oreiller',
            'bundle-bed-linen-percale-quilt-cover-size' => 'Choisissez la taille de la housse de couette',
            'bundle-bed-linen-percale-set-color' => 'Choisissez la couleur de la parure',
            'bundle-bed-linen-percalebio-baby-pillow-case-size' => 'Choisissez la taille de la taie d’oreiller',
            'bundle-bed-linen-percalebio-baby-quilt-cover-size' => 'Choisissez la taille de la housse de couette',
            'bundle-bed-linen-percalebio-baby-set-color' => 'Choisissez la couleur de la parure',
            'bundle-bed-linen-percalebio-kids-pillow-case-size' => 'Choisissez la taille de la taie d’oreiller',
            'bundle-bed-linen-percalebio-kids-quilt-cover-size' => 'Choisissez la taille de la housse de couette',
            'bundle-bed-linen-percalebio-kids-set-color' => 'Choisissez la couleur de la parure',
            'bundle-bed-linen-pillow-case-size' => 'Choisissez la taille des taies d’oreiller',
            'bundle-bed-linen-quilt-cover-size' => 'Choisissez la taille de la housse de couette',
            'bundle-bed-linen-set-color' => 'Choisissez la couleur de la parure',
            'bundle-combo-lit-bloc-type' => 'Choisissez le type de sommier',
            'bundle-combo-lit-color' => 'Choisissez la couleur du lit',
            'bundle-combo-lit-foot' => 'Choisissez la taille des pieds',
            'bundle-combo-lit-size' => 'Choisissez la taille du lit',
            'bundle-foot' => 'Taille des pieds',
            'bundle-frame-type' => 'Choisissez le matelas',
            'bundle-lit-kid-bed-frame' => 'Choisissez le sommier',
            'bundle-lit-kid-color' => 'Couleur du tissu',
            'bundle-lit-kid-size' => 'Choisissez la taille du pack',
            'bundle-literie-adult-blanket-size' => 'Choisissez la taille de la couette',
            'bundle-literie-adult-pillow-size' => 'Choisissez la forme des oreillers',
            'bundle-literie-adult-type' => 'Choisissez le confort du pack',
            'bundle-literie-kid-blanket-size' => 'Choisissez la taille de la couette',
            'bundle-mattress' => 'Choisissez le matelas',
            'bundle-mattress-type' => 'Choisissez le matelas',
            'bundle-pillow-size-uni' => 'Choisissez la taille de l’oreiller',
            'bundle-size' => 'Choisissez la taille du pack',
            'electric-bed-frame-color' => 'Choisissez la couleur du sommier',
            'electric-bed-frame-foot' => 'Choisissez la taille des pieds',
            'electric-bed-frame-size' => 'Choisissez la taille du sommier',
            'go-bed-color' => 'Choisissez la couleur du lit',
            'go-bed-frame-color' => 'Choisissez la couleur du sommier',
            'go-bed-frame-high' => 'Choisissez votre style de sommier',
            'go-bed-frame-size' => 'Choisissez la taille du sommier',
            'go-bed-frame-woof-feet-type' => 'Forme des pieds',
            'go-bed-head-color' => 'Choisissez la couleur de la tête de lit',
            'go-bed-head-size' => 'Choisissez la taille de la tête de lit',
            'go-bed-head-type' => 'Choisissez votre style de tête de lit',
            'go-bed-high' => 'Choisissez votre style de sommier',
            'go-bed-linen-fitted-sheet-linen-color' => 'Choisissez la couleur du drap housse',
            'go-bed-linen-fitted-sheet-linen-size' => 'Choisissez la taille du drap housse',
            'go-bed-size' => 'Choisissez la taille du lit',
            'go-bed-wood-feet-form' => 'Forme des pieds',
            'go-mattress-protector-size' => 'Choisissez la taille du protège matelas',
            'go-mattress-size' => 'Choisissez la taille du matelas',
            'go-mattress-topper-size' => 'Choisissez la taille du matelas',
            'go-mattress-type' => 'Choisissez le type de matelas',
            'go-pack-bed-head-type' => 'Choisissez votre style de tête de lit',
            'go-pack-color' => 'Choisissez la couleur du lit',
            'go-pack-high' => 'Choisissez le style de lit',
            'go-pack-mattress-type' => 'Choisissez le type de matelas',
            'go-pack-size' => 'Choisissez la taille du pack',
            'go-pack-wood-feet-form' => 'Forme des pieds',
            'hybrid-mattress-size' => 'Choisissez la taille du matelas',
            'kid-bed-frame-color' => 'Choisissez la couleur du lit',
            'kid-bed-frame-size' => 'Choisissez la taille du lit',
            'kid-blanket-size' => 'Choisissez la taille de la couette',
            'kid-mattress-protector-size' => 'Choisissez la taille de la protection',
            'kid-mattress-size' => 'Choisissez la taille du matelas',
            'kid-pillow-protector-size' => 'Choisissez la taille de la protection',
            'kid-pillow-size' => 'Choisissez la taille de l’oreiller',
            'kid-steel-bed-frame-feet' => 'Choisissez la taille des pieds',
            'kid-steel-bed-frame-size' => 'Choisissez la taille du lit',
            'kid-storage-bed-frame-color' => 'Choisissez la couleur du lit',
            'kid-storage-bed-frame-size' => 'Choisissez la taille du lit',
            'mattress-protector-size' => 'Choisissez la taille du protège matelas',
            'mattress-topper-size' => 'Choisissez la taille du matelas',
            'mattress-topper-universel-size' => 'Choisissez la taille du matelas',
            'pillow-protector-size' => 'Choisissez la taille du protège oreiller',
            'pillow-size' => 'Choisissez la taille de l’oreiller',
            'pillow-universel-size' => 'Choisissez la taille de l’oreiller',
            'small-bed-frame-color' => 'Choisissez la couleur du lit',
            'small-bed-frame-size' => 'Choisissez la taille du lit',
            'small-mattress-size' => 'Choisissez la taille du matelas',
            'small-sheet-size' => 'Choisissez la taille de la protection',
            'sofa-bed-armrest-type' => 'Choisissez le type d’accoudoir du canapé lit',
            'sofa-bed-color' => 'Choisissez la couleur du tissu',
            'sofa-bed-set-color' => 'Choisissez la couleur du tissu',
            'sofa-cushion-small-cushions-color' => 'Choisissez la couleur du tissu',
            'sofa-module-angle-color' => 'Choisissez la couleur du module',
            'sofa-module-feet-color' => 'Choisissez la couleur des pieds',
            'sofa-module-feet-form' => 'Choisissez la forme des pieds',
            'sofa-module-pouf-color' => 'Choisissez la couleur du pouf',
            'sofa-simple-angle-color' => 'Choisissez la couleur du canapé',
            'sofa-simple-angle-size' => 'Choisissez la taille du canapé',
            'sofa-simple-color' => 'Choisissez la couleur du canapé',
            'sofa-simple-feet-color' => 'Choisissez la couleur des pieds',
            'sofa-simple-feet-form' => 'Choisissez la forme des pieds',
            'sofa-simple-size' => 'Choisissez la taille du canapé',
            'sofa-v2-module-angle-color' => 'Choisissez la couleur du canapé',
            'sofa-v2-simple-color' => 'Choisissez la couleur du canapé',
            'sofa-v2-simple-size' => 'Choisissez la taille du canapé',
            'steed-bed-frame-cover-color' => 'Choisissez la couleur du sommier',
            'steel-bed-frame-cover-size' => 'Choisissez la taille du sommier',
            'steel-bed-frame-feet-size' => 'Choisissez la taille des pieds',
            'steel-bed-frame-size' => 'Choisissez la taille du sommier',
            'storage-bed-frame-color' => 'Choisissez la couleur du sommier',
            'storage-bed-frame-size' => 'Choisissez la taille du sommier',
            'wood-bed-frame-color' => 'Choisissez la couleur du sommier',
            'wood-bed-frame-size' => 'Choisissez la taille du sommier',
            'wood-bed-head-color' => 'Choisissez la couleur de la tête de lit',
            'wood-bed-head-cushions-color' => 'Choisissez la couleur des coussins',
            'wood-bed-head-cushions-size' => 'Choisissez la taille des coussins',
            'wood-bed-head-kid-color' => 'Choisissez la couleur de la tête de lit',
            'wood-bed-head-kid-size' => 'Choisissez la taille de la tête de lit',
            'wood-bed-head-size' => 'Choisissez la taille de la tête de lit',
            'wood-bed-kid-color' => 'Choisissez la couleur du sommier',
            'wood-bed-kid-size' => 'Choisissez la taille du sommier',
            'mattress-size' => 'Choisissez la taille du matelas',
        ];

        foreach ($data as $key => $value) {
            $this->addSql(<<<SQL
                UPDATE sylius_product_option_translation 
                SET site_name = :value 
                WHERE translatable_id = (SELECT id FROM sylius_product_option WHERE code = :key)
            SQL, ['key' => $key, 'value' => $value]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_option_translation DROP site_name');
    }

    public function isTransactional(): bool
    {
        return true;
    }
}
