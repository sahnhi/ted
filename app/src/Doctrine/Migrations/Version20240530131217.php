<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Doctrine\Entity\Sylius\ProductAssociationTypeInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240530131217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cross selling product association type';
    }

    public function up(Schema $schema): void
    {
        // Cart Cross Selling
        $this->addSql(
            'INSERT INTO sylius_product_association_type (code, created_at) VALUES (:code, CURRENT_TIMESTAMP)',
            ['code' => ProductAssociationTypeInterface::TYPE_CART_CROSS_SELLING]
        );
        $this->addSql(<<<SQL
            INSERT INTO sylius_product_association_type_translation (translatable_id, locale, name) VALUES(
               (SELECT id FROM sylius_product_association_type WHERE code = :code),
               'fr_FR',
                'Cross Selling Panier'
            )
        SQL, ['code' => ProductAssociationTypeInterface::TYPE_CART_CROSS_SELLING]);

        // Product Cross Selling
        $this->addSql(
            'INSERT INTO sylius_product_association_type (code, created_at) VALUES (:code, CURRENT_TIMESTAMP)',
            ['code' => ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING]
        );
        $this->addSql(<<<SQL
            INSERT INTO sylius_product_association_type_translation (translatable_id, locale, name) VALUES(
               (SELECT id FROM sylius_product_association_type WHERE code = :code),
               'fr_FR',
                'Cross Selling Produit'
            )
        SQL, ['code' => ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM sylius_product_association_type WHERE code = :code',
            ['code' => ProductAssociationTypeInterface::TYPE_CART_CROSS_SELLING]
        );
        $this->addSql(
            'DELETE FROM sylius_product_association_type WHERE code = :code',
            ['code' => ProductAssociationTypeInterface::TYPE_PRODUCT_CROSS_SELLING]
        );
    }
}
