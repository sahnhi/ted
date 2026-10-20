<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200716125301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_bi_product CHANGE sylius_order_id sylius_order_id INT DEFAULT NULL, CHANGE sylius_order_item_id sylius_order_item_id INT DEFAULT NULL, CHANGE sylius_product_variant_id sylius_product_variant_id INT DEFAULT NULL, CHANGE sylius_taxon_translation_id sylius_taxon_translation_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_bi_product CHANGE sylius_order_id sylius_order_id INT NOT NULL, CHANGE sylius_order_item_id sylius_order_item_id INT NOT NULL, CHANGE sylius_product_variant_id sylius_product_variant_id INT NOT NULL, CHANGE sylius_taxon_translation_id sylius_taxon_translation_id INT NOT NULL');
    }
}
