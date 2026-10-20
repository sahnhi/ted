<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912133905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Pack addons field';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_product_variant_addons (product_variant_id INT NOT NULL, pack_id INT NOT NULL, INDEX IDX_3568CD62A80EF684 (product_variant_id), INDEX IDX_3568CD621919B217 (pack_id), PRIMARY KEY(product_variant_id, pack_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product_variant_addons ADD CONSTRAINT FK_3568CD62A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE sylius_product_variant_addons ADD CONSTRAINT FK_3568CD621919B217 FOREIGN KEY (pack_id) REFERENCES sylius_pack (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sylius_product_variant_addons');
    }
}
